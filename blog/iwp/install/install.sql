<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

// DB Version 0.1.17

$maxIndexLength = 191;

$NO_AUTO_VALUE_ON_ZERO_query = 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"';

$iwp_addons_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."addons` (
  `slug` varchar(64) NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `addon` varchar(255) DEFAULT NULL,
  `validityExpires` int(10) unsigned DEFAULT NULL,
  `initialVersion` varchar(40) DEFAULT NULL,
  `updateCurrentVersion` varchar(40) DEFAULT NULL,
  UNIQUE KEY `slug` (`slug`)
) ".$tableEnv.";";

$iwp_allowed_login_ips_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."allowed_login_ips` (
  `IP` varchar(45) NOT NULL
) ".$tableEnv.";";

$iwp_favourites_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."favourites` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('plugins','themes') NOT NULL,
  `name` varchar(250) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `URL` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE `name_type_unique` (`type`, `name`(".$maxIndexLength."))
) ".$tableEnv.";";

$iwp_groups_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."groups` (
  `groupID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`groupID`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ".$tableEnv.";";

$iwp_groups_sites_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."groups_sites` (
  `groupID` int(10) unsigned DEFAULT NULL,
  `siteID` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `index1` (`groupID`,`siteID`)
) ".$tableEnv.";";

$iwp_hide_list_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."hide_list` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('plugins','themes','core','translations') DEFAULT NULL,
  `siteID` int(10) unsigned DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `URL` text,
  PRIMARY KEY (`ID`)
) ".$tableEnv.";";

$iwp_history_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."history` (
  `historyID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `siteID` int(10) unsigned NOT NULL,
  `actionID` varchar(45) NOT NULL,
  `parentHistoryID` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `status` enum('writingRequest','pending','initiated','running','completed','scheduled','netError','error','processingResponse','multiCallWaiting', 'retry') DEFAULT NULL,
  `error` varchar(256) DEFAULT NULL,
  `userID` int(10) unsigned NOT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `timeout` int(10) unsigned NOT NULL,
  `microtimeAdded` double(14,4) NOT NULL,
  `microtimeInitiated` double(14,4) DEFAULT NULL,
  `microtimeStarted` double(14,4) DEFAULT NULL,
  `microtimeEnded` double(14,4) DEFAULT NULL,
  `timeScheduled` int(10) unsigned DEFAULT NULL,
  `events` smallint(5) unsigned NOT NULL DEFAULT '1',
  `param1` text,
  `param2` text,
  `param3` text,
  `showUser` enum('Y','N') DEFAULT 'Y',
  `retried` smallint(6) DEFAULT '0',
  `runCondition` text,
  `callOpt` text,
  `isPluginResponse` ENUM('1', '0') NOT NULL DEFAULT  '1',
  `recheck` smallint(6) DEFAULT '0',
  `userIDCleared` int(10) unsigned NULL DEFAULT NULL,
  `notifyFailed` ENUM('pending','checked','sent') NULL DEFAULT NULL,
  PRIMARY KEY (`historyID`),
  KEY `actionID` (`actionID`),
  KEY `parentHistoryID` (`parentHistoryID`),
  KEY `microtimeInitiated` (`microtimeInitiated`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `action` (`action`),
  INDEX (`notifyFailed`)
) ".$tableEnv.";";

$iwp_history_additional_data_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."history_additional_data` (
  `historyID` bigint(20) unsigned NOT NULL,
  `detailedAction` varchar(50) DEFAULT NULL,
  `uniqueName` varchar(255) NOT NULL,
  `resultID` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','success','error','netError') NOT NULL DEFAULT 'pending',
  `error` varchar(255) DEFAULT NULL,
  `errorMsg` text,
  UNIQUE KEY `historyID_uniqueName` (`historyID`,`uniqueName`(150)),
  KEY `historyID` (`historyID`)
) ".$tableEnv.";";

$iwp_history_raw_details_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."history_raw_details` (
  `historyID` bigint(20) unsigned NOT NULL,
  `request` longtext,
  `response` longtext,
  `callInfo` longtext,
  `panelRequest` longtext,
  PRIMARY KEY (`historyID`)
) ".$tableEnv.";";

