<?php
#Show Error
define('APP_SHOW_ERROR', true);

@ini_set('display_errors', (APP_SHOW_ERROR) ? 'On' : 'Off');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
if(defined('E_DEPRECATED')) {
error_reporting(error_reporting() & ~E_DEPRECATED);
}
define('SHOW_SQL_ERROR', APP_SHOW_ERROR);

define('APP_VERSION', '2.9.1.1');
define('APP_INSTALL_HASH', '4342e5762243bce975ecb2fdd9c9cbb8b3873fc1');

define('APP_ROOT', dirname(__FILE__));
define('APP_DOMAIN_PATH', 'zachhardesty.com/iwp/');

define('EXECUTE_FILE', 'execute.php');
define('DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT', 180);//Request to client wp

$config = array();
$config['SQL_DATABASE'] = 'fcshar5_iwp284';
$config['SQL_HOST'] = 'localhost';
$config['SQL_USERNAME'] = 'fcshar5_iwp284';
$config['SQL_PASSWORD'] = '38)m41pSI(';
$config['SQL_PORT'] = '3306';
$config['SQL_TABLE_NAME_PREFIX'] = 'iwph9il_';
