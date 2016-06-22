<?php
define('MODULE_PAYMENT_PAYEER_TEXT_TITLE', 'PAYEER (payeer.com)');
define('MODULE_PAYMENT_PAYEER_TEXT_DESCRIPTION', 'payeer.com');
define('MODULE_PAYMENT_PAYEER_STATUS_TITLE', 'Activity module');
define('MODULE_PAYMENT_PAYEER_STATUS_DESC', 'You want to include the module?');
define('MODULE_PAYMENT_PAYEER_SORT_ORDER_TITLE', 'Sort');
define('MODULE_PAYMENT_PAYEER_SORT_ORDER_DESC', 'The lower the number the higher the position in the list of payment systems');
define('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID_TITLE', 'The status of the order after payment');
define('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID_DESC', 'Specify the status of the order, which will be installed after payment');

define('MODULE_PAYMENT_PAYEER_MERCHANT_URL_TITLE', 'URL merchant');
define('MODULE_PAYMENT_PAYEER_MERCHANT_URL_DESC', 'URL for payment in the system Payeer, default https://payeer.com/merchant/');
define('MODULE_PAYMENT_PAYEER_MERCHANT_ID_TITLE', 'ID store');
define('MODULE_PAYMENT_PAYEER_MERCHANT_ID_DESC', 'The store identifier registered in the system "PAYEER".');
define('MODULE_PAYMENT_PAYEER_SECRET_KEY_TITLE', 'Secret key');
define('MODULE_PAYMENT_PAYEER_SECRET_KEY_DESC', 'The secret key of merchant');
define('MODULE_PAYMENT_PAYEER_IPFILTER_TITLE', 'IP filter');
define('MODULE_PAYMENT_PAYEER_IPFILTER_DESC', 'The list of trusted ip addresses, you can specify the mask');
define('MODULE_PAYMENT_PAYEER_EMAILERR_TITLE', 'Email');
define('MODULE_PAYMENT_PAYEER_EMAILERR_DESC', 'Email to send payment errors');
define('MODULE_PAYMENT_PAYEER_LOGFILE_TITLE', 'The path to the log file for payments via Payeer (for example, /payeer_orders.log)');
define('MODULE_PAYMENT_PAYEER_LOGFILE_DESC', 'If path is not specified, the log is not written');
?>