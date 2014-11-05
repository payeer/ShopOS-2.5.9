<?php
define('MODULE_PAYMENT_PAYEER_TEXT_TITLE', 'PAYEER (payeer.com)');
define('MODULE_PAYMENT_PAYEER_TEXT_DESCRIPTION', 'payeer.com');
define('MODULE_PAYMENT_PAYEER_STATUS_TITLE', 'Активность модуля');
define('MODULE_PAYMENT_PAYEER_STATUS_DESC', 'Вы хотите включить модуль?');
define('MODULE_PAYMENT_PAYEER_SORT_ORDER_TITLE', 'Сортировка');
define('MODULE_PAYMENT_PAYEER_SORT_ORDER_DESC', 'Чем ниже цифра, тем выше положение в списке платежных систем');
define('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID_TITLE', 'Статус заказа после оплаты');
define('MODULE_PAYMENT_PAYEER_ORDER_STATUS_ID_DESC', 'Укажите статус заказа, который будет установлен после оплаты');

define('MODULE_PAYMENT_PAYEER_MERCHANT_URL_TITLE', 'URL мерчанта');
define('MODULE_PAYMENT_PAYEER_MERCHANT_URL_DESC', 'url для оплаты в системе Payeer');
define('MODULE_PAYMENT_PAYEER_MERCHANT_ID_TITLE', 'Идентификатор магазина');
define('MODULE_PAYMENT_PAYEER_MERCHANT_ID_DESC', 'Идентификатор магазина, зарегистрированного в системе "PAYEER".<br/>Узнать его можно в <a href="http://www.payeer.com/account/">аккаунте Payeer</a>: "Аккаунт -> Мой магазин -> Изменить".');
define('MODULE_PAYMENT_PAYEER_SECRET_KEY_TITLE', 'Секретный ключ');
define('MODULE_PAYMENT_PAYEER_SECRET_KEY_DESC', 'Секретный ключ оповещения о выполнении платежа,<br/>который используется для проверки целостности полученной информации<br/>и однозначной идентификации отправителя.<br/>Должен совпадать с секретным ключем, указанным в <a href="http://www.payeer.com/account/">аккаунте Payeer</a>: "Аккаунт -> Мой магазин -> Изменить".');
define('MODULE_PAYMENT_PAYEER_IPFILTER_TITLE', 'IP фильтр');
define('MODULE_PAYMENT_PAYEER_IPFILTER_DESC', 'Список доверенных ip адресов, можно указать маску');
define('MODULE_PAYMENT_PAYEER_EMAILERR_TITLE', 'Email');
define('MODULE_PAYMENT_PAYEER_EMAILERR_DESC', 'Email для отправки ошибок оплаты');
define('MODULE_PAYMENT_PAYEER_LOGFILE_TITLE', 'Путь до файла для журнала оплат через Payeer (например, /payeer_orders.log)');
define('MODULE_PAYMENT_PAYEER_LOGFILE_DESC', 'Если путь не указан, то журнал не записывается');
define('MODULE_PAYMENT_PAYEER_DESCRIPTION_TITLE', 'Комментарий к оплате');
define('MODULE_PAYMENT_PAYEER_DESCRIPTION_DESC', 'Пояснение оплаты заказа');
?>
