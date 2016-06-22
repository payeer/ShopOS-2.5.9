<?php
include_once($_SERVER ['DOCUMENT_ROOT'] . '/includes/top.php');

if (isset($_POST['m_operation_id']) && isset($_POST['m_sign']))
{
	$err = false;
	$message = '';
	$orderid = $_POST['m_orderid'];
	
	$q = os_db_query("
		SELECT `configuration_key`, `configuration_value` FROM `" . TABLE_CONFIGURATION . "` WHERE `configuration_key` IN (
			'MODULE_PAYMENT_PAYEER_SECRET_KEY',
			'MODULE_PAYMENT_PAYEER_IPFILTER',
			'MODULE_PAYMENT_PAYEER_EMAILERR',
			'MODULE_PAYMENT_PAYEER_LOGFILE',		
			'MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID'
		)
	");

	while ($data = os_db_fetch_array($q))
	{
		$config[$data['configuration_key']] = $data['configuration_value'];
	}
	
	// запись логов

	$log_text = 
	"--------------------------------------------------------\n" .
	"operation id		" . $_POST['m_operation_id'] . "\n" .
	"operation ps		" . $_POST['m_operation_ps'] . "\n" .
	"operation date		" . $_POST['m_operation_date'] . "\n" .
	"operation pay date	" . $_POST['m_operation_pay_date'] . "\n" .
	"shop				" . $_POST['m_shop'] . "\n" .
	"order id			" . $_POST['m_orderid'] . "\n" .
	"amount				" . $_POST['m_amount'] . "\n" .
	"currency			" . $_POST['m_curr'] . "\n" .
	"description		" . base64_decode($_POST['m_desc']) . "\n" .
	"status				" . $_POST['m_status'] . "\n" .
	"sign				" . $_POST['m_sign'] . "\n\n";
	
	$log_file = $config['MODULE_PAYMENT_PAYEER_LOGFILE'];
	
	if (!empty($log_file))
	{
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . $log_file, $log_text, FILE_APPEND);
	}
	
	// проверка цифровой подписи и ip

	$sign_hash = strtoupper(hash('sha256', implode(":", array(
		$_POST['m_operation_id'],
		$_POST['m_operation_ps'],
		$_POST['m_operation_date'],
		$_POST['m_operation_pay_date'],
		$_POST['m_shop'],
		$_POST['m_orderid'],
		$_POST['m_amount'],
		$_POST['m_curr'],
		$_POST['m_desc'],
		$_POST['m_status'],
		$config['MODULE_PAYMENT_PAYEER_SECRET_KEY']
	))));
	
	$valid_ip = true;
	$sIP = str_replace(' ', '', $config['MODULE_PAYMENT_PAYEER_IPFILTER']);
	
	if (!empty($sIP))
	{
		$arrIP = explode('.', $_SERVER['REMOTE_ADDR']);
		if (!preg_match('/(^|,)(' . $arrIP[0] . '|\*{1})(\.)' .
		'(' . $arrIP[1] . '|\*{1})(\.)' .
		'(' . $arrIP[2] . '|\*{1})(\.)' .
		'(' . $arrIP[3] . '|\*{1})($|,)/', $sIP))
		{
			$valid_ip = false;
		}
	}
	
	if (!$valid_ip)
	{
		$message .= " - the ip address of the server is not trusted\n" . 
		"   trusted ip: " . $sIP . "\n" .
		"   ip of the current server: " . $_SERVER['REMOTE_ADDR'] . "\n";
		$err = true;
	}

	if ($_POST['m_sign'] != $sign_hash)
	{
		$message .= " - do not match the digital signature\n";
		$err = true;
	}
	
	if (!$err)
	{
		// загрузка заказа
		
		$check_query = os_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '$orderid'");
		$flag = os_db_num_rows($check_query);
		
		if ($flag <= 0) 
		{
			$message .= " - wrong order id\n";
			$err = true;
		}
		else
		{
			switch ($request['m_status'])
			{
				case 'success':
					$sq_a = array('orders_status' => $config['MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID']);
					os_db_perform(DB_PREFIX . 'orders', $sq_a, 'update', "orders_id='$orderid'");

					$sq_a = array(
						'orders_id' => $orderid,
						'orders_status_id' => $config['MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID'],
						'date_added' => 'now()',
						'customer_notified' => '0',
						'comments' => 'Payment successful Payeer'
					);
					
					$record = os_db_perform(DB_PREFIX . 'orders_status_history', $sq_a);

					if ($record != 1)
					{
						$message .= " - failed to change the status of the order to success\n";
						$err = true;
					}
					break;
					
				default:
					$sq_a = array('orders_status' => 3);
					os_db_perform(DB_PREFIX . 'orders', $sq_a, 'update', "orders_id='$orderid'");

					$sq_a = array(
						'orders_id' => $orderid,
						'orders_status_id' => 3,
						'date_added' => 'now()',
						'customer_notified' => '0',
						'comments' => 'Payment with Payeer failed'
					);
					
					$record = os_db_perform(DB_PREFIX . 'orders_status_history', $sq_a);
					
					if ($record == 1)
					{
						$message .= " - the payment status is not success\n";
					}
					else
					{
						$message .= " - failed to change the status of the order to fail\n";
					}
					
					$err = true;
					break;
			}
		}
	}
	
	if ($err)
	{
		$to = $config['MODULE_PAYMENT_PAYEER_EMAILERR'];

		if (!empty($to))
		{
			$message = "Failed to make the payment through the system Payeer for the following reasons:\n\n" . $message . "\n" . $log_text;
			$headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n" . 
			"Content-type: text/plain; charset=utf-8 \r\n";
			mail($to, 'Payment error', $message, $headers);
		}
		
		exit ($orderid . '|error');
	}
	else
	{
		exit ($orderid . '|success');
	}
}
?>