<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
require_once('installView.php');
require_once('installFunctions.php');

errorReporting();
defineConstants();
defineGlobalVar();
setMaxExecutionTime();

require_once(APP_INSTALL_ROOT.'/../includes/manageCookies.php'); // should be called after defineConstants();

if(isset($_GET['dbName'])){
	global $config;
	mysqlConnectAndCheck();
	storeDBCredsInCookies($config);
	exit;
}

if (isset($_GET['pluginInstaller'])) {
	definePluginInstaller();
	if (isset($_GET['ABSPATH'])) {
		manageCookies::cookieset('ABSPATH', $_GET['ABSPATH'], array("expire" => COOKIE_EXPIRE_LIMIT));
	}
	if (isset($_GET['folderPath'])) {
		manageCookies::cookieset('folderPath', $_GET['folderPath'], array("expire" => COOKIE_EXPIRE_LIMIT));
	}
	if (isset($_GET['deletePlugin'])) {
		manageCookies::cookieset('deletePlugin', $_GET['deletePlugin'], array("expire" => COOKIE_EXPIRE_LIMIT));
	}
}


if($_GET['step'] == 'install'){
	storeConfigInCookies();
}

if ($_POST['process'] == 'startInstall') {
	startInstall();
	exit;
}

if ($_POST['process'] == 'continueInstall') {
	continueInstall($_POST['type'], $_POST['status']);
	exit;
}

$continueOnClick = '';
$continueLink = '';
$IDDatabase = '';
$continueClass = '';
$continueDivClass = '';
if($GLOBALS['automatedCPanelInstall']) return true;
if(empty($_GET['step'])){
	$check = checkPHPRequirements();
	$continueLink = 'checkRequirement';
} else if($_GET['step'] == 'checkRequirement'){
	$check = checkPHPRequirements();
	validateRequirements();
	$continueLink = 'enterDetails';
} else if($_GET['step'] == 'enterDetails'){
	$continueLink = 'createLogin';
} else if($_GET['step'] == 'createLogin'){
	$continueLink = 'install';
}

//HTML contents
 
printHeader();
if(isIWPAlreadyInstalled()){
	printAlreadyInstalled();
	exit;
}

printSideBarHTML();

if(empty($_GET['step'])){
	printLicenceHTML();
} else if($_GET['step'] == 'checkRequirement'){
	printCheckRequirementHTML();
} else if($_GET['step'] == 'enterDetails'){
	includeWPConfigFile();
	printEnterDBDetailsHTML();
} else if($_GET['step'] == 'createLogin'){
	printEnterUserDetailsHTML();
	$IDDatabase='createLogin';
	if(defined("PLUGIN_INSTALLER")){
		$continueOnClick = 'createLoginCheck(1);';
	} else {
		$continueOnClick = 'createLoginCheck(0);';
	}
} else if($_GET['step'] == 'install'){
	printInstallHTML();
}
printFooterBar($continueOnClick, $continueLink, $IDDatabase, $continueClass, $continueDivClass);