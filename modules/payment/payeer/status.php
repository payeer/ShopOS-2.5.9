<?php

include_once($_SERVER ['DOCUMENT_ROOT'] . '/includes/top.php');


if (isset($_POST['m_operation_id']) && isset($_POST['m_sign']))
{
	$orderid = $_POST['m_orderid'];
	
	$q = os_db_query("
		SELECT `configuration_key`, `configuration_value` FROM `" . TABLE_CONFIGURATION . "` WHERE `configuration_key` IN (
		'MODULE_PAYMENT_PAYEER_SECRET_KEY',
		'MODULE_PAYMENT_PAYEER_LOGFILE',		
		'MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID'
		)
	");

	while ($data = os_db_fetch_array($q))
	{
		$config [ $data['configuration_key']] = $data ['configuration_value'];
	}
	
	$m_key = $config ['MODULE_PAYMENT_PAYEER_SECRET_KEY'];
	
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
			
	if ($config['MODULE_PAYMENT_PAYEER_LOGFILE'] == 'On')
	{
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/payeer_orders.log', $log_text, FILE_APPEND);
	}
	
	if ($_POST['m_sign'] == $sign_hash && $_POST['m_status'] == 'success')
	{
		echo $_POST['m_orderid'] . '|success';
		exit;
	}
	else
	{
		echo $_POST['m_orderid'] . '|error';
		
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
		$message .= "\n" . $log_text;
		$headers = "From: no-reply@" . $_SERVER['HTTP_SERVER']."\r\nContent-type: text/plain; charset=utf-8 \r\n";
		mail($to, $subject, $message, $headers);
				
		exit;
	}
}

?>