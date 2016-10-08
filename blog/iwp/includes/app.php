<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
@set_time_limit(300);
if(!defined('UPDATE_PAGE') && !defined('IS_AJAX_FILE')){
	@ob_start("ob_gzhandler");
}
 
@include_once(dirname(dirname(__FILE__))."/config.php");
if(!defined('APP_ROOT')){
	header('Location: install/index.php');
	exit;
}

define('DISABLE_MINIFY', 1);

if(file_exists(APP_ROOT."/_env.php")){
  @include_once(APP_ROOT."/_env.php");
}

include_once(APP_ROOT."/includes/db.php");
include_once(APP_ROOT."/includes/commonFunctions.php");
include_once(APP_ROOT."/includes/networkUtils.php");
include_once(APP_ROOT."/includes/registry.php");
include_once(APP_ROOT."/includes/TPL.php");
include_once(APP_ROOT."/includes/file.php");
include_once(APP_ROOT.'/includes/HTMLHeadCache.php');
include_once(APP_ROOT."/includes/manageCookies.php");
include_once(APP_ROOT."/controllers/appFunctions.php");
include_once(APP_ROOT."/controllers/manageClients.php");
include_once(APP_ROOT."/controllers/panelRequestManager.php");
include_once(APP_ROOT."/includes/DBPropertyConversion.php");
include_once(APP_ROOT."/controllers/TPLFunctions.php");
include_once(APP_ROOT."/cron/manageEasyCron.php");
include_once(APP_ROOT.'/cron/class-manage-IWP-cron.php');


//Static Data
include_once(APP_ROOT."/includes/httpErrorCodes.php");


$safe_mode_cron = '';
if(version_compare(PHP_VERSION, '5.3.0', '<')) {
	$safe_mode_cron = 'safe_mode=Off';
}
define('APP_PHP_CRON_CMD', 'php -q -d '.$safe_mode_cron);



Reg::set('config', $config);
unset($config);
Reg::set('hooks', array());

//DB connection starts here
DBConnectAndSetOptions();

if(defined('USER_SESSION_NOT_REQUIRED')) {
	$where = array(
          'query' => "accessLevel = ':accessLevel' ORDER BY userID ASC LIMIT 1",
          'params' => array(
               ':accessLevel'=>'admin'
           )
        );
    $userID = DB::getField("?:users", "userID", $where);
    if(empty($userID)){
            return false;
    }
    $GLOBALS['userID'] = $userID;
    $GLOBALS['offline'] = true;
}

$settings = DB::getRow("?:settings", "*", 1);
Reg::set('settings', unserialize($settings['general']));

$settings = Reg::get('settings');

$GLOBALS['isHTTPSDefined'] = defined('APP_HTTPS')?1:0;
$enableHTTPS = intval($settings['enableHTTPS']);
define('APP_HTTPS', $enableHTTPS);//1 => HTTPS on, 0 => HTTPS off
$APP_URL = 'http'.(APP_HTTPS == 1 ? 's' : '').'://'.rtrim(APP_DOMAIN_PATH,"/")."/";
define('APP_URL', $APP_URL);
define('IWP_SITE_URL', getOption('IWPSiteURL'));
protocolRedirect();

$supportURL = getOption('supportURL');
define('SUPPORT_URL', $supportURL);
define('GA_HELP_TRACKING', '?utm_source=application&utm_medium=userapp&utm_campaign=kb');
$GLOBALS['isFTPDefinedConfig'] = isFTPDefinedConfig();
$FTPCreds = @unserialize(getOption('FTPCredentials'));
if(!defined('APP_FTP_HOST'))	define('APP_FTP_HOST', (empty($FTPCreds['HOST'])) ? '': $FTPCreds['HOST'] );
if(!defined('APP_FTP_PORT'))	define('APP_FTP_PORT', (empty($FTPCreds['PORT'])) ? '21': $FTPCreds['PORT']);
if(!defined('APP_FTP_BASE'))	define('APP_FTP_BASE', (empty($FTPCreds['BASE'])) ? '' : $FTPCreds['BASE']);
if(!defined('APP_FTP_USER'))	define('APP_FTP_USER', (empty($FTPCreds['USER'])) ? '' : $FTPCreds['USER']);
if(!defined('APP_FTP_PASS'))	define('APP_FTP_PASS', (empty($FTPCreds['PASS'])) ? '' : $FTPCreds['PASS']);
if(!defined('APP_FTP_SSL'))		define('APP_FTP_SSL', (empty($FTPCreds['SSL'])) ? 0 : $FTPCreds['SSL']);
if(!defined('APP_FTP_USE_SFTP'))	define('APP_FTP_USE_SFTP', (empty($FTPCreds['SFTP'])) ? 0 : intval($FTPCreds['SFTP']));
if(!defined('APP_FTP_PASV'))	define('APP_FTP_PASV', (empty($FTPCreds['PASV'])) ? 0 : $FTPCreds['PASV']);

define('WP_CHANGELOG_URL', 'https://codex.wordpress.org/');
define('WP_PLUGIN_CHANGELOG_URL', 'https://wordpress.org/plugins/');
$getTimeZone = $settings["TIMEZONE"];
if(!$getTimeZone){
	$getTimeZone = ini_get('date.timezone');
	if ( empty($getTimeZone) && function_exists( 'date_default_timezone_set' ) ){
            @date_default_timezone_set( @date_default_timezone_get() );
	}
}else{
	@date_default_timezone_set( $getTimeZone);
}

//session

$cookiePath = parse_url(APP_URL, PHP_URL_PATH);

//@session_set_cookie_params(0, $cookiePath);
//@session_start();





//To prevent SQL Injection
// $_REQUEST_ORIGINAL = $_REQUEST;
// $_GET_ORIGINAL = $_GET;
// $_POST_ORIGINAL = $_POST;

// $_REQUEST = filterParameters($_REQUEST);
// $_GET = filterParameters($_GET);
// $_POST = filterParameters($_POST);



include_once(APP_ROOT."/controllers/processManager.php");

Reg::set('dateFormatLong', 'M d, Y @ h:ia');
Reg::set('dateFormatYearLess', 'M d @ h:ia');

checkUserLoggedInAndRedirect();

defineAppFullURL();


if(!defined('FORCED_AJAX_CALL_MIN_INTERVAL')){
	define('FORCED_AJAX_CALL_MIN_INTERVAL', 1);
}

//need user id for checkUserLoggedInAndRedirect() so this code move top to here
if(!defined('UPDATE_PAGE')){
//addons //reason why it is not used in update page(update process page) is if those addons are loaded, in update process it include the latest file to run particular addon's update process by including its class, which results in fatal error of class already exists.
loadActiveAddons();

clearUncompletedTask();

checkTriggerStatus();

checkBackupTasks();
if (function_exists('scheduleBackupFailedNotify')) {
  scheduleBackupFailedNotify();
}


}