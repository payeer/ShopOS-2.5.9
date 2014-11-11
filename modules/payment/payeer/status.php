<?php

include_once($_SERVER ['DOCUMENT_ROOT'] . '/includes/top.php');


if (isset($_POST['m_operation_id']) && isset($_POST['m_sign']))
{
	$orderid = $_POST['m_orderid'];
	
	$check_query = os_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '$orderid'");
	$flag = os_db_num_rows($check_query);
	
	if ($flag > 0) 
	{
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
			$config [ $data['configuration_key']] = $data ['configuration_value'];
		}
		
		$m_key = $config['MODULE_PAYMENT_PAYEER_SECRET_KEY'];
		
		$arHash = array(
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
			$m_key
		);
		
		$sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));
		
		// проверка принадлежности ip списку доверенных ip
		
		$list_ip_str = $config['MODULE_PAYMENT_PAYEER_IPFILTER'];

		if (!empty($list_ip_str)) 
		{
			$list_ip = explode(',', $list_ip_str);
			$this_ip = $_SERVER['REMOTE_ADDR'];
			$this_ip_field = explode('.', $this_ip);
			$list_ip_field = array();
			$i = 0;
			$valid_ip = FALSE;
			foreach ($list_ip as $ip)
			{
				$ip_field[$i] = explode('.', $ip);
				if ((($this_ip_field[0] ==  $ip_field[$i][0]) or ($ip_field[$i][0] == '*')) and
					(($this_ip_field[1] ==  $ip_field[$i][1]) or ($ip_field[$i][1] == '*')) and
					(($this_ip_field[2] ==  $ip_field[$i][2]) or ($ip_field[$i][2] == '*')) and
					(($this_ip_field[3] ==  $ip_field[$i][3]) or ($ip_field[$i][3] == '*')))
					{
						$valid_ip = TRUE;
						break;
					}
				$i++;
			}
		}
		else
		{
			$valid_ip = TRUE;
		}
			
		$log_text = 
			"--------------------------------------------------------\n".
			"operation id		".$_POST["m_operation_id"]."\n".
			"operation ps		".$_POST["m_operation_ps"]."\n".
			"operation date		".$_POST["m_operation_date"]."\n".
			"operation pay date	".$_POST["m_operation_pay_date"]."\n".
			"shop				".$_POST["m_shop"]."\n".
			"order id			".$_POST["m_orderid"]."\n".
			"amount				".$_POST["m_amount"]."\n".
			"currency			".$_POST["m_curr"]."\n".
			"description		".base64_decode($_POST["m_desc"])."\n".
			"status				".$_POST["m_status"]."\n".
			"sign				".$_POST["m_sign"]."\n\n";
				
		if (!empty($config['MODULE_PAYMENT_PAYEER_LOGFILE']))
		{
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . $config['MODULE_PAYMENT_PAYEER_LOGFILE'], $log_text, FILE_APPEND);
		}
		
		if ($_POST['m_sign'] == $sign_hash && $_POST['m_status'] == 'success' && $valid_ip)
		{
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

			if ($record == 1)
			{
				exit ($_POST['m_orderid'] . '|success');
			}
		}
		else
		{
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
				$to = $config['MODULE_PAYMENT_PAYEER_EMAILERR'];
				$subject = "Payment error";
				$message = "Failed to make the payment through the system Payeer for the following reasons:\n\n";
				
				if ($_POST["m_sign"] != $sign_hash)
				{
					$message .= " - Do not match the digital signature\n";
				}
				
				if ($_POST['m_status'] != "success")
				{
					$message .= " - The payment status is not success\n";
				}
				
				if (!$valid_ip)
				{
					$message .= " - the ip address of the server is not trusted\n";
					$message .= "   trusted ip: " . $list_ip_str . "\n";
					$message .= "   ip of the current server: " . $_SERVER['REMOTE_ADDR'] . "\n";
				}
				
				$message .= "\n" . $log_text;
				$headers = "From: no-reply@" . $_SERVER['HTTP_SERVER'] . "\r\nContent-type: text/plain; charset=utf-8 \r\n";
				mail($to, $subject, $message, $headers);

				exit ($_POST['m_orderid'] . '|error');
			}
		}
	}
}

?>