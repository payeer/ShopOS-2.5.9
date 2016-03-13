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

		$this->form_action_url = MODULE_PAYMENT_PAYEER_MERCHANT_URL;
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

		$order_id_query = os_db_query("SELECT MAX(orders_id) AS max FROM " . TABLE_ORDERS);
		$order_id = os_db_fetch_array($order_id_query);
		$order_id = $order_id['max'];
		$m_orderid = $order_id + 1;
		$m_amount = number_format($order->info['total'], 2, '.', '');
		$m_curr = $order->info['currency'] == 'RUR' ? 'RUB' : $order->info['currency'];
		$m_desc = base64_encode('111');
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
		
		$process_button_string = os_draw_hidden_field('m_shop', $m_shop) .
		os_draw_hidden_field('m_orderid', $m_orderid) .
		os_draw_hidden_field('m_amount', $order->info['total']) .
		os_draw_hidden_field('m_curr', $m_curr) .
		os_draw_hidden_field('m_desc', $m_desc) . 
		os_draw_hidden_field('m_sign', $sign);

		return $process_button_string;
    }

    function before_process() 
	{
		return false;
    }

    function after_process()
	{
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
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_MERCHANT_URL', 'https://payeer.com/merchant/', '6', '8', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_MERCHANT_ID', '', '6', '0', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_SECRET_KEY', '', '6', '1', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_IPFILTER', '', '6', '3', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_EMAILERR', '', '6', '4', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_LOGFILE', '', '6', '5', now())");
		os_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYEER_SORT_ORDER', '1', '6', '6', now())");
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
			'MODULE_PAYMENT_PAYEER_IPFILTER', 
			'MODULE_PAYMENT_PAYEER_EMAILERR', 
			'MODULE_PAYMENT_PAYEER_LOGFILE', 
			'MODULE_PAYMENT_PAYEER_SORT_ORDER', 
			'MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID'
		);
    }
}
?>