<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

function checkAndConvertingMyisamToInnodo($tableNames){
	if (!isSupportInnodb()){
		// appUpdateMsg('Your DataBase does not support InnoDB...');
		exit ;
	}
	if (!empty($tableNames)) {
		foreach ($tableNames as $key => $tableName) {
			//appUpdateMsg('Checking MyISAM for '.$tableName.'...');
			if (isTableMyisamEngine($tableName)) {
				//appUpdateMsg('Converting to InnoDB '.$tableName.'...');
				$startTime = time();
				$isdone = convertMyisamToInnodb($tableName);
				$endTime = time();
				getInnoDBConversionLogs($tableName, $startTime, $endTime, $isdone, 'Manual SQL Error: '.DB::errorNo().' ' .DB::error() );
				if (!$isdone) {
					updateOption('InnoDBConversionFailTable', $tableName);
					//appUpdateMsg('Not able to Converted InnoDB '.$tableName.'...');
					return false;
				}
			}else{
				//appUpdateMsg('This table not MyISAM...');
				continue;
			}
		}
		return true;
	}
}

function isTableMyisamEngine($tableName){
	if (!empty($tableName)) {
		$sql = "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '".Reg::get('config.SQL_DATABASE')."' AND TABLE_NAME ='?:".$tableName."'"; 
		$result = DB::getField($sql);
		if ($result == 'MyISAM') {
			return true;
		}
		return false;
	}

}
function convertMyisamToInnodb($tableName){
	DB::setPrintErrorOnFailure(false);
	$sql = "ALTER TABLE ?:".$tableName." ENGINE = InnoDB";
	$isDone = DB::doQuery($sql);
	if ($isDone) {
		return true;
	}
	return false;
}

function isSupportInnodb(){
	
	DB::setPrintErrorOnFailure(false);
	$DBVersion = DB::version();
	if (version_compare($DBVersion, '5.1.1', '>')) {
		$sql = "SELECT SUPPORT FROM INFORMATION_SCHEMA.ENGINES WHERE ENGINE = 'InnoDB'";
		$support = DB::getField($sql);
		if ($support != false) {
			$support = strtolower($support);
			if ($support == 'no') {
				return false;
			}
			return true;
		}
		return false;
	}else{
		$sql = "SHOW ENGINES";
		$support = DB::getArray($sql);
		if ($support != false) {
			foreach ($support as $key => $value) {
				if (($value['Engine'] == 'InnoDB') && ($value['Support'] == 'YES' || $value['Support'] == 'Default')) {
					return true;
				}
			}
		}
	}
	return false;
}

function generateTableNames($data){
	$tableNames = unserialize(getOption('conversionNeededTables'));
	$tableCount = count($tableNames);
	if ($tableCount == 1) {
		unsetArrayConvertInnoDBToMyisam();
		$tableNames = getConversionNeededTable($tableNames, $data);
	} else{
		$tableNames = getConversionNeededTable($tableNames, $data);
	}
	return $tableNames;
}

function splitTableNames($tableNames, $data){
	$convertedTableCount = getOption('convertedTableCount');
	$convertedTableCount = $convertedTableCount?$convertedTableCount:0;
	$tableCount = count($tableNames);
	$lastTrips = 0;
	$convertableTableNames = array();
	if ($convertedTableCount) {
		$divided = floor($tableCount / $data['noOfTrips']);
		$remainder = $tableCount % $data['noOfTrips'];
		if (($convertedTableCount + $divided + $remainder) >= $tableCount && $remainder) {
			$divided = $tableCount - $convertedTableCount;
			unsetArrayConvertInnoDBToMyisam();
			$lastTrips = 1;
            updateOption('convertedTableCount', 0);
		}
		for ($i = 0; $i != $divided; $i++) { 
			array_push($convertableTableNames, $tableNames[$convertedTableCount+$i]);
		}
	} else{
		$divided = floor($tableCount/$data['noOfTrips']);
		for ($i = 0; $i < $divided; $i++) { 
			array_push($convertableTableNames, $tableNames[$i]);
		}
	}
	if (!$lastTrips) {
		updateOption('convertedTableCount', $convertedTableCount + $divided);
	}
	return $convertableTableNames;
}
function unsetArrayConvertInnoDBToMyisam(){ 
	$convertInnoDBOptions = unserialize(getOption('convertInnoDBToMyisam'));
	foreach ($convertInnoDBOptions as $key => $value) {
		unset($convertInnoDBOptions[$key]);
		break;
	}
	updateOption('convertInnoDBToMyisam', serialize($convertInnoDBOptions));
}
function getMyisamTableNames($slots){
	 $tableNames = array();
	switch ($slots) {
		case 1:
			return array("0" => array("0"=>"addons", "1"=>"settings", "2" => "users" ),"1" => array("0"=>"hide_list"));
			break;
		case 2:
		    return array("0" => array("0"=>"user_privilege_template","1"=>"install_clone_repository", "2"=>"code_snippets", "3"=>"backup_repository", "4" => "backup_schedules"),"1" => array("0"=>"google_analytics_profiles","1"=>"google_analytics_profiles_sites","2"=>"wp_optimize_schedules_link"));
		    break;
		default:
			return false;

	}

}
function isInnoDBConversionNeeded(){
	$getInnoDBOptions = getConvertInnoDBToMyisamOption();
	$conversionNeededTables = array();
	$return = false;
	if (!empty($getInnoDBOptions) && isSupportInnodb()) {
		$conversionNeededTables = getMyisamTableNames($getInnoDBOptions);
		if (!empty($conversionNeededTables)) {
			foreach ($conversionNeededTables as $tripKey => $tableNames) {
				foreach ($tableNames as $key => $tableName) {
					if (!isTableMyisamEngine($tableName)) {
						unset($conversionNeededTables[$tripKey][$key]);
						if (empty($conversionNeededTables[$tripKey])) {
							unset($conversionNeededTables[$tripKey]);
						}
					} else {
						$return = true;
					}
				}
			}
		}
		updateOption('conversionNeededTables', serialize($conversionNeededTables));
		return $return;
	}
	return false;
}

