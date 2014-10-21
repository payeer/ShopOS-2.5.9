<?php
define('MODULE_PAYMENT_PAYEER_TEXT_TITLE', 'PAYEER (payeer.com)');
define('MODULE_PAYMENT_PAYEER_TEXT_DESCRIPTION', 'payeer.com');
define('MODULE_PAYMENT_PAYEER_STATUS_TITLE', 'Activity module');
define('MODULE_PAYMENT_PAYEER_STATUS_DESC', 'You want to include the module?');
define('MODULE_PAYMENT_PAYEER_SORT_ORDER_TITLE', 'Sort');
define('MODULE_PAYMENT_PAYEER_SORT_ORDER_DESC', 'The lower the number the higher the position in the list of payment systems');
define('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID_TITLE', 'The status of the order after payment');
define('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID_DESC', 'Specify the status of the order, which will be installed after payment');

define('MODULE_PAYMENT_PAYEER_MERCHANT_URL_TITLE', 'The URL of the merchant');
define('MODULE_PAYMENT_PAYEER_MERCHANT_URL_DESC', 'url for payment in the system Payeer');
define('MODULE_PAYMENT_PAYEER_MERCHANT_ID_TITLE', 'ID store');
define('MODULE_PAYMENT_PAYEER_MERCHANT_ID_DESC', 'The store identifier registered in the system "PAYEER".<br/>it can be found in <a href="http://www.payeer.com/account/">Payeer account</a>: "Account -> My store -> Edit".');
define('MODULE_PAYMENT_PAYEER_SECRET_KEY_TITLE', 'Secret key');
define('MODULE_PAYMENT_PAYEER_SECRET_KEY_DESC', 'The secret key notification about the payment,<br/>which is used to verify the integrity of the received information<br/>and unambiguous identification of the sender.<br/>Must match the secret key specified in the <a href="http://www.payeer.com/account/">Payeer account</a>: "Account -> My store -> Edit".');
define('MODULE_PAYMENT_PAYEER_CURRENCY_TITLE', 'Currency');
define('MODULE_PAYMENT_PAYEER_CURRENCY_DESC', 'Currency code (RUB, USD, EUR, UAH)');
define('MODULE_PAYMENT_PAYEER_IPFILTER_TITLE', 'IP filter');
define('MODULE_PAYMENT_PAYEER_IPFILTER_DESC', 'The list of trusted ip addresses, you can specify the mask');
define('MODULE_PAYMENT_PAYEER_EMAILERR_TITLE', 'Email');
define('MODULE_PAYMENT_PAYEER_EMAILERR_DESC', 'Email to send payment errors');
define('MODULE_PAYMENT_PAYEER_LOGFILE_TITLE', 'Log');
define('MODULE_PAYMENT_PAYEER_LOGFILE_DESC', 'The query log from Payeer is stored in the file: /payeer_orders.log');
?>