$iwp_options_DLL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."options` (
  `optionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `optionName` varchar(255) NOT NULL,
  `optionValue` longtext,
  PRIMARY KEY (`optionID`),
  UNIQUE KEY `optionName` (`optionName`(".$maxIndexLength."))
) ".$tableEnv.";";

$iwp_settings_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."settings` (
  `general` text,
  `timeUpdated` int(10) unsigned DEFAULT NULL
) ".$tableEnv.";";

$iwp_sites_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."sites` (
  `siteID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `URL` varchar(250) DEFAULT NULL,
  `adminURL` varchar(250) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `IP` varchar(45) DEFAULT NULL,
  `WPVersion` varchar(40) DEFAULT NULL,
  `pluginVersion` varchar(40) DEFAULT NULL,
  `adminUsername` varchar(45) DEFAULT NULL,
  `isOpenSSLActive` enum('1','0') NOT NULL DEFAULT '1',
  `randomSignature` varchar(40) DEFAULT NULL,
  `privateKey` text NOT NULL,
  `serverGroup` varchar(50) DEFAULT NULL,
  `network` tinyint(3) unsigned DEFAULT NULL,
  `multisiteID` smallint(6) DEFAULT NULL,
  `parent` tinyint(3) unsigned DEFAULT NULL,
  `httpAuth` varchar(255) DEFAULT NULL,
  `callOpt` text,
  `connectURL` enum('default','adminURL','siteURL') NOT NULL DEFAULT 'default',
  `connectionStatus` tinyint(1) NOT NULL DEFAULT '1',
  `links` text,
  `notes` text,
  `siteTechinicalInfo` text,
  `infoLastUpdate` datetime DEFAULT NULL,
  `siteCookie` text NULL,
  `lastFaviconUpdate` int(10) unsigned NOT NULL DEFAULT 0, 
  `favicon` text NULL,
  `stagingBaseSiteID` int(10) unsigned DEFAULT NULL,
  `type` ENUM('normal','staging') NOT NULL DEFAULT 'normal',
  `stagingFtpDetails` text NULL,
  `ftpDetails` text NULL,
  PRIMARY KEY (`siteID`),
  UNIQUE KEY `siteURL_UNIQUE` (`URL`(".$maxIndexLength.")),
  KEY `type` (`type`)
) ".$tableEnv.";";

$iwp_site_stats_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."site_stats` (
  `siteID` int(10) unsigned NOT NULL,
  `lastUpdatedTime` int(10) unsigned DEFAULT NULL,
  `stats` longtext,
  PRIMARY KEY (`siteID`)
) ".$tableEnv.";";

$iwp_temp_storage_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."temp_storage` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `paramID` varchar(50) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `type` (`type`,`paramID`)
) ".$tableEnv.";";

$iwp_users_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."users` (
  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(45) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `password` varchar(40) DEFAULT NULL,
  `accessLevel` enum('admin','manager') DEFAULT NULL,
  `permissions` text,
  `status` enum('1','0') NOT NULL DEFAULT '1',
  `updatesNotificationMailLastSent` int(10) unsigned DEFAULT NULL,
  `recentlyLoggedIn` int(10) unsigned DEFAULT NULL,
  `notifications` text,
  `help` text,
  `authInfo` text,
  `resetPassword` text,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ".$tableEnv.";";

$iwp_user_access_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."user_access` (
  `userID` int(10) unsigned DEFAULT NULL,
  `siteID` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `index1` (`userID`,`siteID`)
) ".$tableEnv.";";

