<?php

function errorReporting(){
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
	if(defined('E_DEPRECATED')) {
		error_reporting(error_reporting() & ~E_DEPRECATED);
	}
}

function defineGlobalVar(){
	$GLOBALS['progressBar'] = 0;
}

function pluginInstallerGetAdminEmail(){
	includeWPConfigFile();
	if (function_exists('is_multisite') && is_multisite()) {
		$admin_email = get_site_option( 'admin_email' );
	} else {
		$admin_email = get_option('admin_email');
	}
	return $admin_email;
}

function definePluginInstaller(){
	if (defined('PLUGIN_INSTALLER')) {
		return false;
	}
	define('PLUGIN_INSTALLER', '1');
}

function pluginInstaller(){
	if(defined('PLUGIN_INSTALLER')){
		echo "&pluginInstaller";
	}
}

function setMaxExecutionTime(){
	$GLOBALS['maximumExecutionTime'] = 300 + ini_get('max_execution_time');
	@set_time_limit($maximumExecutionTime);//300 => 5 mins
}

function defineConstants(){
	//about current installation
	define('INSTALL_APP_VERSION', '2.9.1.1');
	define('APP_INSTALL_ROOT', dirname(__FILE__));
	define('APP_ROOT', APP_INSTALL_ROOT.'/..');
	define('REQUIRED_MINIMUM_MYSQL_VERSION', '5.0.2'); //4.1.2
	define('COOKIE_EXPIRE_LIMIT', time() + (60 * 15) ); // 15 mins
}

function getRootURL(){
	if(!isset($_SERVER['REQUEST_URI'])){
		$serverrequri = $_SERVER['PHP_SELF'];
	} else{
		$serverrequri = $_SERVER['REQUEST_URI'];
	}
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$serverProtocol = explode('/', $_SERVER["SERVER_PROTOCOL"]);
	$protocol = strtolower(reset($serverProtocol));
	$protocol .= $s;
	$port = ($_SERVER["SERVER_PORT"] == "80" || $_SERVER["SERVER_PORT"] == "443") ? "" : (":".$_SERVER["SERVER_PORT"]);

	//port issue - fixed.
	$host = explode(":", $_SERVER['HTTP_HOST']);
	$fullURL = $protocol."://".$host[0].$port.$serverrequri;

	//old
	//$fullURL = $protocol."://".$_SERVER['HTTP_HOST'].$port.$serverrequri;

	$fullURLParts = explode('/', $fullURL);
	array_pop($fullURLParts);
	array_pop($fullURLParts);
	return implode('/', $fullURLParts);
}

function executeSchemaQueries($schemaFile, $tableNamePrefix, $status = NULL, $type = NULL){
	global $startedTime;
	$startedTime = time();
	$schemaQueries = getInstallschemaQueries($schemaFile, $tableNamePrefix);
	if(empty($schemaQueries)){
		installDie("No SQL queries found for installing");
	}
	$tablesCount = sizeof($schemaQueries);
	$progressRate = getProgressRate($tablesCount);
	$flag = 0;
	foreach($schemaQueries as $query){
		if ($type != 'cpanel') {
			$flag++;
			$GLOBALS['progressBar'] = $GLOBALS['progressBar'] + $progressRate;
			if (!isAjaxTimeExceed(time())) {
				printStatus('executeSchemaQueries', $flag, $GLOBALS['progressBar']);
			}
			if ($status != 0 && $status != NULL && $status > $flag ) {
				continue;
			}
		}

		DB::doQuery($query) or installDie('Mysql error: (' . DB::errorNo().') '.DB::error().'<br>'.$query);
	}
	if ($type != 'cpanel') {
		printStatus("executeSchemaQueries" , 'completed', $GLOBALS['progressBar']);
	}
	return true;
}

function printStatus($process, $msg, $progressBar = 0){
	if ($progressBar == 0) {
		$result = array($process => $msg);
	} else {
		$result = array($process => $msg , 'progressBar' => $progressBar);
	}
	echo json_encode($result);
	exit;
}