function getConvertInnoDBToMyisamOption(){
	$options = unserialize(getOption('convertInnoDBToMyisam'));
	if (!empty($options)) {
		foreach ($options as $key) {
			return $key;
		}
	}
	return false;
}
function processInnoDBConversion(){
	$getInnoDBOptions = getConvertInnoDBToMyisamOption();
	if (isSupportInnodb()) {
		if (!empty($getInnoDBOptions)) {
			isInnoDBConversionNeeded();
			$tableNames = generateTableNames($getInnoDBOptions);
			if (!empty($tableNames)) {
				$result = checkAndConvertingMyisamToInnodo($tableNames);
				if (!$result) {
					updateOption('convertInnoDBToMyisam', serialize(array('0'=>1, '1' =>2)));
					return $result;
				} else{
					//unsetArrayConvertInnoDBToMyisam();
				}
			}
		}
	} else{
		return false;
	}
	if (getConvertInnoDBToMyisamOption()) {
		return 'redirect';
	} else{
		return true;
	}
}

function getConversionNeededTable(){
	$options = unserialize(getOption('conversionNeededTables'));
	if (!empty($options)) {
		foreach ($options as $key => $value) {
			$temp = $value;
			unset($options[$key]);
			updateOption('conversionNeededTables', serialize($options));
			return $temp;
		}
	}
	return false;
}

function getInnoDBConversionLogs($tableName, $startTime, $endTime, $status, $lastError){
	$logOption = unserialize(getOption('InnoDBConversionLogs'));
	$logArray = array("tableName" => $tableName, "startTime" => $startTime, "endTime" => $endTime, "status" => $status, "lastError" => $lastError);
	if (is_array($logOption)) {
		array_push($logOption, $logArray);
	} else{
		$logOption[] = $logArray;
	}
	updateOption('InnoDBConversionLogs', serialize($logOption));
}

function showConversionNeededTableNames(){
	if (!isSupportInnodb()) {
		return false;
	}
	$InnoDBOptions = unserialize(getOption('convertInnoDBToMyisam'));
	$neededTables = array();
	$return = false;
	$flag = 0;
	if (empty($InnoDBOptions)) {
		return false;
	}
	foreach ($InnoDBOptions as $optionKey => $option) {
		$flag = 0;
		$conversionNeededTables = getMyisamTableNames($option);
		if (!empty($conversionNeededTables)) {
			foreach ($conversionNeededTables as $tripKey => $tableNames) {
				foreach ($tableNames as $key => $tableName) {
					if (isTableMyisamEngine($tableName)) {
						array_push($neededTables, $tableName);
						$return = true;
						$flag = 1;
					}
				}
			}
			if (!$flag) {
				unset($InnoDBOptions[$optionKey]);
			}
		}
	}
	if ($return) {
		updateOption('convertInnoDBToMyisam', serialize($InnoDBOptions));
		updateOption('showConversionNeededTableNames', serialize($neededTables));
	}

	return $return;
}	

function showInnoDBConversionError(){
	$tableName = getOption('InnoDBConversionFailTable');
	$tableLog = unserialize(getOption('InnoDBConversionLogs'));
	
	foreach ($tableLog as $key => $value) {
		if ($value['tableName'] == $tableName) {
			return $value['lastError'];
		}
	}
}