$iwp_favourite_groups_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."favourite_groups` (
  `groupID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`groupID`), 
  UNIQUE KEY `name_type_unique` (`name`(141),`type`(50))
)".$tableEnv.";";

$iwp_favourites_groups_map_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."favourites_groups_map` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupID` int(10) unsigned NOT NULL,
  `favouriteID` int(10) unsigned NOT NULL,
  PRIMARY KEY(`ID`),
  UNIQUE KEY `groupID_favouriteID_unique` (`groupID`,`favouriteID`)
)".$tableEnv.";";

$iwp_login_logs_DDL = "CREATE TABLE IF NOT EXISTS `".$tableNamePrefix."login_logs` (
`ID` int(10) unsigned NOT NULL AUTO_INCREMENT, 
`userID` int(10) unsigned NOT NULL, 
`email` varchar(45) DEFAULT NULL, 
`accessLevel` enum('admin', 'manager', 'others') DEFAULT 'others', 
`time` int(10) unsigned NOT NULL, 
`browserInfo` varchar(45) DEFAULT NULL,
`loginAuthType`  enum('authNone', 'authBasic', 'authDuo') DEFAULT NULL,
`IP` varchar(45) DEFAULT NULL,
`error` varchar(255) DEFAULT NULL,
`loginAttemptStatus` enum('success', 'error') DEFAULT NULL,
`protocol`enum('HTTPS', 'HTTP') DEFAULT NULL,
`loginRedirect` enum('authNone', 'authBasic', 'authDuo') DEFAULT NULL, 
`browser` varchar(255) DEFAULT NULL,PRIMARY KEY (`ID`), INDEX `time` (`time`))".$tableEnv.";";

$settingsSerialized = 'a:7:{s:31:"MAX_SIMULTANEOUS_REQUEST_PER_IP";s:1:"2";s:24:"MAX_SIMULTANEOUS_REQUEST";s:1:"3";s:33:"TIME_DELAY_BETWEEN_REQUEST_PER_IP";s:3:"200";s:13:"sendAnonymous";s:1:"0";s:24:"enableReloadDataPageLoad";s:1:"1";s:26:"autoSelectConnectionMethod";s:1:"1";s:32:"CONSIDER_3PART_IP_ON_SAME_SERVER";s:1:"1";}';

$iwp_settings_default = "INSERT INTO `".$tableNamePrefix."settings` ( `general`, `timeUpdated`) VALUES
('".$settingsSerialized."', 0);";

$iwp_options_defaults = "INSERT INTO `".$tableNamePrefix."options` (`optionID`, `optionName`, `optionValue`) VALUES
(1, 'installedTime', NULL),
(2, 'anonymousDataNextSchedule', NULL),
(3, 'serviceURL', 'https://service.infinitewp.com/'),
(4, 'anonymousDataLastSent', NULL),
(5, 'updateLastCheck', NULL),
(6, 'updateAvailable', NULL),
(7, 'updatesNotificationMailLastSent', NULL),
(8, 'cronLastRun', NULL),
(9, 'updateHideNotify', NULL),
(10, 'updateNotifySentToJS', NULL),
(11, 'updateNotificationDynamicContent', NULL),
(12, 'offlineNotifications', NULL),
(13, 'appRegisteredUser', NULL),
(14, 'updateAddonsAvailable', NULL),
(15, 'newAddonsAvailable', NULL),
(16, 'promoAddons', NULL),
(17, 'IWPSiteURL', 'https://infinitewp.com/'),
(18, 'supportURL', 'https://support.infinitewp.com/'),
(19, 'isDirectFS', 'Y'),
(20, 'lastCronNotRunAlert', NULL);";

$schemaQueries = array();
$schemaQueries[] = $NO_AUTO_VALUE_ON_ZERO_query;
$schemaQueries[] = $iwp_addons_DDL;
$schemaQueries[] = $iwp_allowed_login_ips_DDL;
$schemaQueries[] = $iwp_favourites_DDL;
$schemaQueries[] = $iwp_groups_DDL;
$schemaQueries[] = $iwp_groups_sites_DDL;
$schemaQueries[] = $iwp_hide_list_DDL;
$schemaQueries[] = $iwp_history_DDL;
$schemaQueries[] = $iwp_history_additional_data_DDL;
$schemaQueries[] = $iwp_history_raw_details_DDL;
$schemaQueries[] = $iwp_options_DLL;
$schemaQueries[] = $iwp_settings_DDL;
$schemaQueries[] = $iwp_sites_DDL;
$schemaQueries[] = $iwp_site_stats_DDL;
$schemaQueries[] = $iwp_temp_storage_DDL;
$schemaQueries[] = $iwp_users_DDL;
$schemaQueries[] = $iwp_user_access_DDL;
$schemaQueries[] = $iwp_favourite_groups_DDL;
$schemaQueries[] = $iwp_favourites_groups_map_DDL;
$schemaQueries[] = $iwp_login_logs_DDL;
$schemaQueries[] = $iwp_settings_default;
$schemaQueries[] = $iwp_options_defaults;
