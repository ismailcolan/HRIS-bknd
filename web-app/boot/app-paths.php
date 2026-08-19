<?php
/** Description: App core vars file set core system constants*/

define("DS", DIRECTORY_SEPARATOR);
define("WS", "/");

/** @constant FP - (Root) directory 
 * @filepath  <["DOCUMENT_ROOT"]> */
define("FP", dirname(dirname(__DIR__)) . DS);
/* Equally is to use $_SERVER["DOCUMENT_ROOT"], but this will not work for //server/project/ url,
 * useful for mobile devices, where the .hosts file editability is not always allowed
 * define("FP", filter_input(INPUT_SERVER, "DOCUMENT_ROOT", FILTER_SANITIZE_STRING) . '/'); */

/** @constant APP - App (web-app) directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/*/
define("APP", FP . "web-app/");

/** @constant APP_BOOT - App boot directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/boot/ */
define("APP_BOOT", APP . "boot/");

/** @constant APP_CONF - App config directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/config/ */
define("APP_CONF", APP . "config/");

/** @constant APP_HTML - App html directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/app-html/ */
define("APP_HTML", APP . "webapp-html/");

/** @constant APP_LOGS - App logs directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/webapp-logs/ */
define("APP_LOGS", APP . "webapp-logs/");

/** @constant APP_HELPERS - App html directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/helpers/ */
define("APP_HELPERS", APP . "helpers/");

/** @constant APP_LANG_FILENAME_TEMPLATE - App html directory
 * @filepath <["DOCUMENT_ROOT"]>/web-app/config/translations/app-translations-[:lang:].php */
define("APP_LANG_FILENAME_TEMPLATE", APP_CONF . "translations/app-translations-[:lang:].php");


