<?php
/** Description: App resources file - sets various system constants  */

define("SITE_NAME","Human Road");
define("SITE_SLOGAN","");

define("APP_CONTROLLER", APP . "Controller/");
define("APP_MODEL", APP . "Model/");
define("APP_VIEW", APP . "View/");



define("HTML_INDENT","    ");

define("ASTERISK","<span title=\"Required field\">*</span>");
// 
define("USER_DATE","n/j/Y");
define("USER_DATE_TIME","n/j/Y g:i a");
define("USER_DATE_TIME_SHORT","n/j/Y\ng:i:s a");
define("USER_DATE_TIME_FLAT","n/j/Y g:i:s a");
define("USER_DATE_EXAMPLE","4/30/2018");
define("USER_DATETIME_FOR_FILE","j M Y, h\\h i\m\\i\\n a");
define("GLOBAL_PHP_DATE_ALT","m/d/Y");
define("GLOBAL_PHP_DATE_LONG","F d Y");
define("GLOBAL_PHP_DATETIME", USER_DATE." h:i a");

define("MYSQL_DATE","Y-m-d");
define("MYSQL_DATE_TIME","Y-m-d H:i:s");

define("LOCAL_PHP_DATETIME_LONG", "m/d/y h:i a");

define("GLOBAL_MYSQL_DATE","%m/%d/%y");
define("GLOBAL_MYSQL_DATE_LONG","%M %d %Y");
define("GLOBAL_MYSQL_DATETIME", GLOBAL_MYSQL_DATE." %h:%i %p");
define("GLOBAL_MYSQL_DATETIME_LONG", GLOBAL_MYSQL_DATE_LONG." %h:%i:%s %p");


define("GLOBAL_MONEY_SIGN", "$");


define("TXT_SAVE_OK",      "The data has been saved successfully.");
define("TXT_SAVE_ERROR",   "The data could not be saved. Please try again later.");
define("TXT_DELETE_CONFIRM", "Do you really want to delete this row and connected data?");
define("TXT_DELETE_OK",    "The selected items were successfully deleted.");
define("TXT_DELETE_ERROR", "The selected items could not be deleted. Please try again later.");
define("TXT_DEACTIVATE_OK",    "The selected items were successfully deactivated.");
define("TXT_DEACTIVATE_ERROR", "The selected items could not be deactivated. Please try again later.");
define("TXT_REACTIVATE_OK",    "The selected items were successfully activated.");
define("TXT_REACTIVATE_ERROR", "The selected items could not be activated. Please try again later.");
define("TXT_INCORRECT_ID", "The request item could not be found.<br /><small>Please check your url and ensure the item is not deleted.</small>");
define("TXT_DEACTIVATE_CONFIRM", "Do you really want to de-activate this row?");
define("TXT_DELETE_CONFIRM_CART_ITEM", "Do you really want to delete this cart item?");
define("TXT_ITEM_ADDED_TO_CART", "The item has been added to your cart.");
define("TXT_ORDER_SUBITTED_OK",      "The order has been submitted successfully.");
define("TXT_ORDER_SUBITTED_ERROR",   "The order data could not be saved. Please try again later.");
define("TXT_ORDER_REVIEW", "Please review your order and click SEND ORDER to finalize it.");

define("TXT_SEND_OK",      "Your message was sent successfully.");
define("TXT_SEND_ERROR",   "Your message could not be sent. Please try again later.");