function getProgressRate($tablesCount){
	$remainingProgressWidth = 250; // totally 300 ( create user = 25 , create config file = 25 , remaining for tables)
	return $remainingProgressWidth/$tablesCount;
}

function getInstallschemaQueries($schemaFile, $tableNamePrefix){
	$tableEnv = DB::getSQLTableEnv();
	$schemaQueries = setSQLEnvInQueries($schemaFile, $tableNamePrefix, $tableEnv);
	return $schemaQueries;
}

function setSQLEnvInQueries($schemaFile, $tableNamePrefix, $tableEnv){
	include $schemaFile;
	return $schemaQueries;
}

function modifyConfigFile($file, $config, $type){

	$appInstallHash = sha1(APP_INSTALL_ROOT.uniqid('', true));
	if(isset($config['appDomainPath'])){
		$appDomainPath = $config['appDomainPath'];
	}else{
		$appFullURL = getRootURL();
		$appFullURLArray = explode('//', $appFullURL);
		$appDomainPath = $appFullURLArray[1];
	}

$fileContent = '<?php
#Show Error
define(\'APP_SHOW_ERROR\', true);

@ini_set(\'display_errors\', (APP_SHOW_ERROR) ? \'On\' : \'Off\');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
if(defined(\'E_DEPRECATED\')) {
error_reporting(error_reporting() & ~E_DEPRECATED);
}
define(\'SHOW_SQL_ERROR\', APP_SHOW_ERROR);

define(\'APP_VERSION\', \''.INSTALL_APP_VERSION.'\');
define(\'APP_INSTALL_HASH\', \''.$appInstallHash.'\');

define(\'APP_ROOT\', dirname(__FILE__));
define(\'APP_DOMAIN_PATH\', \''.$appDomainPath.'/\');

define(\'EXECUTE_FILE\', \'execute.php\');
define(\'DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT\', 180);//Request to client wp

$config = array();
$config[\'SQL_DATABASE\'] = \''.IWPAddSlashes($config['dbName']).'\';
$config[\'SQL_HOST\'] = \''.IWPAddSlashes($config['dbHost']).'\';
$config[\'SQL_USERNAME\'] = \''.IWPAddSlashes($config['dbUser']).'\';
$config[\'SQL_PASSWORD\'] = \''.IWPAddSlashes($config['dbPass']).'\';
$config[\'SQL_PORT\'] = \''.$config['dbPort'].'\';
$config[\'SQL_TABLE_NAME_PREFIX\'] = \''.IWPAddSlashes($config['dbTableNamePrefix']).'\';
';

	file_put_contents($file, $fileContent) or installDie('Unable to modify the config file.');
	if ($type != 'cpanel') {
		printStatus("modifyConfigFile" , 'completed', 275);
	}
}

function createConfigFileIfNotExists(){
	$configFile = APP_INSTALL_ROOT.'/../config.php';
	if(!file_exists($configFile)){
		@file_put_contents($configFile, '');
	}
}

function echoStatusAndExit($status){
	$statusMsg = 'error';
	if($status){
		$statusMsg = 'completed';
	}
	if($statusMsg == 'completed'){ exit; }
}

function installDie($msg){
	$msg = 'Error: '. $msg;
	$result = array('error' => trim(preg_replace('/\s\s+/', ' ', $msg)));
	echo json_encode($result);
	echoStatusAndExit(false);
	die();
}

function checkFinal($key){
	if($GLOBALS['check']['final'][$key] === true){
		$class = 'success';
	} else if($GLOBALS['check']['final'][$key] === 1){
		$class = 'warning';
	} else{
		$class = 'fail';
	}
	echo $class;
}

function checkAvailable($key, $type){
	if ($type == 'statusMsg') {
		echo !empty($GLOBALS['check']['available'][$key]) ? 'ENABLED' : 'DISABLED';
	} else if($type == 'errorClass'){
		if ($key == 'PHP_SAFE_MODE') {
			echo !($GLOBALS['check']['available'][$key]) ? '' : 'error';
			return ;
		}
		 echo !empty($GLOBALS['check']['available'][$key]) ? '' : 'error';
	}
}

function indexPagesClass($indexStep){
	$steps = array();
	$steps[0] = '';
	$steps[1] = 'checkRequirement';
	$steps[2] = 'enterDetails';
	$steps[3] = 'createLogin';
	$steps[4] = 'install';

	$currentStep = empty($_GET['step']) ? '' : $_GET['step'];

	$currentStepPosition = array_search($currentStep, $steps);

	$indexStepPosition = array_search($indexStep, $steps);

	if($indexStepPosition < $currentStepPosition ){
		echo 'rep_sprite_backup completed';
	} else if($indexStepPosition === $currentStepPosition ){
		echo  'rep_sprite_backup current linkDisabled';
	} else{
		echo 'linkDisabled';
	}
}

function checkPHPRequirements(){

	$check = array();
	$check['required']['PHP_VERSION'] 		= '5.2.4';
	$check['required']['PHP_SAFE_MODE'] 	= 0;//should be in off
	$check['required']['PHP_WITH_MYSQL'] 	= 1;
	$check['required']['PHP_WITH_OPEN_SSL'] = 1;
	$check['required']['PHP_WITH_CURL'] 	= 1;
	$check['required']['PHP_FILE_UPLOAD'] 	= 1;
	$check['required']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE'] = 1;

	//======================================================================>

	$check['available']['PHP_VERSION'] 			= phpversion();

	$phpSafeMode = ini_get('safe_mode');
	$check['available']['PHP_SAFE_MODE'] 		= !empty($phpSafeMode);
	$check['available']['PHP_WITH_MYSQL'] 		= (class_exists('mysqli') or function_exists('mysql_connect'));
	$check['available']['PHP_WITH_OPEN_SSL'] 	= function_exists('openssl_verify');
	$check['available']['PHP_WITH_CURL'] 		= function_exists('curl_exec');
	$check['available']['PHP_FILE_UPLOAD'] 		= ini_get('file_uploads') == 1 ? true : false;

	//checking PHP_MAX_EXECUTION_TIME_CONFIGURABLE
	$check['available']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE'] = 0;
	if($GLOBALS['maximumExecutionTime'] == ini_get('max_execution_time')){
		$check['available']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE'] = 1;
	}else if(ini_get('max_execution_time') == 0){
		//If set to zero, no time limit is imposed
		$check['available']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE'] = 1;
	}

	$check['final']['PHP_VERSION'] 		    = (version_compare($check['available']['PHP_VERSION'], $check['required']['PHP_VERSION'])  >= 0) ? true: false;
	$check['final']['PHP_SAFE_MODE'] 		= ($check['required']['PHP_SAFE_MODE'] == $check['available']['PHP_SAFE_MODE']) ? true: false;
	$check['final']['PHP_WITH_MYSQL'] 		= ($check['required']['PHP_WITH_MYSQL'] == $check['available']['PHP_WITH_MYSQL']) ? true: false;
	$check['final']['PHP_WITH_OPEN_SSL'] 	= ($check['required']['PHP_WITH_OPEN_SSL'] == $check['available']['PHP_WITH_OPEN_SSL']) ? true: 1;//1 = optional
	$check['final']['PHP_WITH_CURL']		= ($check['required']['PHP_WITH_CURL'] == $check['available']['PHP_WITH_CURL']) ? true: false;
	$check['final']['PHP_FILE_UPLOAD']		= ($check['required']['PHP_FILE_UPLOAD'] == $check['available']['PHP_FILE_UPLOAD']) ? true: false;
	$check['final']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE']  = ($check['required']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE'] == $check['available']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE'] ) ? true: false;
	$GLOBALS['check'] = $check;
	return $check;

}

function isRequirementsSatisfied(){
	$check = checkPHPRequirements();
	if( $check['final']['PHP_VERSION'] == 1 &&  $check['final']['PHP_SAFE_MODE'] ==1 &&  $check['final']['PHP_WITH_MYSQL']  == 1 && $check['final']['PHP_WITH_CURL']  == 1 && $check['final']['PHP_MAX_EXECUTION_TIME_CONFIGURABLE']  == 1 ) {
		return true;
	}
	return false;
}

function mysqlConnectAndCheck(){
	global $config;
	require_once(APP_INSTALL_ROOT.'/../includes/db.php');

	if(isset($_GET['dbHost']) && !empty($_GET['dbHost'])){

		$config['dbHost'] = $_GET['dbHost'];
		$config['dbUser'] = $_GET['dbUser'];
		$config['dbPass'] = $_GET['dbPass'];
		$config['dbName'] = $_GET['dbName'];
		$config['dbPort'] = $_GET['dbPort'];
		$config['dbTableNamePrefix'] = $_GET['dbTableNamePrefix'];
	} else if(!empty($_POST['dbHost']) && isset($_POST['dbHost'])){
		$config['dbHost'] = $_POST['dbHost'];
		$config['dbUser'] = $_POST['dbUser'];
		$config['dbPass'] = $_POST['dbPass'];
		$config['dbName'] = $_POST['dbName'];
		$config['dbPort'] = $_POST['dbPort'];
		$config['dbTableNamePrefix'] = $_POST['dbTableNamePrefix'];
	}
	checkTablePrefixFormat($config['dbTableNamePrefix']);
	$isConnected = DB::connect($config['dbHost'], $config['dbUser'], $config['dbPass'], $config['dbName'], $config['dbPort']);
	if(!$isConnected){
		$errNo = DB::connectErrorNo();
		$err = DB::connectError();
		if (empty($errNo) && empty($err)) {
			installDie('Mysql connect error: cannot connect to database server. Please check credentials.');
		}
		installDie('Mysql connect error: (' . $errNo.') '.$err);
	}
	DB::setTableNamePrefix($config['dbTableNamePrefix']);
	DB::setPrintErrorOnFailure($isPrint=false);
	//getting MYSQL_VERSION
	$mysqlVersion = DB::version();
	if(!$mysqlVersion){
		installDie('Unable to fetch Mysql version no');
	}
	if(version_compare($mysqlVersion, REQUIRED_MINIMUM_MYSQL_VERSION) < 0){
		installDie('Minimum MySQL Version required is '.REQUIRED_MINIMUM_MYSQL_VERSION);
	}
}

function storeDBCredsInCookies($config){
	unset($config['dbPass']);
	manageCookies::cookieset('DBcreds', $config, array("expire" => COOKIE_EXPIRE_LIMIT));
}

function isAjaxTimeExceed($currentTime){
	global $startedTime;
	$maxExecutionTime = $startedTime + 1; // 1 second
	if ($currentTime >= $maxExecutionTime) {
		return false;
	}
	return true;
}

function installPanel($config, $type = NULL){
	createConfigFileIfNotExists();
	if(!is_writable(APP_INSTALL_ROOT.'/../config.php')){
		installDie("Please set config.php file permission to 666 or writable by script. <a style='text-decoration: underline;' id='reStartInstall'>try again</a>");
	}

	pluginInstallerEmailSubcribe($config);

	//insert tables
	executeSchemaQueries(APP_INSTALL_ROOT.'/install.sql', $config['dbTableNamePrefix'], NULL, $type);
	//create config file
	createConfigFile($config, $type);
	//create first user
	createSuperAdmin($config, $type);

	pluginInstallerEmailIWPCreds($config);
	// remove install folder
	removeInstallFolder($config, $type);
}

function removeInstallFolder($config, $type = NULL){
	$installFolderPath = APP_INSTALL_ROOT;
	if (defined('PLUGIN_INSTALLER')) {
		includeWPConfigFile();
		$result = update_option( 'iwp_install_url', $config['IWPDomainPath']);
		pluginInstallerEmailIWPCreds($config);
		$deleteInstallerPlugin = manageCookies::cookieGet('deletePlugin');
		if ($deleteInstallerPlugin == 1) {
			deleteInstallerPlugin();
		}
	}
	removeAllPanelCookies();
	deleteFolder($installFolderPath, $type);
}

function checkTablePrefixFormat($prefix){
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) ){
		installDie("\"Table Prefix\" can only contain numbers, letters, and underscores.");
	}
}
function IWPAddSlashes($str){
	return addslashes($str);
}

function removeAllPanelCookies(){
	manageCookies::cookieAllUnset();
}

function deleteInstallerPlugin(){
	includeWPConfigFile();
	@include_once(ABSPATH . 'wp-admin/includes/file.php');
	@include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	deactivate_plugins(array("iwp-admin-panel-installer/init.php"));
	delete_plugins(array("iwp-admin-panel-installer/init.php"));
}

function createSuperAdmin($config, $type = NULL){
	$isDone = DB::insert("?:users", array('email' => $config['email'], 'accessLevel' => 'admin', 'password' => sha1($config['password']), 'notifications' => 'a:1:{s:23:"updatesNotificationMail";a:5:{s:9:"frequency";s:6:"weekly";s:11:"coreUpdates";s:1:"1";s:13:"pluginUpdates";s:1:"1";s:12:"themeUpdates";s:1:"1";s:18:"translationUpdates";s:1:"1";}}'));
	$installedTime = time();
	DB::update("?:options", array('optionValue' => $installedTime), "optionName = 'installedTime'");
	DB::update("?:options", array('optionValue' => ($installedTime + 86400)), "optionName = 'anonymousDataNextSchedule'");
	DB::update("?:options", array('optionValue' => $installedTime), "optionName = 'lastCronNotRunAlert'"); // assume cron notified for new users

	$r = DB::getRow("select * from ?:settings");
	$settings =  unserialize($r['general']);
	$timezone = ini_get('date.timezone');
	if ( !empty($timezone)){
		$settings['TIMEZONE'] = $timezone;
	}
	DB::update("UPDATE ?:settings SET `general` = '".serialize($settings)."'");

	if(!$isDone){
		installDie('Unable to create user.');
	}
	if ($type != 'cpanel') {
		printStatus("userCreated" , 'completed', 275);
	}
}

function createConfigFile($config, $type = NULL){
	modifyConfigFile(APP_INSTALL_ROOT.'/../config.php', $config, $type);
}

function continueInstall($type, $status){
	global $config;
	checkPluginInstaller();
	getConfigData();
	mysqlConnectAndCheck();
	if($type == 'executeSchemaQueries'){
		checkIWPInstalledAlready();
		executeSchemaQueries(APP_INSTALL_ROOT.'/install.sql', $config['dbTableNamePrefix'], $status);
	} else if($type == "modifyConfigFile"){
		checkIWPInstalledAlready();
		createConfigFile($config);
	} else if($type == 'userCreated'){
		createSuperAdmin($config);
	} else if($type == 'removeInstallFolder'){
		removeInstallFolder($config);
	}
}

function getConfigData(){
	global $config;
	$config['dbHost'] = $_POST['dbHost'];
	$config['dbUser'] = $_POST['dbUser'];
	$config['dbPass'] = $_POST['dbPass'];
	$config['dbName'] = $_POST['dbName'];
	$config['dbPort'] = $_POST['dbPort'];
	$config['dbTableNamePrefix'] = $_POST['dbTableNamePrefix'];
	$config['email'] = $_POST['email'];
	$config['password'] = $_POST['password'];
	if (defined('PLUGIN_INSTALLER')) {
		$tempConfig = manageCookies::cookieGet('config');
		$config['IWPDomainPath'] = $tempConfig['IWPDomainPath'];
		$config['emailSubscribe'] = $tempConfig['emailSubscribe'];
	}
}

function startInstall(){
	global $config;
	checkIWPInstalledAlready();
	checkPluginInstaller();
	getConfigData();
	mysqlConnectAndCheck();
	installPanel($config);
}

function checkIWPInstalledAlready(){
	if(isIWPAlreadyInstalled()){
		installDie("It looks like the admin panel is already installed here. To re-install, empty the config.php file, save it and retry.");
	}
}

function checkPluginInstaller(){
	$folderPath = manageCookies::cookieGet('folderPath');
	if (isset($folderPath) && !empty($folderPath)) {
		definePluginInstaller();
	}
}

function storeConfigInCookies(){
	if($_GET['step'] != 'install'){
		return false;
	}
	global $config;

	mysqlConnectAndCheck();

	if (defined('PLUGIN_INSTALLER')) {
		includeWPConfigFile();
		$folderPath = manageCookies::cookieGet('folderPath');
		$IWPDomainPath = get_site_url().'/'.$folderPath;
		$config['IWPDomainPath'] = $IWPDomainPath;
		$config['emailSubscribe'] = $_POST['emailSubscribe'];
	}

	$config['dbTableNamePrefix'] = $_POST['dbTableNamePrefix'];
	$config['email'] = $_POST['email'];
	$config['password'] = $_POST['password'];
	$cookieConfig = $config;
	unset($cookieConfig['dbPass'], $cookieConfig['password']);
	manageCookies::cookieset('config', $cookieConfig, array("expire" => COOKIE_EXPIRE_LIMIT));
}

function pluginInstallerEmailSubcribe($config){
	if (!defined('PLUGIN_INSTALLER')) {
		return false;
	}
	$subscribeStatus = $config['emailSubscribe'];
	if ($subscribeStatus != 1) {
		return false;
	}

	$mailChimpURL = "http://infinitewp.staging.wpengine.com/mailchimp.php";
	$ch = curl_init();
	$curlConfig = array(
	CURLOPT_URL            => $mailChimpURL,
	CURLOPT_POST           => true,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POSTFIELDS     => array(
		'email' =>  $config['email'],
		'group' =>  'plugin',
		)
	);
	curl_setopt_array($ch, $curlConfig);
	$result = curl_exec($ch);
	curl_close($ch);
}

function pluginInstallerEmailIWPCreds($config){

	if (!defined('PLUGIN_INSTALLER')) {
		return false;
	}
	includeWPConfigFile();
	$mailContent = '<body bgcolor="#F1F1F1  ">
<div style="background-color:#fff; margin:20px auto; width:600px; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size:13px; height: 192px; border: 1px solid #DDD; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1)  ;">
  <h3 style="border-bottom: 1px solid #EEE; font-size: 14px; padding: 8px 12px; margin: 0; line-height: 1.4; text-align:center;">Your InfiniteWP admin panel details</h3>
  <table width="100%" border="0" style="margin:20px 0" cellpadding="10">
    <tbody>
      <tr>
        <td width="30%" align="right">Link</td>
        <td><strong><a href="'.$config['IWPDomainPath'].'/" target="_blank">'.$config['IWPDomainPath'].'/</a></strong></td>
      </tr>
      <tr>
        <td align="right">Email</td>
        <td><strong>'.$config['email'].'</strong></td>
      </tr>
      <tr>
        <td align="right">Password</td>
        <td><strong>'.$config['password'].'</strong></td>
      </tr>
    </tbody>
  </table>
</div>
</body>';
	add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	wp_mail($config['email'], 'Welcome to InfiniteWP - You\'re ready to manage all your Wordpress sites Centrally', $mailContent);
}

function deleteFolder($src, $type) {
	if (!file_exists($src)) {
		return false;
	}
    $dir = @opendir($src);
    while(false !== ( $file = @readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( @is_dir($src . '/' . $file) ) {
               deleteFolder($src . '/' . $file, $type);
            }
            else {
                @unlink($src . '/' . $file);
            }
        }
    }
    @closedir($dir);
   $status = @rmdir($src);
    if ($status == false) {
		DB::insert("?:options", array('optionName' => 'lastRemoveInstallFolderNotified', 'optionValue' => time()));
		if ($type != 'cpanel') {
			printStatus("removeInstallFolder" , 'failed');
		}
	} else{
		if ($type != 'cpanel') {
			printStatus("removeInstallFolder" , 'success');
		}
	}
}

function isIWPAlreadyInstalled(){
	@include_once APP_INSTALL_ROOT.'/../config.php';
	if(defined('APP_INSTALL_HASH')){
		return true;
	}
	return false;
}

function includeWPConfigFile(){
	$ABSPATH = manageCookies::cookieGet('ABSPATH');
	if(!empty($ABSPATH)){
		include_once manageCookies::cookieGet('ABSPATH')."/wp-config.php";
	}
}
