<?php
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
  // tell people trying to access this file directly goodbye...
  exit('This file can not be accessed directly...');
}

error_reporting(E_ERROR | E_PARSE); 
//error_reporting(E_ALL);

//serve as global variables
//SITE_ROOT contains the full path to the website folder
define("SITE_ROOT", dirname(dirname(__FILE__)));

//Change the include_path configuration option to enable PEAR
if((substr(strtoupper(PHP_OS), 0, 3)) == "WIN")
	define("PATH_SEPARATOR", ";");
else
	define("PATH_SEPARATOR", ":");

ini_set('include_path', SITE_ROOT.'/libs/PEAR'.PATH_SEPARATOR.ini_get('include_path'));

/* Setting for Tempest Watch Configuration
 * Sets contact email, SMS, and SMS provider
 * Email and/or SMS notification flags
 */
 define('EMAIL_NOTIFICATION', TRUE);
 define('SMS_NOTIFICATION', TRUE);
 define('CONTACT_EMAIL', 'me@yourname.name');
 define('CONTACT_SMS', '5555555555');
 define('CONTACT_SMS_PROVIDER', 'tmomail.net'); //TMobile. Please refer to https://en.wikipedia.org/wiki/SMS_gateway