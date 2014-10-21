<?php

  class payeer 
  {
    var $code, $title, $description, $enabled;

    function payeer() 
	{
		global $order;

		$this->code = 'payeer';
		$this->title = MODULE_PAYMENT_PAYEER_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_PAYEER_TEXT_DESCRIPTION;
		$this->icon = 'logo_payeer.png';
		$this->icon_small = 'payeer.png';
		$this->sort_order = MODULE_PAYMENT_PAYEER_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_PAYEER_STATUS == 'True') ? true : false);

		if ((int)MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID > 0) 
		{
			$this->order_status = MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID;
		}
		
		// проверка принадлежности ip списку доверенных ip
		$list_ip_str = str_replace(' ', '', MODULE_PAYMENT_PAYEER_IPFILTER);

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

		$this->valid_ip = $valid_ip;
		
		if ($valid_ip)
		{
			$this->form_action_url = MODULE_PAYMENT_PAYEER_MERCHANT_URL;
		}
		else
		{
			$order_id_query = os_db_query("select max(orders_id) as max from " . TABLE_ORDERS);
			$order_id = os_db_fetch_array($order_id_query);
			$order_id = $order_id['max'];
			$m_orderid = $order_id + 1;
			$this->form_action_url = $_SERVER['HOST'] . '/checkout_process.php?result=fail&m_orderid=' . $m_orderid;
		}
    }

    function javascript_validation() 
	{
		return false;
    }

    function selection() 
	{
		return array('id' => $this->code, 'module' => $this->title);
    }

    function pre_confirmation_check() 
	{
		return false;
    }

    function confirmation() 
	{
		return false;
    }

    function process_button() 
	{
		global $order, $currencies, $currency, $osPrice;

		$m_shop = MODULE_PAYMENT_PAYEER_MERCHANT_ID;

		$order_id_query = os_db_query("select max(orders_id) as max from " . TABLE_ORDERS);
		$order_id = os_db_fetch_array($order_id_query);
		$order_id = $order_id['max'];
		$m_orderid = $order_id + 1;

		$m_amount = number_format($order->info['total'], 2, '.', '');

		$m_curr = MODULE_PAYMENT_PAYEER_CURRENCY;

		$m_desc = base64_encode('Payment order No. ' . $m_orderid);

		$m_key = MODULE_PAYMENT_PAYEER_SECRET_KEY;

		$arHash = array(
			$m_shop,
			$m_orderid,
			$m_amount,
			$m_curr,
			$m_desc,
			$m_key
		);

		$sign = strtoupper(hash('sha256', implode(':', $arHash)));
		
		if ($this->valid_ip)
		{
			$process_button_string = os_draw_hidden_field('m_shop', $m_shop) .
								   os_draw_hidden_field('m_orderid', $m_orderid) .
								   os_draw_hidden_field('m_amount', $order->info['total']) .
								   os_draw_hidden_field('m_curr', $m_curr) .
								   os_draw_hidden_field('m_desc', $m_desc) . 
								   os_draw_hidden_field('m_sign', $sign);
		}
		else
		{
			$process_button_string = '';
			
			$log_text = 
				"--------------------------------------------------------\n".
				"shop				" . $m_shop . "\n" .
				"order id			" . $m_orderid . "\n" .
				"amount				" . $m_amount."\n" .
				"currency			" . $m_curr."\n" .
				"description		" . base64_decode($m_desc) . "\n".
				"sign				" . $sign . "\n\n";

			$to = MODULE_PAYMENT_PAYEER_EMAILERR;
			$subject = "Payment error";
			$message = "Failed to make the payment through the system Payeer for the following reasons:\n\n";
			$message .= " - the ip address of the server is not trusted\n";
			$message .= "   trusted ip: " . MODULE_PAYMENT_PAYEER_IPFILTER . "\n";
			$message .= "   ip of the current server: " . $_SERVER['REMOTE_ADDR'] . "\n";
			$message .= "\n" . $log_text;
			$headers = "From: no-reply@" . $_SERVER['HTTP_SERVER'] . "\r\nContent-type: text/plain; charset=utf-8 \r\n";
			mail($to, $subject, $message, $headers);
		}
		
		return $process_button_string;
    }

    function before_process() 
	{
		return false;
    }

    function after_process()
	{
		if (isset($_GET['m_operation_id']) && isset($_GET['m_sign']))
		{
			$orderid = $_GET['m_orderid'];
			
			$m_key = MODULE_PAYMENT_PAYEER_SECRET_KEY;
			
			$arHash = array(
				$_GET['m_operation_id'],
				$_GET['m_operation_ps'],
				$_GET['m_operation_date'],
				$_GET['m_operation_pay_date'],
				$_GET['m_shop'],
				$_GET['m_orderid'],
				$_GET['m_amount'],
				$_GET['m_curr'],
				$_GET['m_desc'],
				$_GET['m_status'],
				$m_key
			);
			
			$sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));
			
			if ($_GET['m_sign'] == $sign_hash && $_GET['m_status'] == 'success')
			{
				$sq_a = array('orders_status' => MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID);
				os_db_perform(DB_PREFIX . 'orders', $sq_a, 'update', "orders_id='$orderid'");

				$sq_a = array(
					'orders_id' => $orderid,
					'orders_status_id' => MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID,
					'date_added' => 'now()',
					'customer_notified' => '0',
					'comments' => 'Payment successful Payeer'
				);
				os_db_perform(DB_PREFIX . 'orders_status_history', $sq_a);
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
				os_db_perform(DB_PREFIX . 'orders_status_history', $sq_a);
			}
		}
		elseif(isset($_GET['m_orderid']))
		{
			$m_orderid = $_GET['m_orderid'];
			
			$sq_a = array('orders_status' => 3);
			os_db_perform(DB_PREFIX . 'orders', $sq_a, 'update', "orders_id='$m_orderid'");

			$sq_a = array(
				'orders_id' => $m_orderid,
				'orders_status_id' => 3,
				'date_added' => 'now()',
				'customer_notified' => '0',
				'comments' => 'Payment with Payeer failed'
			);
			os_db_perform(DB_PREFIX . 'orders_status_history', $sq_a);
		}
		
		return false;
    }

    function output_error()
	{
		return false;
    }

    function check() 
	{
		if (!isset($this->_check)) 
		{
			$check_query = os_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYEER_STATUS'");
			$this->_check = os_db_num_rows($check_query);
		}
		return $this->_check;
    }

    function install() 
	{
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYEER_STATUS', 'True', '6', '1', 'os_cfg_select_option(array(\'True\', \'False\'), ', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_MERCHANT_URL', '//payeer.com/merchant/', '6', '8', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_MERCHANT_ID', '', '6', '0', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_SECRET_KEY', '', '6', '1', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_CURRENCY', 'RUB', '6', '2', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_IPFILTER', '', '6', '3', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_EMAILERR', '', '6', '4', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYEER_LOGFILE', 'True', '6', '5', 'os_cfg_select_option(array(\'On\', \'Off\'), ', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_SORT_ORDER', '1', '6', '7', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID', '0', '6', '0', 'os_cfg_pull_down_order_statuses(', 'os_get_order_status_name', now())");
    }

    function remove() 
	{
		os_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() 
	{
		return array(
			'MODULE_PAYMENT_PAYEER_STATUS',
			'MODULE_PAYMENT_PAYEER_MERCHANT_URL',
			'MODULE_PAYMENT_PAYEER_MERCHANT_ID',
			'MODULE_PAYMENT_PAYEER_SECRET_KEY', 
			'MODULE_PAYMENT_PAYEER_CURRENCY', 
			'MODULE_PAYMENT_PAYEER_IPFILTER', 
			'MODULE_PAYMENT_PAYEER_EMAILERR', 
			'MODULE_PAYMENT_PAYEER_LOGFILE', 
			'MODULE_PAYMENT_PAYEER_SORT_ORDER', 
			'MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID'
		);
    }
  }
?>