<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
function DBConnectAndSetOptions(){
	$isConnected = DB::connect(Reg::get('config.SQL_HOST'), Reg::get('config.SQL_USERNAME'), Reg::get('config.SQL_PASSWORD'), Reg::get('config.SQL_DATABASE'), Reg::get('config.SQL_PORT'));
	if(!$isConnected){
		die('Mysql connect error: (' . DB::connectErrorNo().') '.DB::connectError() );
	}
	DB::setTableNamePrefix(Reg::get('config.SQL_TABLE_NAME_PREFIX'));
	DB::doQuery("SET SESSION sql_mode = ' '");
	checkDBStatus();
}

function processCallReturn($Array){
	return $Array;
}

function isPluginResponse($data){ // Checking if we got the rite data / it didn't time out
 
	if(stripos($data,'IWPHEADER') === false){
		return false;
	}
	return true;
}
function signData($data, $isOpenSSLActive, $privateKey, $randomSignature){
	if( function_exists('openssl_verify') && $isOpenSSLActive ){
		openssl_sign($data, $signature, base64_decode($privateKey));
	}
	elseif(!$isOpenSSLActive){
		$signature =  md5($data . $randomSignature);
	}
	else{
		return false;	
	}
	return $signature;
}

function secureData($data, $isOpenSSLActive, $privateKey, $randomSignature){
	if(!empty($data) && $isOpenSSLActive && !empty($privateKey)){
	   $secureData = serialize($data);		 
	   $secureDataArray = str_split($secureData, 96); //max length 117
		   
	   $secureDataEncrypt = array();
	   
		foreach($secureDataArray as $secureDataPart){				 			  
			openssl_private_encrypt($secureDataPart, $secureDataEncryptPart, base64_decode($privateKey));
			$secureDataEncrypt[] = $secureDataEncryptPart;
		}
		$data = $secureDataEncrypt;
	}	
	return $data;	
}

function addHistory($historyData, $historyAdditionalData=''){
	$historyData['userID'] = $GLOBALS['userID'];
	$historyData['microtimeAdded']=microtime(true);
	$historyID =  DB::insert('?:history', $historyData);
	if(!empty($historyAdditionalData)){
		foreach($historyAdditionalData as $row){			
			$row['historyID'] = $historyID;
			DB::insert("?:history_additional_data", $row);
			
		}
	}
	
	return $historyID;
}

function updateHistory($historyData, $historyID, $historyAdditionalData=false){
	$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
	DB::update('?:history', $historyData, $where);
	
	if(!empty($historyAdditionalData)){
		DB::update('?:history_additional_data', $historyAdditionalData, $where);
	}
}

function getHistory($historyID, $getAdditional=false){
	$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
	$historyData = DB::getRow('?:history', '*', $where);
	if($getAdditional){
		$historyData['additionalData'] = DB::getArray('?:history_additional_data', '*', $where);
	}
	return $historyData;
}

function getSiteData($siteID, $byPassAccess = NULL, $byPassStaging = NULL){
	if($siteID){
		if(userStatus() == "admin" || $byPassAccess === "true" ){
			$where = array(
	      		'query' =>  "siteID=':siteID'",
	      		'params' => array(
	               ':siteID'=>$siteID
					)
			);
			return DB::getRow("?:sites","*",$where);
		}
		else{
			if (function_exists('stagingGetStagingSiteData') && $byPassStaging != true) {
				$siteTempData = stagingGetStagingSiteData($siteID);
				if (!empty($siteTempData) && is_array($siteTempData)) {
					foreach ($siteTempData as $key => $data) {
						$siteData = $data;
					}
					return $siteData;
				}
			}
			$where = array(
	      		'query' =>  "UA.userID =':userID' AND  UA.siteID IN (:siteID) AND S.siteID=UA.siteID",
	      		'params' => array(
	               ':userID'=>$GLOBALS['userID'],
	               ':siteID'=>$siteID
					)
			);
			return DB::getRow("?:user_access UA, ?:sites S", "S.*, UA.siteID", $where);
		}
	}
}

function getSitesData($siteIDs=false, $where='1'){ //for current user
	if(userStatus() == "admin"){
		if(!empty($siteIDs)){
			$where = "siteID IN (". implode(', ', DB::esc($siteIDs)) .")";
		} else {
			$where = "type = 'normal'";
		}
	
		return DB::getArray("?:sites", "*", $where, "siteID");
	}
	else{
		if(!empty($siteIDs)){
			if (function_exists('stagingGetStagingSiteData')) {
				$siteData = stagingGetStagingSiteData($siteIDs);
				if (!empty($siteData) && is_array($siteData)) {
					return $siteData;
				}
			}
			$where = " AND UA.siteID IN (". implode(',', DB::esc($siteIDs)) .") ";
			return DB::getArray("?:user_access UA, ?:sites S", "S.*, UA.siteID", "UA.userID =".DB::esc($GLOBALS['userID'])." AND UA.siteID IN (". implode(',', DB::esc($siteIDs)) .") AND S.siteID=UA.siteID ", "siteID");
		}
		return DB::getArray("?:user_access UA, ?:sites S", "S.*, UA.siteID", "UA.userID ='".DB::esc($GLOBALS['userID'])."' AND S.siteID=UA.siteID AND type = 'normal'", "siteID");
	}
}

function prepareRequestAndAddHistory($PRP){	
	$defaultPRP = array('doNotExecute' 		=> false,
						'exitOnComplete' 	=> false,
						'doNotShowUser' 	=> false,//hide in history
						'directExecute' 	=> false,
						'signature' 		=> false,
						'timeout' 			=> DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT,
						'runCondition'		=> false,
						'status'			=> 'pending',
						'isPluginResponse'	=> 1,
						'sendAfterAllLoad'	=> false,
						'callOpt'			=> array()
						);
						
	$PRP = array_merge($defaultPRP, $PRP);
	@extract($PRP);
	
	if(empty($historyAdditionalData)){
		echo 'noHistoryAdditionalData';
		return false;	
	}
	
	if( ($siteData['connectURL'] == 'default' && defined('CONNECT_USING_SITE_URL') && CONNECT_USING_SITE_URL == 1) || $siteData['connectURL'] == 'siteURL'){
		$URL = $siteData['URL'];
	}
	else{//if($siteData['connectURL'] == 'default' || $siteData['connectURL'] == 'adminURL')
		$URL = $siteData['adminURL'];
	}
	
	$historyData = array('siteID' 	=> $siteData['siteID'], 
						 'actionID' => Reg::get('currentRequest.actionID'),
						 'userID' 	=> $GLOBALS['userID'],
						 'type' 	=> $type,
						 'action' 	=> $action,
						 'events' 	=> $events,
						 'URL' 		=> trim($URL),
						 'timeout' 	=> $timeout,
						 'isPluginResponse' => $isPluginResponse);	
	if($doNotShowUser){
		$historyData['showUser'] = 'N';
	}
	
	if($parentHistoryID){
		$historyData['parentHistoryID'] = $parentHistoryID;
	}

	if(!empty($actionID)){
		$historyData['actionID'] = $actionID;
	}
	
	if(!empty($siteData['callOpt'])){
		$callOpt = @unserialize($siteData['callOpt']);
	}
	
	if(!empty($siteData['httpAuth'])){
		$callOpt['httpAuth'] = @unserialize($siteData['httpAuth']);
	}
	if(!empty($runCondition)){
		$historyData['runCondition'] = $runCondition;
	}
	if(!empty($timeScheduled)){
		$historyData['timeScheduled'] = $timeScheduled;
	}
	if(!empty($notifyFailed)){
		$historyData['notifyFailed'] = $notifyFailed;
	}
	
	$historyData['callOpt'] = serialize($callOpt);
	$historyID = addHistory($historyData, $historyAdditionalData);
		
	if($signature === false){
		$signature = signData($requestAction.$historyID, $siteData['isOpenSSLActive'], $siteData['privateKey'], $siteData['randomSignature']);
	}
	
	$requestParams['username'] =  $siteData['adminUsername'];
	
	if(isset($requestParams['secure'])){
		$requestParams['secure'] = secureData($requestParams['secure'], $siteData['isOpenSSLActive'], $siteData['privateKey'], $siteData['randomSignature']);
	}
	
	if(!empty($requestParams['args'])){
		$requestParams['args']['parentHID'] = $historyID;
	}

	$requestData = array('iwp_action' => $requestAction, 'params' => $requestParams, 'id' => $historyID, 'signature' => $signature, 'iwp_admin_version' => APP_VERSION, 'is_save_activity_log' => isSaveActivityLogInClient(), 'activities_log_datetime' => date('Y-m-d H:i:s', time()));
		
	$updateHistoryData = array('status' => $status);
	
	updateHistory($updateHistoryData, $historyID);
	
	DB::insert("?:history_raw_details", array('historyID' => $historyID, 'request' => base64_encode(serialize($requestData)), 'panelRequest' => serialize($_REQUEST) ) );
	
	if($directExecute){
		set_time_limit(0);
		echo 'direct_execute<br />';
		executeRequest($historyID, $type, $action, $siteData['URL'], $requestData, $timeout, true, $callOpt);
	}
	else{
		echo 'async_call_it_should_be<br />';
		if($exitOnComplete){ set_time_limit(0); echo "async_call_it_should_be_working"; Reg::set('currentRequest.exitOnComplete', true); }
		elseif($sendAfterAllLoad){ Reg::set('currentRequest.sendAfterAllLoad', true); }
	}
	return $historyID;
}



function executeRequest($historyID, $type='', $action='', $URL='', $requestData='', $timeout='', $isPluginResponse=true, $callOpt=array()){	
	$responseProcessor = array();
	$responseProcessor['plugins']['install'] = $responseProcessor['themes']['install'] = 'installPluginsThemes';
	$responseProcessor['plugins']['manage'] = $responseProcessor['themes']['manage'] = 'managePluginsThemes';	
	$responseProcessor['plugins']['get'] = $responseProcessor['themes']['get'] = 'getPluginsThemes';
	$responseProcessor['stats']['getStats'] = 'getStats';
	$responseProcessor['PTC']['update'] = 'updateAll';
	$responseProcessor['backup']['now'] = 'backup';
	$responseProcessor['backup']['multiCallNow'] = 'backup';
	$responseProcessor['backup']['restore'] = 'restoreBackup';
	$responseProcessor['backup']['remove'] = 'removeBackup';
	$responseProcessor['site']['add'] = 'addSite';
	$responseProcessor['site']['readd'] = 'readdSite';
	$responseProcessor['site']['maintain'] = 'iwpMaintenance';
	$responseProcessor['site']['auto_updater_settings'] = 'editSite';
	$responseProcessor['site']['remove'] = 'removeSite';
	$responseProcessor['site']['backupTest'] = 'backupTest';
	$responseProcessor['clientPlugin']['update'] = 'updateClient';
	$responseProcessor['backup']['trigger'] = 'triggerRecheck';
	$responseProcessor['cookie']['get'] = 'getCookie';

	setHook('responseProcessors', $responseProcessor);
	
	$historyData = getHistory($historyID);
	$actionResponse = $responseProcessor[$historyData['type']][$historyData['action']];
	$where = array(
			      		'query' =>  "historyID=':historyID'",
			      		'params' => array(
			               ':historyID'=>$historyID
           				)
        			);

	if(manageClients::methodPreExists($actionResponse)){
		manageClients::executePre($actionResponse, $historyID);
		$historyDataTemp = getHistory($historyID);
		if($historyDataTemp['status'] != 'pending'){
			return false;	
		}
		unset($historyDataTemp);
	}	
	
	if(empty($type) || empty($action) || empty($URL) || empty($requestData)){
		
		$historyRawDetails = DB::getRow("?:history_raw_details", "*", $where);
		$requestData = unserialize(base64_decode($historyRawDetails['request']));
		$historyData = getHistory($historyID);
		$type = $historyData['type'];
		$action = $historyData['action'];
		$URL = $historyData['URL'];
		$timeout =  $historyData['timeout'];
		$isPluginResponse =  $historyData['isPluginResponse'];
		$callOpt = @unserialize($historyData['callOpt']);
	}
	
	$siteID = $historyData['siteID'];
	$callOpt = buildCookie($callOpt, $siteID, $historyData);
	addNoCacheToURL($URL, $historyData);

	updateHistory(array('microtimeInitiated' => microtime(true), 'status' => 'running'), $historyID);

	$updateHistoryData = array();
	list($rawResponseData, $updateHistoryData['microtimeStarted'], $updateHistoryData['microtimeEnded'], $curlInfo, $rawResponseHeader)  = doCall($URL, $requestData, $timeout, $callOpt);

	DB::update("?:history_raw_details", array('response' => addslashes($rawResponseData), 'callInfo' => serialize($curlInfo)), $where);
	
	$cookie = extractCookie($rawResponseHeader);
	saveCookie($cookie, $siteID);


	$cURLErrors = new cURLErrors($curlInfo);

	if(!$cURLErrors->isOk()){
		$updateHistoryAdditionalData = array();
		$updateHistoryAdditionalData = $cURLErrors->getErrorDetails();
		$updateHistoryData['status'] = $updateHistoryAdditionalData['status'];
		$updateHistoryData['error'] = $updateHistoryAdditionalData['error'];

		if(!isPluginResponse($rawResponseData)){//sometimes 500 error with proper IWP Client Response, so if it not proper response continue set error and exit
			if (!retryFailedTasksChecker($historyID, $actionResponse, $curlInfo['errorNo'], $curlInfo['info']['http_code'])) {
				updateHistory($updateHistoryData, $historyID, $updateHistoryAdditionalData);
			}
			insertHTTPCodeErrorsOfICIntoTempStorage($historyData['type'] ,$historyData['action'] ,$historyID, $cURLErrors->getErrorMsg());
			return checkTriggerStatus();
		}
	}

	if($isPluginResponse){//$isPluginResponse is set to true then expecting result should be pluginResponse
		//checking response is the plugin data
		if(!isPluginResponse($rawResponseData)){ //Checking the timeout	
			//For left menu color codes
			if($type=='stats' && $action == 'getStats'){
				$where = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
				DB::update("?:sites", array('connectionStatus' => '0'), $where);
			}
			$updateHistoryAdditionalData = array();
			$updateHistoryAdditionalData['status'] = $updateHistoryData['status'] = 'error';	
			$updateHistoryAdditionalData['error'] = $updateHistoryData['error'] = 'main_plugin_connection_error';
			$updateHistoryAdditionalData['errorMsg'] = 'IWP Client plugin connection error.';
			updateHistory($updateHistoryData, $historyID, $updateHistoryAdditionalData);
			return checkTriggerStatus();	
		 }
		removeResponseJunk($rawResponseData);
		$responseData = processCallReturn( unserialize(base64_decode($rawResponseData)) );
	} else{
		$responseData = $rawResponseData;
	}
	
	$updateHistoryData['status'] = 'processingResponse';
	updateHistory($updateHistoryData, $historyID);
	
	//handling reponseData below
	
	if(manageClients::methodResponseExists($actionResponse)){
		manageClients::executeResponse($actionResponse, $historyID, $responseData);
		//call_user_func('manageClients::'.$funcName, $historyID, $responseData);
		$status = "completed";
		$historyReponseStatus = Reg::get("historyResponseStatus");
		if(isset($historyReponseStatus[$historyID])){
			$status = $historyReponseStatus[$historyID];
		}
		updateHistory(array('status' => $status), $historyID);
		
		return true;
	}
	else{
		updateHistory(array('status' => 'completed'), $historyID);
		echo '<br>no_response_processor';
		return 'no_response_processor';
	}
}

function isTasksRunningByActionID($actionID){
	$where = array(
			'query'		=> "actionID = ':actionID' AND status NOT IN ('completed', 'netError', 'error')",
			'params' 	=> array(
					':actionID'	=> $actionID
				)
			);
	return DB::getExists("?:history", "historyID", $where);
}

function getCompletedTaskIDsByActionID($actionID){
	$where = array(
			'query' => "actionID = ':actionID' AND status IN ('completed', 'netError', 'error')",
			'params' => array(
				':actionID'	=> $actionID,
				)
			);
	$completedTask = DB::getArray("?:history", "historyID, siteID", $where);
	return $completedTask;
}

function getHistoryAdditionalDataByHistoryID($historyID){
	$where = array(
			'query'  => "historyID = :historyID" ,
			'params' => array(
				':historyID' => $historyID,
				)
			);
	$data = DB::getArray("?:history_additional_data", "historyID, status, error, errorMsg, uniqueName", $where);
	return $data;
}

function stripHeaders(&$rawResponseData, $curlInfo){  
    if(isset($curlInfo["info"]["header_size"])) { 
        $header_size = $curlInfo["info"]["header_size"];  
        $rawResponseData = substr($rawResponseData, $header_size);        
    } 
}

function retryFailedTasksChecker($historyID, $actionResponse, $errorNo = NULL, $http_code = NULL){
	$needRetryErrorNo = array('28');
	$needRetryhttp_code = array('500', '501', '503', '504');
	$needRetryActionResponse = array('getStats', 'getPluginsThemes', 'installPluginsThemes', 'updateAll'); // actions of IWP
	$where = array(
		      		'query' =>  "historyID=':historyID'",
			      	'params' => array(
		               ':historyID'=>$historyID
					)
				);

	$currentRetryStatus = DB::getFields("?:history", "retried", $where);

    if ($currentRetryStatus[0] == 0 && (in_array($actionResponse, $needRetryActionResponse)) && (in_array($errorNo, $needRetryErrorNo) || in_array($http_code, $needRetryhttp_code) ) ) {
		$retriedTask = array('status'=>'retry');
		DB::update('?:history', $retriedTask, $where);
		return true;
	} else {
		return false;
	}
}

function insertHTTPCodeErrorsOfICIntoTempStorage($type, $action, $historyID, $errorData){
	if ($type != 'installClone' && $action != 'testConnection') {
		return '';
	}
	$actionID = DB::getField("?:history","actionID", "historyID = ".$historyID);
	DB::insert("?:temp_storage", array('type' => 'getICTestConnection', 'paramID' => $actionID, 'time' => time(), 'data' =>  serialize(array('error' => $errorData))));

}

function isNonCookieSites($siteID, $historyData){
    $type = $historyData['type'];
    $action = $historyData['action'];
    $isPluginResponse = $historyData['isPluginResponse'];
    
    $emptySiteID = empty($siteID);
    $isNonPluginResponse = ($isPluginResponse != 1);
    $addNewSite = ($action === 'newSite' && $type === 'installClone');
    return $emptySiteID || $isNonPluginResponse || $addNewSite;
}

function buildCookie($callOpt, $siteID, $historyData){
    if(isNonCookieSites($siteID, $historyData)){
        return $callOpt;
    }
    $useCookie = !defined('USECOOKIE_OPT') || (defined('USECOOKIE_OPT') && USECOOKIE_OPT === TRUE);
    if($useCookie){
        if(!empty($callOpt)){
            $callOpt['useCookie'] = 1;
            $type = $historyData['type'];
            if($type === 'cookie'){
                return $callOpt;
            }
            $where = array(
			      		'query' =>  "siteID = ':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
            $cookie = DB::getField("?:sites", "siteCookie", $where);
            if(hasCookieExpired($cookie)){
                $swap = Reg::get('currentRequest.actionID');
                Reg::set('currentRequest.actionID', uniqid('', true)); 
                manageClientsFetch::getCookieProcessor($siteID);// direct execute
                Reg::set('currentRequest.actionID', $swap); 
                $cookie = DB::getField("?:sites", "siteCookie", $where );
            }
            $callOpt['cookie'] = $cookie;
        }else{
            $callOpt = array('useCookie'=>1);
        }
        return $callOpt;
    }
}

function hasCookieExpired($siteCookie){
    if(!trim($siteCookie)){
        return true;
    }

    $cookieDelimiter = ';';
    $cookies = explode($cookieDelimiter, $siteCookie);
    $encodedPipe = '%7C';
    foreach ($cookies as $cookie) {
        $isWPCookie = strpos(trim($cookie), 'wordpress') === 0;
        $tokens = explode($encodedPipe, $cookie);
        $timeToken = $tokens[1];
        if ($isWPCookie && is_numeric($timeToken)) {
            $now = time();
            $isExpired = $now > $timeToken;
            return $isExpired;
        }
    }
    return false;
}

function extractCookie($rawResponseData){
    preg_match_all('/^Set-Cookie: (.*?;)/sim', $rawResponseData, $cookieTemp);
    return implode(" ", $cookieTemp[1]);
}
	
function saveCookie($cookie, $siteID){
    if(!empty($cookie)){
    	 $where = array(
			      		'query' =>  "siteID = ':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
        DB::update("?:sites", "siteCookie='".$cookie."'", $where);
    }
}

function addNoCacheToURL(&$URL, $historyData){

	if( defined('DISABLE_NO_CACHE_TO_URL') && DISABLE_NO_CACHE_TO_URL ){
		return false;
	}

	$noCacheCalls = array();
	$noCacheCalls['plugins']['install'] = $noCacheCalls['themes']['install'] = 1;
	$noCacheCalls['plugins']['manage'] = $noCacheCalls['themes']['manage'] = 1;	
	$noCacheCalls['plugins']['get'] = $noCacheCalls['themes']['get'] = 1;
	$noCacheCalls['stats']['getStats'] = 1;
	$noCacheCalls['PTC']['update'] = 1;
	$noCacheCalls['clientPlugin']['update'] = 1;

	$type = $historyData['type'];
	$action = $historyData['action'];

	if( $noCacheCalls[$type][$action] ){

		$randStr = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), rand(3, 16), 8);//generating random 8 char str

		$URLParts = parse_url($URL);

		//adding new get variable to URL
		parse_str($URLParts['query'], $getVars);
		$getVars['no_cache_'.$randStr] = time();
		$URLParts['query'] = $getVars;

		$URL = httpBuildURLCustom($URLParts);
	}
}

function onAsyncFailUpdate($historyID, $info){
	
	if($info['status'] == true){ return false; }
	
	$errorMsg = 'Fsock error: ';
	$error = 'fsock_error';
	
	if(!empty($info['error'])){
		$errorMsg .= $info['error'];
		$error =  'fsock_error_info';
	}
	
	if($info['writable'] === false){
		$errorMsg .= 'Unable to write request';
		$error = 'fsock_error_unable_to_write_request';
	}	
	
	$updateHistoryAdditionalData = array();
	$updateHistoryAdditionalData['status'] = $updateHistoryData['status'] = 'error';	
	$updateHistoryAdditionalData['error'] = $updateHistoryData['error'] = $error;
	$updateHistoryAdditionalData['errorMsg'] = $errorMsg;
	updateHistory($updateHistoryData, $historyID, $updateHistoryAdditionalData);
	return true;
	
}

function removeResponseJunk(&$response){
	$headerPos = stripos($response, '<IWPHEADER');
	if($headerPos !== false){
		$response = substr($response, $headerPos);
		$response = substr($response, strlen('<IWPHEADER>'), stripos($response, '<ENDIWPHEADER')-strlen('<IWPHEADER>'));
	}
}

function getServiceResponseToArray($response){
	removeResponseJunk($response);
	$response = @base64_decode($response);
	$response = @unserialize($response);
	return $response;
}

function responseDirectErrorHandler($historyID, $responseData){
	
	if(!empty($responseData['error']) && is_string($responseData['error'])){
		$where = array(
			      		'query' =>  "historyID=':historyID'",
			      		'params' => array(
			               ':historyID'=>$historyID
           				)
        			);
		DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['error'], 'error' => $responseData['error_code']), $where);
		return true;
	}
	return false;
	
}

function exitOnComplete(){
	$maxTimeoutSeconds = 60;//1 min
	if(!empty($GLOBALS['offline'])){
		$maxTimeoutSeconds = 60 * 15;//15 min
	}
	$where = array(
			      		'query' =>  "actionID = ':actionID' AND status NOT IN('completed', 'error', 'netError')",
			      		'params' => array(
			               ':actionID'=>Reg::get('currentRequest.actionID')
           				)
        			);
	$maxTimeoutTime = time() + $maxTimeoutSeconds;
	while(DB::getExists("?:history", "historyID", $where) && $maxTimeoutTime > time()){
		usleep(100000);//1000000 = 1 sec
	}
}

function sendAfterAllLoad(&$requiredData){//changing exit on complete technique

	$_requiredData = array();
	if(!empty($requiredData)){
		$exceptionRequireData = array('getHistoryPanelHTML');
		foreach($requiredData as $key => $value){
			if(!in_array($key, $exceptionRequireData)){
				$_requiredData[$key] = $requiredData[$key];
				unset($requiredData[$key]);
			}
		}
	}
	
        $waitActions= manageCookies::cookieGet('waitActions');
        if(empty($waitActions)) {
            $waitActions = array();
        } 
        
	$actionID = Reg::get('currentRequest.actionID');
	$currentTime = time();
	$waitActions[$actionID] = array('timeInitiated' => $currentTime,
									'timeExpiresFromSession' => $currentTime + (20 * 60),
									'requiredData' => $_requiredData
									);
        manageCookies::cookieSet('waitActions',$waitActions,array('expire'=>0));
}

function clearUncompletedTask(){
	$addtionalTime = 30;//seconds
	$time = time();
	
	//$updateData = array( 'H.status' => 'netError',
	//					 'H.error' => 'timeoutClear',
	//					 'HAD.status' => 'netError',
	//					 'HAD.error' => 'timeoutClear',
	//					 'HAD.errorMsg' => 'Timeout'
	//					);
	
	$updateData  = "H.status = IF(H.status IN('initiated', 'running'), 'netError', IF(H.status = 'processingResponse', 'error', 'error'))";
	$updateData .= ", HAD.status = IF(H.status IN('initiated', 'running'), 'netError', IF(H.status = 'processingResponse', 'error', 'error'))";
	$updateData .= ", H.error = IF(H.status IN('initiated', 'running'), 'timeoutClear', IF(H.status = 'processingResponse', 'processingResponseDied', 'unknownError'))";
	$updateData .= ", HAD.error = IF(H.status IN('initiated', 'running'), 'timeoutClear', IF(H.status = 'processingResponse', 'processingResponseDied', 'unknownError'))";
	$updateData .= ", HAD.errorMsg = IF(H.status IN('initiated', 'running'), 'Timeout', IF(H.status = 'processingResponse', 'Error in processing data', 'unknownError'))";
	$where = "H.historyID = HAD.historyID";
	$where .= " AND ( H.status IN('initiated', 'running') OR (H.status = 'processingResponse' AND (H.microtimeEnded + 150) < ".DB::esc($time).") )";//150 seconds
	$where .= " AND (H.microtimeInitiated + H.timeout + ".DB::esc($addtionalTime).") < ".DB::esc($time)."";
	$where .= " AND H.microtimeInitiated > 0";//this will prevent un-initiated task from clearing
	DB::update("?:history H, ?:history_additional_data HAD", $updateData, $where);
}

//Generate the passcodeMail and send it
function sendPasscodeMail($passcode, $userID) {
	$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$userID
           				)
        			);
    $user = DB::getRow("?:users", "email, name", $where);
    $passlinkInfo = array(
        'passcodeLink'  =>  APP_URL."login.php?passlink=".  base64_encode($passcode),
        'passcode'      =>  $passcode,
        'userEmail'     =>  $user['email'],
        'requestIp'     =>  $_SERVER['REMOTE_ADDR'],
        'appUrl'        =>  APP_URL,
        'expired'       =>  date("g:i a \o\\n F j", time()+(60*60)),    //Set the expired as 1 hrs
        'userID'        =>  $userID
    );
    return sendAppMail($passlinkInfo, $contentTPL = '/templates/email/passcodeLink.tpl.php');
}

//Verifying by Passcode
function verifyPasscode($params,$type="code") {
    
    $authInfo = unserialize(manageCookies::cookieGet('authCookieInfo'));
    $where1 = array(
			      		'query' =>  "userID = ':userID'" ,
			      		'params' => array(
			               ':userID'=>$authInfo['userId']
           				)
        			);
    $email = DB::getField("?:users", "email" ,$where1);
    if(time()<=$authInfo['validity']) {
    	$where = array(
			      		'query' =>  "userID = ':userID'" ,
			      		'params' => array(
			               ':userID'=>$authInfo['userId']
           				)
        			);
        $dbAuthInfo = DB::getField("?:users", "authInfo", $where);
        $dbAuthInfo = unserialize(base64_decode($dbAuthInfo));
        $passcode = ($type=="code")?$params['passcode']:base64_decode($params);
        if(!empty($passcode) && !empty($dbAuthInfo['passcode']) && (string)$dbAuthInfo['passcode'] === (string)$passcode) {
            manageCookies::cookieUnset('authCookieInfo');
            DB::update("?:users", array("authInfo" => ""), $where);
            loginByUserId($authInfo['userId']);
            addLoginLog($email, 'authBasic', 'success', '');
            header('Location: '.APP_URL); //'Location: ' => index.php
            exit;
        } else {
            $passVerifyCount =  manageCookies::cookieGet('passVerifyCount');
            if(empty($passVerifyCount) || $passVerifyCount <2) {
                $passVerifyCount = ($passVerifyCount=="")?1:$passVerifyCount+1;
                manageCookies::cookieSet('passVerifyCount', $passVerifyCount,array('expire'=>0));
                $errorMsg = 'passcodeInvalid';
                addLoginLog($email, 'authBasic', 'error', getLoginPageMsg($errorMsg));
                header('Location: login.php?view=getPasscode&errorMsg='.$errorMsg);
                exit;
            } else {
                manageCookies::cookieUnset('passVerifyCount');
                $lockOutTime = time()+(60*15);
                DB::update("?:users", array("authInfo" => $lockOutTime), $where);
                $lockOut = base64_encode($lockOutTime);
                $errorMsg = 'accountLock';
                $errorMsgFormat = getLoginPageMsg($errorMsg);
                addLoginLog($email, 'authBasic', 'error', sprintf($errorMsgFormat, convertToMinSec($lockOutTime-time())));
                header('Location: login.php?errorMsg='.$errorMsg.'&lockOut='.$lockOut);
                exit;
            }
            
        }
    } else {
        $errorMsg = 'passcodeValidity';
        addLoginLog($email, 'authBasic', 'error', getLoginPageMsg($errorMsg));
        header('Location: login.php?errorMsg='.$errorMsg);
        exit;
    }
    exit;
}

//login by userID
function loginByUserId($userID) {
	$where = array(
			      		'query' =>  "userID = ':userID' ORDER BY userID ASC LIMIT 1",
			      		'params' => array(
			               ':userID'=>$userID
           				)
        			);
    $userInfo = DB::getRow("?:users", "userID, accessLevel, email,password", $where);
    loginByUser($userInfo);
}

//login by userID
function loginByUserName($userName) {
	$where = array(
			      		'query' =>  "email = ':email' ORDER BY userID ASC LIMIT 1" ,
			      		'params' => array(
			               ':email'=>$userName
           				)
        			);
    $userInfo = DB::getRow("?:users", "userID, accessLevel, email,password", $where);
    loginByUser($userInfo);
}

//Login by given user info
function loginByUser($userInfo) {
    $userEmail = strtolower(trim($userInfo["email"]));
    $userPass = $userInfo["password"];
    $userCookie = md5($userEmail.$userPass);
    $userCookie = $userEmail . '||' . $userCookie;
    manageCookies::cookieSet('userCookie', $userCookie,array('expire'=>0));
    $where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$userID
           				)
        			);
    DB::update("?:users", array("recentlyLoggedIn" => time()), $where);
}

//Login .
function userLogin($params){
	
	if(empty($params)){ return false; }
	/*if($isUserExists){*/	
		if(function_exists('multiUserStatus')){
			if(multiUserStatus($params)){
				$isUserExists = true;
			}
			else{
				$isUserExists = false;
			}
		}
		else{
			$where = array(
			      		'query' =>  "email = ':email' AND password = ':password' ORDER BY userID ASC LIMIT 1" ,
			      		'params' => array(
			               ':email'=>trim($params["email"]),
			               ':password'=>sha1($params["password"])
           				)
        			);
			$userName = DB::getRow("?:users", "userID, accessLevel, email", $where);
			$isUserExists = !empty($userName["userID"]) ? true : false;
			
			$userID = $userName["userID"];
			if($isUserExists) $GLOBALS['userID'] = $userID;
			if($userName['accessLevel'] != 'admin' && $isUserExists){
				$errorMsg = 'onlyAdmin';
				addLoginLog($params['email'], 'authNone', 'error', getLoginPageMsg($errorMsg));
				header('Location: login.php?errorMsg='.$errorMsg);
				exit;
			}
		}
	/*}*/
	
        // If user enter wrong passcode 3times, We set authInfo+1hrs. So user not able to login the panel by next 1 hrs.
        //We use the $authData to identify the authInfo have serialize data or not. If its not serialize data there means unserialize will return the false(boolion) value
        //echo $GLOBALS['userID'];die;
		$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$GLOBALS['userID']
           				)
        			);
		$dbAuthInfo = DB::getField("?:users", "authInfo", $where );
        $authData = @unserialize($dbAuthInfo);  
        if($authData === false && $dbAuthInfo!="" && (time()<$dbAuthInfo)) {
            $lockOut = base64_encode($dbAuthInfo);
            $errorMsg = 'accountLock';
            $errorMsgFormat = getLoginPageMsg($errorMsg);
            addLoginLog($params['email'], 'authBasic', 'error', sprintf($errorMsgFormat, convertToMinSec(base64_decode($lockOut)-time())));
            header('Location: login.php?errorMsg='.$errorMsg.'&lockOut='.$lockOut);
            die;
        }elseif($authData === false && $dbAuthInfo!="" && (time()>=$dbAuthInfo)){
            DB::update("?:users", array("authInfo" => ""), $where);
        }
        
		$allowedLoginIPs = DB::getFields("?:allowed_login_ips", "IP", "1", "IP");
		$allowedLoginIPsClear = 1;
		if($isUserExists && !empty($allowedLoginIPs)){
			$allowedLoginIPsClear = 0;
			foreach($allowedLoginIPs as $IP){
				if($returnFlag = IPInRange($_SERVER['REMOTE_ADDR'], trim($IP))){
					$allowedLoginIPsClear = 1;
					break;
				}
			}
		}
	
	if($isUserExists && $allowedLoginIPsClear == 1){
            //After all login check done, we look the authendication method.
            if(isExistOption('loginAuthType')) { $loginAuthType = getOption('loginAuthType'); } else { $loginAuthType = 'authNone'; }
            if (defined('DISABLE_2FA') && DISABLE_2FA) {
           		$loginAuthType = 'authNone';	
            }
            if($loginAuthType=="authBasic") {
                $passcode = mt_rand(100000, 999999);
                $mailPasscode= base64_encode($passcode);
                $validity = time()+(60*60);
                $authInfo = base64_encode(serialize(array('userId'=>$GLOBALS['userID'],'passcode'=>$passcode,'validity'=>$validity)));
                $authCookieInfo = serialize(array('userId'=>$GLOBALS['userID'],'validity'=>$validity));
                manageCookies::cookieSet('authCookieInfo', $authCookieInfo,array('expire'=>0));
                DB::update("?:users", array("authInfo" => $authInfo), $where);
                if(sendPasscodeMail($passcode,$GLOBALS['userID'])) {
                    addLoginLog($params['email'], 'authNone', 'success', '', 'authBasic');
                    header('Location: login.php?view=getPasscode&successMsg=passcodeMailSent');
                    exit;
                } else {
                    $errorMsg = 'passcodeMailError';
                    addLoginLog($params['email'], $loginAuthType, 'error', getLoginPageMsg($errorMsg));
                    header('Location: login.php?errorMsg='.$errorMsg);
                    exit;
                }
            }elseif(function_exists ('getDuoFrame') && $loginAuthType=="authDuo") {
                if(!($GLOBALS['duoFrameStr'] = getDuoFrame($params["email"]))) {
                    $errorMsg = 'duoConnectionError';
                    addLoginLog($params['email'], $loginAuthType, 'error', getLoginPageMsg($errorMsg));
                    header('Location: login.php?errorMsg='.$errorMsg);
                    exit;
                } else {
                    $_GET['view'] = "duoFrame";
                }
            }else {
                loginByUserId($GLOBALS['userID']);
                addLoginLog($params['email'], 'authNone', 'success');
                header('Location: '.APP_URL); //'Location: ' => index.php
                exit;
            }
	}
	else{
            manageCookies::cookieUnset('userCookie');
            $errorMsg = 'invalid';
            if($allowedLoginIPsClear == 0){
                    $errorMsg = 'access';
            }
            addLoginLog($params['email'], 'authNone', 'error', getLoginPageMsg($errorMsg));
            header('Location: login.php?errorMsg='.$errorMsg);
            exit;
	}
}

function userLogout($userRequest=false){
	manageCookies::cookieAllUnset();
	if($userRequest){
		$successMsg = 'logout';
		if(!defined('IS_AJAX_FILE')){
			header('Location: login.php?successMsg='.$successMsg);
			exit;
		}
	}else{
		if(!defined('IS_AJAX_FILE')){
				header('Location: login.php');
				exit;
		}
	}
}

function resetPasswordLinkValidate($params){
	
	$isValidLink = false;
	$isExpired = false;

	$userDetails = DB::getRow("?:users", "userID, resetPassword", "sha1(email) = '".DB::realEscapeString($params['transID'])."'");

	if(!empty($userDetails) && is_array($userDetails)){
		$userID = $userDetails['userID'];
		$resetDetails = $userDetails['resetPassword'];
		if(!empty($resetDetails)){
			$resetDetails = unserialize($resetDetails);

			if(!empty($resetDetails) && is_array($resetDetails) && !empty($resetDetails['resetHash']) && !empty($resetDetails['validity'])){
				if($resetDetails['resetHash'] === $params['resetHash']){
					$isValidLink = true;
					if($resetDetails['validity'] > 0  && $resetDetails['validity'] < time()){
						$isExpired = true;
					}
				}
			}
		}

	}
	return array('isValidLink' => $isValidLink, 'isExpired' => $isExpired);
}

function userLoginResetPassword($params){

	
	if( (isset($params['view']) && $params['view'] == 'resetPasswordChange') ||  (isset($params['action']) && $params['action'] == 'resetPasswordChange') ){//validate IWP reset password link, before showing the form or saving new password

		$result = resetPasswordLinkValidate(array('resetHash' => $params['resetHash'], 'transID' => $params['transID']));
		
		if($result['isValidLink'] == true && $result['isExpired'] == true){
			header("Location: login.php?errorMsg=resetPasswordLinkExpired");
			exit;
		}
		elseif($result['isValidLink'] == false){
			$errorMsg = 'resetPasswordLinkInvalid';
			header("Location: login.php?errorMsg=resetPasswordLinkInvalid");
			exit;
		}
		//if above if conditions fails, then it all good, allow to load reset password form

	}
	

	if(isset($params['action']) && $params['action'] == 'resetPasswordChange'){

		//above code should validate the resetHash and transID

		if(!isset($params['newPassword']) || empty($params['newPassword'])){
			header('Location: login.php?view=resetPasswordChange&resetHash='.$params['resetHash'].'&transID='.$params['transID'].'&errorMsg=resetPasswordInvalidPassword');
			exit;
		}
		$where = array(
			      		'query' =>  "sha1(email) = ':email' ",
			      		'params' => array(
			               ':email'=>$params['transID']
           				)
        			);
		$isUpdated = DB::update("?:users", array( "password" => sha1($params['newPassword']), "resetPassword" => serialize(array()) ), $where);
		if($isUpdated){
			header('Location: login.php?successMsg=resetPasswordChanged');
			exit;
		}else{
			header('Location: login.php?view=resetPasswordChange&resetHash='.$params['resetHash'].'&transID='.$params['transID'].'&errorMsg=resetPasswordFailed');
			exit;
		}


	}
	elseif(isset($params['action']) && $params['action'] == 'resetPasswordSendMail'){
		$params["email"] = DB::realEscapeString($params['email']);
		$where = array(
			      		'query' =>  "email = ':email'",
			      		'params' => array(
			               ':email'=>$params["email"]
           				)
        			);
		$userDets = DB::getRow("?:users", "userID", $where );
		$isUserExists = !empty($userDets["userID"]) ? true : false;	
		if(!$isUserExists){
			header('Location: login.php?view=resetPassword&errorMsg=resetPasswordEmailNotFound');
			exit;
		}
		
		$hashValue = serialize(array('hashCode' => 'resetPassword', 'uniqueTime' => microtime(true), 'userPin' => $userDets['userID']));
		$resetHash = sha1($hashValue);
		
		$where = array(
			      		'query' =>  "userID = ':userID' ",
			      		'params' => array(
			               ':userID'=>$userDets['userID']
           				)
        			);
		DB::update("?:users", array("resetPassword" => serialize(array("resetHash" => $resetHash, "validity" => time()+86400))), $where);
		
		$verificationURL = APP_URL."login.php?view=resetPasswordChange&resetHash=".$resetHash."&transID=".sha1($params["email"]);
		
		$isSent = sendAppMail(array('userID' => $userDets["userID"], 'verificationURL' => $verificationURL), '/templates/email/resetPassword.tpl.php');
		
		if(!empty($isSent)){
			header('Location: login.php?successMsg=resetPasswordMailSent');
			exit;
		}
		else{
			header('Location: login.php?view=resetPassword&errorMsg=resetPasswordMailError');
			exit;
		}

	}
}

function checkUserLoggedIn(){
	
	$return = false;
        $userCookie = manageCookies::cookieGet('userCookie');
        if($userCookie!='') {
            list($userEmail,$userSlat) = explode('||', $userCookie);
            $userEmail = filterParameters($userEmail);
            if($userEmail!='' && $userSlat!='') {
            		$where = array(
			      		'query' =>  "email = ':email'" ,
			      		'params' => array(
			               ':email'=>trim($userEmail)
           				)
        			);
                    $userInfo = DB::getRow("?:users", "userID,email,password", $where );
                    
                    $GLOBALS['userID'] = $userInfo['userID'];
                    $GLOBALS['email'] = strtolower($userInfo['email']);
                    $stringtoSalt = $GLOBALS['email'].$userInfo['password'];
                    if(!empty($stringtoSalt)){
                        $dbSlat = md5($GLOBALS['email'].$userInfo['password']);
                        if($userSlat==$dbSlat) {
                            $return = true;
                        }
                    }
            }
        }
   

	if($return == false){
		userLogout();
	}
	return $return;
}

function checkUserLoggedInAndRedirect(){
	//check session, if not logged in redirect to login page
	if(!defined('USER_SESSION_NOT_REQUIRED') && !defined('IS_EXECUTE_FILE')){
		if(!checkUserLoggedIn()){
			echo jsonEncoder(array('logout' => true));
			exit;
		}
	}
}

function checkRawHistoryAndDelete(){
	$currentTime = mktime( 0, 0, 0, date("n"), date("j"),date("Y"));
	$nextSchedule = getOption('deleteRawHistoryNextSchedule');
	if($currentTime > $nextSchedule){
		$nextSchedule = $currentTime + 86400;
		$thirtyDaysAgo = $currentTime - (30 * 86400);
		$sql = "DELETE HRD FROM `?:history_raw_details` AS HRD INNER JOIN `?:history` AS H ON HRD.historyID = H.historyID WHERE  H.microtimeAdded < '".$thirtyDaysAgo."'";
		$isDeleted = DB::doQuery($sql);
		if($isDeleted)
			updateOption('deleteRawHistoryNextSchedule', $nextSchedule);
			DB::doQuery("OPTIMIZE TABLE `?:history_raw_details`");		
	}
}

function anonymousDataRunCheck(){

	if(!Reg::get('settings.sendAnonymous')){ return false; }
	
	$currentTime = time();	
	$nextSchedule = getOption('anonymousDataNextSchedule');
	
	if($currentTime > $nextSchedule){		
		//execute
		$fromTime = getOption('anonymousDataLastSent');
		$isSent = anonymousDataSend($fromTime, $currentTime);
		if(!$isSent){
			return false;	
		}
		//if sent execute below code
		updateOption('anonymousDataLastSent', $currentTime);
		
		$installedTime = getOption('installedTime');
		
		$weekDay = @date('w', $installedTime);
		$currWeekDay = @date('w', $currentTime);
	    $nextNoOfDays = 7 - $currWeekDay + $weekDay;		
		$nextSchedule = mktime(@date('H', $installedTime), @date('i', $installedTime), @date('s', $installedTime), @date('m', $currentTime), @date('d', $currentTime)+$nextNoOfDays, @date('Y', $currentTime));
		updateOption('anonymousDataNextSchedule', $nextSchedule);
		
	}
}

function anonymousDataSend($fromTime, $toTime){

	$fromTime = (int)$fromTime;
	
	$anonymousData = array();
	$addons = DB::getFields("?:addons", "slug", "1", 'slug');	
	if(!empty($addons)){
		foreach($addons as $slug => $v){
			$addons[$slug] = getAddonVersion($slug);
		}
	}	
	$anonymousData['addonsBought'] = $addons;
	$anonymousData['sites'] 			= DB::getField("?:sites", "count(siteID)", "type = 'normal'");
	$anonymousData['stagingSites'] 			= DB::getField("?:sites", "count(siteID)", "type = 'staging'");
	$anonymousData['groups']			= DB::getField("?:groups", "count(groupID)", "1");
	$anonymousData['groupMaxSites']		= DB::getField("?:groups_sites", "count(siteID) as maxSiteCount", "1 GROUP BY groupID ORDER BY maxSiteCount DESC LIMIT 1", "maxSiteCount");
	$anonymousData['hiddenCount']		= DB::getField("?:hide_list", "count(ID)", "1");
	$anonymousData['favourites']		= DB::getField("?:favourites", "count(ID)", "1");
	$anonymousData['settings']			= DB::getArray("?:settings", "*", "1");
	$anonymousData['users']				= DB::getField("?:users", "count(userID)", "1");
	$anonymousData['allowedLoginIps']	= DB::getField("?:allowed_login_ips", "count(IP)", "1");
		
	$anonymousData['siteNameMaxLength']	= DB::getField("?:sites", "length(name) as siteNameLength", "1 ORDER BY siteNameLength DESC LIMIT 1");	
	$anonymousData['groupNameMaxLength']= DB::getField("?:groups", "length(name) as groupNameLength", "1 ORDER BY groupNameLength DESC LIMIT 1");
	
	//$anonymousData['appVersion'] 				= APP_VERSION;
	$anonymousData['appInstalledTime'] 			= getOption('installedTime');
	$anonymousData['lastHistoryActivityTime'] 	= DB::getField("?:history", "microtimeAdded", "1 ORDER BY historyID DESC");	
	$anonymousData['updatesNotificationMailLastSent'] = getOption('updatesNotificationMailLastSent');
	
	$anonymousData['DBInfo'] = getAnonymousDBInfo();
	//to find hostType
	$anonymousData['hostType'] = 'unknown';
	
	if(!empty($_SERVER['SERVER_ADDR'])){
		$SERVER_ADDR = $_SERVER['SERVER_ADDR'];
	}
	else{
		$SERVER_ADDR = gethostbyname($_SERVER['HTTP_HOST']);
	}
	
	if(!empty($SERVER_ADDR)){
		if(IPInRange($SERVER_ADDR, '127.0.0.0-127.255.255.255')){
			$anonymousData['hostType'] = 'local';
		}
		elseif(IPInRange($SERVER_ADDR, '10.0.0.0-10.255.255.255') || IPInRange($SERVER_ADDR, '172.16.0.0-172.31.255.255') || IPInRange($SERVER_ADDR, '192.168.0.0-192.168.255.255')){
			$anonymousData['hostType'] = 'private';
		}
		else{
			$anonymousData['hostType'] = 'public';
		}
	}
	
	//history stats
	$where = array(
			      		'query' =>  "microtimeAdded > ':fromTime' AND microtimeAdded <= ':toTime' GROUP BY status",
			      		'params' => array(
			               ':fromTime'=>$fromTime,
			               ':toTime'=>$toTime

           				)
        			);
	$anonymousData['historyStatusStats']= DB::getFields("?:history", "count(status) as statusCount, status", $where, "status");
	$where = array(
			      		'query' =>  "microtimeAdded > ':fromTime' AND microtimeAdded <= ':toTime'",
			      		'params' => array(
			               ':fromTime'=>$fromTime,
			               ':toTime'=>$toTime

           				)
        			);
	$tempHistoryData					= DB::getRow("?:history", "count(historyID) as historyCount, count(DISTINCT actionID) as historyActions", $where);
	$anonymousData['historyCount']		= $tempHistoryData['historyCount'];
	$anonymousData['historyActions']	= $tempHistoryData['historyActions'];
	$where = array(
			      		'query' =>  "H.historyID = HAD.historyID AND H.microtimeAdded > ':fromTime' AND H.microtimeAdded <= ':toTime' GROUP BY HAD.status",
			      		'params' => array(
			               ':fromTime'=>$fromTime,
			               ':toTime'=>$toTime

           				)
        			);
	$anonymousData['historyAdditionalStatusStats']	= DB::getFields("?:history H, ?:history_additional_data HAD", "count(HAD.status) as statusCount, HAD.status",$where, "status");
	$where = array(
			      		'query' =>  "H.historyID = HAD.historyID AND H.microtimeAdded > ':fromTime' AND H.microtimeAdded <= ':toTime'",
			      		'params' => array(
			               ':fromTime'=>$fromTime,
			               ':toTime'=>$toTime

           				)
        			);
	$anonymousData['historyAdditionalCount']		= DB::getField("?:history H, ?:history_additional_data HAD", "count(HAD.historyID) as historyCount", $where);
	$where = array(
			      		'query' =>  "H.historyID = HAD.historyID AND H.microtimeAdded > ':fromTime' AND H.microtimeAdded <= ':toTime' GROUP BY H.type, H.action, HAD.detailedAction, HAD.status ORDER BY H.type, H.action, HAD.detailedAction, HAD.status",
			      		'params' => array(
			               ':fromTime'=>$fromTime,
			               ':toTime'=>$toTime

           				)
        			);	
	$historyEventStatusStatsArray	= DB::getArray("?:history H, ?:history_additional_data HAD", "H.type, H.action, HAD.detailedAction, HAD.status, COUNT(H.historyID) as events", $where);
	$where = array(
			      		'query' =>  "H.historyID = HAD.historyID AND H.microtimeAdded > ':fromTime' AND H.microtimeAdded <= ':toTime' AND HAD.status IN ('error', 'netError')",
			      		'params' => array(
			               ':fromTime'=>$fromTime,
			               ':toTime'=>$toTime

           				)
        			);
	$historyEventErrorCodeStatsArray	= DB::getArray("?:history H, ?:history_additional_data HAD", "H.type, H.action, HAD.detailedAction, HAD.status, HAD.error", $where);
		
	if(!empty($historyEventErrorCodeStatsArray)){
		foreach($historyEventErrorCodeStatsArray as $v){
			if(!empty($v['error'])){
				$errorCodes[$v['type']][$v['action']][$v['detailedAction']][$v['status']][] = $v['error'];
			}
		}
	}
		
	$historyEventStatusStats= array();
	if(!empty($historyEventStatusStatsArray)){
		foreach($historyEventStatusStatsArray as $v){
			
			if(!isset($historyEventStatusStats[$v['type']][$v['action']][$v['detailedAction']]['total'])){
				$historyEventStatusStats[$v['type']][$v['action']][$v['detailedAction']]['total'] = 0;
			}
			$historyEventStatusStats[$v['type']][$v['action']][$v['detailedAction']][$v['status']] = $v['events'];			
			$historyEventStatusStats[$v['type']][$v['action']][$v['detailedAction']]['total'] += $v['events'];
			
			if(is_array($errorCodes[$v['type']][$v['action']][$v['detailedAction']]))
			foreach($errorCodes[$v['type']][$v['action']][$v['detailedAction']] as $key => $value){
				if($key == $v['status']){
					$historyEventStatusStats[$v['type']][$v['action']][$v['detailedAction']]['errorStats'][$v['status']] = array_count_values($value);
				}				
			}
		}
	}	
	
	$anonymousData['historyEventStatusStats'] = $historyEventStatusStats;
		
	$tempSiteStats				= DB::getArray("?:sites", "WPVersion, pluginVersion, isOpenSSLActive, callOpt, network, parent", "1");
	
	//To get contentType for sites.
	foreach($tempSiteStats as $key => $siteStats){

		if(!isset($anonymousData['siteStats']['WPVersion'][$siteStats['WPVersion']]))	{
			$anonymousData['siteStats']['WPVersion'][$siteStats['WPVersion']] = 1;
		}else{
			$anonymousData['siteStats']['WPVersion'][$siteStats['WPVersion']] += 1;
		}

		if(!isset($anonymousData['siteStats']['pluginVersion'][$siteStats['pluginVersion']]))	{
			$anonymousData['siteStats']['pluginVersion'][$siteStats['pluginVersion']] = 1;
		}else{
			$anonymousData['siteStats']['pluginVersion'][$siteStats['pluginVersion']] += 1;
		}

		if(!isset($anonymousData['siteStats']['WPAndClientVersion'][$siteStats['WPVersion'].'-'.$siteStats['pluginVersion']]))	{
			$anonymousData['siteStats']['WPAndClientVersion'][$siteStats['WPVersion'].'-'.$siteStats['pluginVersion']] = 1;
		}else{
			$anonymousData['siteStats']['WPAndClientVersion'][$siteStats['WPVersion'].'-'.$siteStats['pluginVersion']] += 1;
		}

		if(!isset($anonymousData['siteStats']['isOpenSSLActive'][$siteStats['isOpenSSLActive']]))	{
			$anonymousData['siteStats']['isOpenSSLActive'][$siteStats['isOpenSSLActive']] = 1;
		}else{
			$anonymousData['siteStats']['isOpenSSLActive'][$siteStats['isOpenSSLActive']] += 1;
		}

		if(!isset($anonymousData['siteStats']['network'][$siteStats['network']]))	{
			$anonymousData['siteStats']['network'][$siteStats['network']] = 1;
		}else{
			$anonymousData['siteStats']['network'][$siteStats['network']] += 1;
		}

		if(!isset($anonymousData['siteStats']['parent'][$siteStats['parent']]))	{
			$anonymousData['siteStats']['parent'][$siteStats['parent']] = 1;
		}else{
			$anonymousData['siteStats']['parent'][$siteStats['parent']] += 1;
		}
		
		$siteStatsCallOpt = unserialize($siteStats['callOpt']);
		if(!isset($anonymousData['siteStats']['contentType'][$siteStatsCallOpt['contentType']]))	{
			$anonymousData['siteStats']['contentType'][$siteStatsCallOpt['contentType']] = 1;
		}else{
			$anonymousData['siteStats']['contentType'][$siteStatsCallOpt['contentType']] += 1;
		}
	}
	//To get installed plugins
	$sitePlugins = array();
	$tempSitePlugins	= panelRequestManager::getRecentPluginsStatus();
	foreach ($tempSitePlugins as $siteID => $sitePluginData) {

		foreach ($sitePluginData as $slug => $pluginData) {
			$sitePlugins[$slug] = array('isInstalled'=>0,'isActivated'=>0,'name'=>$pluginData['name'],'title'=>$pluginData['title'],'version'=>array(),'isnetworkActivated'=>0);
		}
		}

	foreach ($tempSitePlugins as $siteID => $sitePluginData) {
		foreach ($sitePluginData as $slug => $pluginData) {
			if($pluginData['isInstalled'])	$sitePlugins[$slug]['isInstalled'] += 1;
			if($pluginData['isActivated'])	$sitePlugins[$slug]['isActivated'] += 1;
			if($pluginData['network'])		$sitePlugins[$slug]['isnetworkActivated'] += 1;
			if(!isset($sitePlugins[$slug]['version'][$pluginData['version']]))	{
				$sitePlugins[$slug]['version'][$pluginData['version']] = 1;
			}else{
				$sitePlugins[$slug]['version'][$pluginData['version']] += 1;
			}
	}

	}
	unset($tempSitePlugins);
	$anonymousData['sitePlugins']	= $sitePlugins;
	//To get installed themes
	$siteThemes = array();
	$tempSiteThemes	= panelRequestManager::getRecentThemesStatus();
	foreach ($tempSiteThemes as $siteID => $siteThemeData) {
		if(isset($siteThemeData['inactive'])){
			foreach ($siteThemeData['inactive'] as $themeData) {
				$siteThemes[$themeData['path']] = array('name'=>$themeData['name'],'version'=>array(),'stylesheet'=>$themeData['stylesheet'],'isActive'=>0,'isInstalled'=>0);
			}
		}
		if(isset($siteThemeData['active'])){
			$siteThemes[$siteThemeData['active'][0]['path']] = array('name'=>$siteThemeData['active'][0]['name'],'version'=>array(),'stylesheet'=>$siteThemeData['active'][0]['stylesheet'],'isActive'=>0,'isInstalled'=>0);
		}

		}

	foreach ($tempSiteThemes as $siteID => $siteThemeData) {
		if(isset($siteThemeData['inactive'])){
			foreach ($siteThemeData['inactive'] as $themeData) {
				$siteThemes[$themeData['path']]['isInstalled'] += 1;
				if(!isset($siteThemes[$themeData['path']]['version'][$themeData['version']]))	{
					$siteThemes[$themeData['path']]['version'][$themeData['version']] = 1;
				}else{
					$siteThemes[$themeData['path']]['version'][$themeData['version']] += 1;
				}
			}
		}
		if(isset($siteThemeData['active'])){
			$siteThemes[$siteThemeData['active'][0]['path']]['isInstalled'] += 1;
			$siteThemes[$siteThemeData['active'][0]['path']]['isActive'] += 1;

			if(!isset($siteThemes[$siteThemeData['active'][0]['path']]['version'][$siteThemeData['active'][0]['version']]))	{
				$siteThemes[$siteThemeData['active'][0]['path']]['version'][$siteThemeData['active'][0]['version']] = 1;
			}else{
				$siteThemes[$siteThemeData['active'][0]['path']]['version'][$siteThemeData['active'][0]['version']] += 1;
		}
		}
	}
	unset($tempSiteThemes);
	$anonymousData['siteThemes']	= $siteThemes;
	
	$anonymousData['server']['PHP_VERSION'] 	= phpversion();
	$anonymousData['server']['PHP_CURL_VERSION']= curl_version();
	$anonymousData['server']['MYSQL_VERSION'] 	= DB::getField("select version() as V");
	$anonymousData['server']['OS'] =  php_uname('s');
	$anonymousData['server']['OSVersion'] =  php_uname('v');
	$anonymousData['server']['Machine'] =  php_uname('m');


	$anonymousData['server']['webServerSoftware'] = $_SERVER['SERVER_SOFTWARE'];

	//To get server interface. (ex: apache, apache2filter, apache2handler, caudium, cgi, ... more)	
	$anonymousData['server']['PHP_SAPI'] = php_sapi_name(); 
	
	
	$anonymousData['server']['PHPDisabledFunctions'] = explode(',', ini_get('disable_functions'));	
	array_walk($anonymousData['server']['PHPDisabledFunctions'], 'trimValue');
	
	$anonymousData['server']['PHPDisabledClasses'] = explode(',', ini_get('disable_classes'));	
	array_walk($anonymousData['server']['PHPDisabledClasses'], 'trimValue');
	
	$anonymousData['browser'] = $_SERVER['HTTP_USER_AGENT'];
	
	$requestData = array('anonymousData' => serialize($anonymousData), 'appInstallHash' => APP_INSTALL_HASH, 'appVersion' => APP_VERSION);
	list($result) = doCall(getOption('serviceURL').'anonymous.php', $requestData, $timeout=30);
	
	$result = getServiceResponseToArray($result);
	
	if($result['status'] == 'true'){
		return true;	
	}
	return false;
	
}

function getAnonymousDBInfo(){

	$tables = DB::getArray("SHOW TABLE STATUS");
 	foreach ($tables as $table) {
		if(strpos($table['Name'], Reg::get('config.SQL_TABLE_NAME_PREFIX')) === false ) { continue;} //get only iwp related tables
		$table['Name'] = str_replace(Reg::get('config.SQL_TABLE_NAME_PREFIX'),'',$table['Name']);  //removing perfix from table name
		$DBInfo['tablesInfo'][$table['Name']] =  $table;
 	}
	$status = DB::getExists("SHOW TABLE STATUS FROM information_schema");
	if ($status) {
		$DBInfo['DBAccess'] = array('information_schema' => array('access' => 'yes'));
	} else {
		$DBInfo['DBAccess'] = array('information_schema' => array('access' => 'no'));
	}
	return $DBInfo;
}
function getReportIssueData($actionID, $reportType='historyIssue'){
	
	$reportIssue = array();
	
	//collecting data about the action
	
	$addons = DB::getFields("?:addons", "slug", "1", 'slug');
	
	if(!empty($addons)){
		foreach($addons as $slug => $v){
			$addons[$slug] = getAddonVersion($slug);
		}
	}	
	
	$reportIssue['addonsBought'] = $addons;
	
	if(!empty($actionID) && $reportType='historyIssue'){
		$where = array(
			      		'query' =>  "H.actionID =':actionID'",
			      		'params' => array(
			               ':actionID'=>$actionID
           				)
        			);
		$reportIssue['history'] = DB::getArray("?:history H", "*", $where);		
		$siteIDs = DB::getFields("?:history H", "H.siteID, H.actionID", $where);					
		if(empty($reportIssue['history'])){
			return false;
		}
		
		foreach($reportIssue['history'] as $key => $data){
			
			if(($data['type'] == 'backup' && $data['action'] == 'multiCallNow') || ($data['type'] == 'scheduleBackup' && $data['action'] == 'multiCallRunTask')){
				$where = array(
			      		'query' =>  "parentHistoryID =':parentHistoryID' GROUP BY status",
			      		'params' => array(
			               ':parentHistoryID'=>$data['historyID']
           				)
        			);
				$reportIssue['backupTriggerHistoryStatusCount'][$data['historyID']] = DB::getFields("?:history", "count(status) as statusCount, status", $where, "status");
				$reportIssue['backupTriggerHistoryStatusCount'][$data['historyID']]['total'] = array_sum($reportIssue['backupTriggerHistoryStatusCount'][$data['historyID']]);
				$where = array(
			      		'query' =>  "parentHistoryID =':parentHistoryID' ORDER BY historyID DESC LIMIT 6",
			      		'params' => array(
			               ':parentHistoryID'=>$data['historyID']
           				)
        			);
				$reportIssue['backupTriggerHistory'][$data['historyID']]= DB::getArray("?:history", "*", $where);	//edited for report issue multi tasks			
								
				if(!empty($reportIssue['backupTriggerHistory'])){
					foreach($reportIssue['backupTriggerHistory'] as $keys => $value){
						$where = array(
				      		'query' =>  "historyID = ':historyID'",
				      		'params' => array(
				               ':historyID'=>$value[0]['historyID']
	           				)
        				);
						$reportIssue['backupTriggerHistoryAdditional'][$data['historyID']][] = DB::getArray("?:history_additional_data", "*", $where); //edited for report issue multi tasks	
						$reportIssue['backupTriggerHistoryRaw'][$data['historyID']][] = DB::getArray("?:history_raw_details", "*", $where); //edited for report issue multi tasks	
					}
				}
			}
		}
		$where = array(
			      		'query' =>  "H.actionID = ':actionID' AND HAD.historyID = H.historyID",
			      		'params' => array(
			               ':actionID'=>$actionID,
           				)
        			);		
		$reportIssue['historyAdditional'] = DB::getArray("?:history_additional_data HAD, ?:history H", "HAD.*", $where);
                $where = array(
			      		'query' =>  "H.actionID = ':actionID' AND HRD.historyID = H.historyID",
			      		'params' => array(
			               ':actionID'=>$actionID,
           				)
        			);	
		$reportIssue['historyRaw']        = DB::getArray("?:history_raw_details HRD, ?:history H", "HRD.*", $where);	
		$reportIssue['siteDetails'] 	  = DB::getArray("?:sites", "siteID, URL, WPVersion, pluginVersion, network, parent, IP, siteTechinicalInfo", "siteID IN (".implode(',',DB::esc($siteIDs)).")", "siteID");	
	}
	
	$reportIssue['settings'] = DB::getRow("?:settings", "general", "1");
	$reportIssue['settings']['general'] = unserialize($reportIssue['settings']['general']);
	
	$reportIssue['fsockSameURLConnectCheck'] = fsockSameURLConnectCheck(APP_FULL_URL.'execute.php');
	//$siteID = DB::getArray("?:");
		
	$reportIssue['server']['PHP_VERSION'] 	= phpversion();
	$reportIssue['server']['PHP_CURL_VERSION']= curl_version();
	$reportIssue['server']['PHP_WITH_OPEN_SSL'] = function_exists('openssl_verify');
	$reportIssue['server']['PHP_MAX_EXECUTION_TIME'] =  ini_get('max_execution_time');
	$reportIssue['server']['MYSQL_VERSION'] 	= DB::getField("select version() as V");
	$reportIssue['server']['OS'] =  php_uname('s');
	$reportIssue['server']['OSVersion'] =  php_uname('v');
	$reportIssue['server']['Machine'] =  php_uname('m');
	
	$reportIssue['server']['PHPDisabledFunctions'] = explode(',', ini_get('disable_functions'));	
	array_walk($reportIssue['server']['PHPDisabledFunctions'], 'trimValue');
	
	$reportIssue['server']['PHPDisabledClasses'] = explode(',', ini_get('disable_classes'));	
	array_walk($reportIssue['server']['PHPDisabledClasses'], 'trimValue');
	
	$reportIssue['browser'] = $_SERVER['HTTP_USER_AGENT'];
	$reportIssue['reportTime'] = time();


	removeSecureContentFromReportIssue($reportIssue['backupTriggerHistoryRaw'] , true);
	removeSecureContentFromReportIssue($reportIssue['historyRaw'] , false );
		

	
	//to find hostType
	$reportIssue['hostType'] = 'unknown';
	
	if(!empty($_SERVER['SERVER_ADDR'])){
		$SERVER_ADDR = $_SERVER['SERVER_ADDR'];
	}
	else{
		$SERVER_ADDR = gethostbyname($_SERVER['HTTP_HOST']);
	}
	
	$reportIssue['serverIP'] = $SERVER_ADDR;
	
	if(!empty($SERVER_ADDR)){
		if(IPInRange($SERVER_ADDR, '127.0.0.0-127.255.255.255')){
			$reportIssue['hostType'] = 'local';
		}
		elseif(IPInRange($SERVER_ADDR, '10.0.0.0-10.255.255.255') || IPInRange($SERVER_ADDR, '172.16.0.0-172.31.255.255') || IPInRange($SERVER_ADDR, '192.168.0.0-192.168.255.255')){
			$reportIssue['hostType'] = 'private';
		}
		else{
			$reportIssue['hostType'] = 'public';
		}
	}
	//to find hostType - Ends
	
	if(!empty($actionID) && $reportType='historyIssue'){
		$reportIssueTemp = array();
		$reportIssueTemp[$actionID] = $reportIssue;
        updateOption('reportIssueTemp',  serialize($reportIssueTemp));
	}	
	
	return array('actionID' =>$actionID, 'report' => $reportIssue);
}

function removeSecureContentFromReportIssue(&$historyRaws , $isBackupTriggerHistoryRaw){
	
	if ($isBackupTriggerHistoryRaw && !empty($historyRaws) && is_array($historyRaws)) {
		foreach ($historyRaws as $key1 => $historyRaw) {
			foreach ($historyRaw as $key2 => $siteRaws) {
				foreach ($siteRaws as $key3 => $siteRaw) {
					$decodedHistoryRawRequest = unserialize(base64_decode($siteRaw['request']));
					unset($decodedHistoryRawRequest['signature'],
					$decodedHistoryRawRequest['params']['secure']);
					$historyRaws[$key1][$key2][$key3]['request'] = base64_encode(serialize($decodedHistoryRawRequest));
				}
			}
		}
	}else {
		if(!empty($historyRaws) && is_array($historyRaws)){
			foreach($historyRaws as $key => $value){
				$decodedHistoryRawRequest = unserialize(base64_decode($value['request']));
				unset($decodedHistoryRawRequest['signature'],
				$decodedHistoryRawRequest['params']['signature'],
				$decodedHistoryRawRequest['params']['secure']);
				if ($decodedHistoryRawRequest['ftpHost']) {
					unset($decodedHistoryRawRequest['ftpHost'],
					$decodedHistoryRawRequest['ftpPort'],
					$decodedHistoryRawRequest['ftpUser'],
					$decodedHistoryRawRequest['ftpPass'],
					$decodedHistoryRawRequest['ftpBase'],
					$decodedHistoryRawRequest['dbHost'],
					$decodedHistoryRawRequest['dbUser'],
					$decodedHistoryRawRequest['dbPassword'],
					$decodedHistoryRawRequest['dbName']);
				}
				$historyRaws[$key]['request'] = base64_encode(serialize($decodedHistoryRawRequest));
			}
		}
	}

}	


function sendReportIssue($params){
	if(!empty($params)){
		
		
		if($params['type'] == 'historyIssue' && !empty($params['report'])){
			$actionID = $params['actionID'];
            $reportIssueTemp = unserialize(getOption('reportIssueTemp'));
            if((!isset($reportIssueTemp[$actionID])) || (empty($reportIssueTemp[$actionID]))) {
                return false;
            }
			$params['reportBase64'] = base64_encode(serialize($reportIssueTemp[$actionID]));
			unset($params['report']);
            updateOption('reportIssueTemp','');
		}
		elseif($params['type'] == 'userIssue'){//user Issue
			$temp = getReportIssueData('', 'userIssue');
			$params['reportBase64'] = base64_encode(serialize($temp['report']));
			unset($temp);
		}
		
		$data = array('reportData' => $params);
		if(function_exists('gzcompress')){
			$data['reportDataCompressed'] = gzcompress(serialize($data['reportData']));
			unset($data['reportData']);
		}
		
		$temp = doCall(getOption('serviceURL').'report.php', $data, $timeout=60);
		list($result) = $temp;
		
		$result = getServiceResponseToArray($result);
		
		if($result['status'] == 'true'){
			return true;
		}
	}
	return false;
}

function addOption($optionName, $optionValue){
	return DB::insert("?:options", array('optionName' => $optionName, 'optionValue' => $optionValue));
}

function updateOption($optionName, $optionValue){
	$where = array(
          'query' => "optionName = ':optionName'",
          'params' => array(
               ':optionName'=>$optionName
           )
        );
	
	if(isExistOption($optionName)){
		return DB::update("?:options", array('optionName' => $optionName, 'optionValue' => $optionValue), $where);
	}
	else{
		return addOption($optionName, $optionValue);
	}
	
}

function getOption($optionName){
	$where = array(
          'query' => "optionName = ':optionName'",
          'params' => array(
               ':optionName'=>$optionName
           )
        );
	return DB::getField("?:options", "optionValue", $where);
}

function deleteOption($optionName){
	$where = array(
          'query' => "optionName = ':optionName'",
          'params' => array(
               ':optionName'=>$optionName
           )
        );
	return DB::delete("?:options", $where);
}

function isExistOption($optionName){
	$where = array(
			      		'query' =>  "optionName = ':optionName'",
			      		'params' => array(
			               ':optionName'=>$optionName
           				)
        			);
	return DB::getExists("?:options", "optionID", $where);
}

/* Manipulating "Options" */ 

function manipulateOption($optionName, $optionValue){
	$where = array(
			      		'query' =>  "optionName = ':optionName'",
			      		'params' => array(
			               ':optionName'=>$optionName
           				)
        			);
	$optionVal = DB::getField("?:options", "optionValue", $where);
	$arrayVal = unserialize($optionVal);
	
	if(is_array($arrayVal) && !empty($arrayVal))
	{
		$slug = $optionValue['slug'];
		if(!array_key_exists($slug, $arrayVal))
		{
			$arrayVal[$slug] = array();
		}
		elseif(array_key_exists($slug, $arrayVal))
		{
			$arrayEnd = end($arrayVal[$slug]);
			$optionValue['prevVer'] = $arrayEnd['newVer'];
			reset($arrayVal);
		}
		
	}
	elseif(!is_array($arrayVal) || empty($arrayVal))
	{
		$arrayVal = array();
		$slug = $optionValue['slug'];
		$arrayVal[$slug] = array();
	}
	
	array_push($arrayVal[$slug], $optionValue);
	$serVer = serialize($arrayVal);	 
	updateOption($optionName, $serVer);
}

/* Manipulating "Options" */ 

function sendAppMail($params = array(), $contentTPL = '', $options=array()){
	$TPLOptions = array('isMailFormattingReqd' => true);
	$content = TPL::get($contentTPL, $params, $TPLOptions);
	$content = explode("+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-", $content);
	$subject = $content[0];
	$message = $content[1];
	$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$params['userID']
           				)
        			);
	$user = DB::getRow("?:users", "email, name", $where);
	$to = $user['email'];
	$toName = $user['name'];
	$emailDetails = getFromMail($options);
	$from = $emailDetails['fromEmail'];
	$fromName = $emailDetails['fromName'];
	if(isset($options['isTest']) && !empty($options['isTest'])){
		$emailSettings = getOption("emailTempSettings");		//for sending test email use temporary value.
	}
	else{
		$emailSettings = getOption("emailSettings");
	}
	if($emailSettings){
		$emailSettings = unserialize($emailSettings);
	}
	$options['emailSettings'] = $emailSettings;
	
	return sendMail($from, $fromName, $to, $toName, $subject, $message, $options);	
	
}

function getNoReplyEmailIDByHost(){
	$URL_Info = parse_url(APP_URL);
	$host = trim($URL_Info['host'],'www.');
	if(function_exists('checkdnsrr') && checkdnsrr($host)){
		$noReplayMailID = 'noreply-iwp@' . $host;
		return $noReplayMailID;
	}
	else{
		return false;
	}
}

function getDefaultFromEmail(){
	$where = array(
			      		'query' =>  "accessLevel=':accessLevel' ORDER BY userID LIMIT ". 1,
			      		'params' => array(
			               ':accessLevel'=>'admin'
           				)
        			);
	$user = DB::getField("?:users", "email",$where);
	$noReplayMailID = getNoReplyEmailIDByHost();
	if($noReplayMailID){
		return $noReplayMailID;
	}
	else{
		return $user;
	}
	
}

function getFromMail($options=array()){	
	$emailDetails = array();
	if(isset($options['isTest']) && !empty($options['isTest'])){
		$emailSettings = getOption("emailTempSettings");		//for sending test email use temporary value.
	}
	else{
		$emailSettings = getOption("emailSettings");
	}
	$emailSettings = unserialize($emailSettings);
	$fromEmail = $emailSettings['fromEmail'];
	$fromName = $emailSettings['fromName'];
	if($fromName){
		$emailDetails['fromName'] = $fromName;
	}
	else{
		$emailDetails['fromName'] = 'InfiniteWP';
	}
	if($fromEmail){
		$emailDetails['fromEmail'] = $fromEmail;
	}
	else{
		$emailDetails['fromEmail'] = getDefaultFromEmail();
	}

	return $emailDetails;
}

/*
* Log recent 10 cron initiated time. only for system crons.
*/
function systemCronRunLog($time){
	
	$recentLogs = array();
	
	$getRecentCronLog = getOption('cronRecentRunLog');
	
	if(!empty($getRecentCronLog)){
		$recentLogs = unserialize($getRecentCronLog);
	}
	
	if(count($recentLogs) >= 10){
		array_shift($recentLogs);
	}
	
	array_push($recentLogs, $time);
	updateOption('cronRecentRunLog', serialize($recentLogs));
}

function cronRun(){
	
	ob_start();
	
	ignore_user_abort(true);
	set_time_limit(0);
	//$userID = DB::getField("?:users", "userID", "1 ORDER BY userID ASC LIMIT 1");
	$where = array(
			      		'query' =>  "accessLevel=':accessLevel' ORDER BY userID LIMIT 1",
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

	$time = time();
	updateOption('cronLastRun', $time); //sometime cron may take long time to complete, so updating first last cron run

	
	if(CRON_MODE == 'systemCronShortTime' || CRON_MODE == 'systemCronDefault'){
		systemCronRunLog($time);
	}
	
	updatesNotificationMailRunCheck();
	
	$temp = array();
	setHook('cronRun', $temp);
	
	$settings = Reg::get('settings');
	//if($settings['executeUsingBrowser'] == 1){//this condition is commented to fix #0000221
		do{
			$status = executeJobs();
		}
		while($status['requestInitiated'] > 0 && $status['requestPending'] > 0 && ($GLOBALS['cronStartTime'] + CRON_TIMEOUT) > time());
	//}
	
	include_once(APP_ROOT."/includes/favicon.php");
	Favicon::getFaviconsCronJob($GLOBALS['cronStartTime']);
	
	//before ending
	if(CRON_MODE == 'easyCronTask'){
		//if no more new task(jobs) self disable task cron of easy cron
		if(!isAnyActiveOrPendingJobs()){
			$getNextTaskScheduleTime = getNextTaskScheduleTime();
			if( !(!empty($getNextTaskScheduleTime) && $getNextTaskScheduleTime < (time() + 30*60)) ){//no new task for next 30 mins
				//disable here
				manageEasyCron::taskCronDisable();
			}
		}
	}

	ob_end_clean();

	if(CRON_MODE == 'IWP_Cron'){
		$response = array('pingBack' => true);
		$noNewTaskScheduled = !isAnyActiveOrPendingJobs();
		if($noNewTaskScheduled){
			$getNextTaskScheduleTime = getNextTaskScheduleTime();
			$minutes = 30;
			$isAtleast30MinutesAway = !timeUtils::isLessThanNowInMinutes($minutes, $getNextTaskScheduleTime);
			if($isAtleast30MinutesAway){
				$response['pingBack'] = false;
			}
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}

}


function updatesNotificationMailRunCheck($getNextScheduleTime = false){
	
	/*$settings = panelRequestManager::getSettings();
	$updatesNotificationMail = $settings['notifications']['updatesNotificationMail'];*/

	$isAtleastOneSiteAdded = DB::getExists("?:sites", "siteID", "type = 'normal' LIMIT 1");
	if(!$isAtleastOneSiteAdded){
		return false;//if no sites are there, then don't send update notification mail
	}

	$getAllNotifications = DB::getArray("?:users", "userID, accessLevel, permissions, notifications, updatesNotificationMailLastSent", 1, "userID");
	if(!empty($getAllNotifications))
	foreach($getAllNotifications as $userID => $userData){
		
		if(empty($userData['notifications'])){ continue; }
		if($userData['accessLevel'] == 'manager'){
			$permissions = unserialize($userData['permissions']);
			if(empty($permissions['access']) || (!empty($permissions['access']) && !in_array('updates', $permissions['access']))){
				continue;
			}
		}
		
		$updatesNotificationMail = unserialize($userData['notifications']);
		$updatesNotificationMail = $updatesNotificationMail['updatesNotificationMail'];
		//check setting
		if($updatesNotificationMail['frequency'] == 'never' || (empty($updatesNotificationMail['coreUpdates']) && empty($updatesNotificationMail['pluginUpdates']) && empty($updatesNotificationMail['themeUpdates'])) ){
			continue;//	updatesNotificationMail is disabled in the settings
		}
		//get updates Notification Mail Last Sent
		$updatesNotificationMailLastSent = $userData['updatesNotificationMailLastSent'];
		
		//check last run falls within the frequency
		if($updatesNotificationMailLastSent > 0){
			if($updatesNotificationMail['frequency'] == 'daily'){
				$frequencyStartTime = strtotime(@date("d F Y 10:00:00",time())); // changed to send at 10:00 am localtime
			}
			elseif($updatesNotificationMail['frequency'] == 'weekly'){//mon to sun week
				$day = @date('w', time());
				// $frequencyStartTime = mktime(0, 0, 1, @date('m'), @date('d') - ($day > 0 ? $day: 7) + 1, @date('Y'));
				$frequencyStartTime = strtotime((@date('d',time()) - $day+1)." ".@date("F Y 10:00:00",time()));	// changed to send on monday,10:00 am localtime
			}
			else{
				continue;//user not subscribed to update notification mail
			}
			if($updatesNotificationMailLastSent < $frequencyStartTime  && $frequencyStartTime <= time()){
				//send mail
			}
			else{
				//stop mail 0 => '1409655692',  1 => 1409670000,  2 => 1409656008	
				continue;//already sent or time less than 10:00 according to local time
			}
		

		}
		//To get the time schedued to send the update mail Notification.
		if($getNextScheduleTime){
			if(isset($frequencyStartTime)){
				$time = $frequencyStartTime;
			}else{
				$time = time();
			}
			return $time;
		}
		return updatesNotificationMailSend();
	
	}
	
}

function updatesNotificationMailSend($force=false){
	
	if($force == false){
		$getAllNotifications = DB::getArray("?:users", "userID, accessLevel, permissions, notifications, updatesNotificationMailLastSent", 1, "userID");
	}
	else{
		$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$GLOBALS['userID']
           				)
        			);
		$getAllNotifications = DB::getArray("?:users", "userID, accessLevel, permissions, notifications, updatesNotificationMailLastSent", $where, "userID");
	}
		
	$run = 1;
	if(!empty($getAllNotifications))
	foreach($getAllNotifications as $userID => $userData){
		
		if(empty($userData['notifications'])){
			continue;
		}

		if($userData['accessLevel'] == 'manager'){
			$permissions = unserialize($userData['permissions']);
			if(empty($permissions['access']) || (!empty($permissions['access']) && !in_array('updates', $permissions['access']))){
				continue;
			}
			
		}
		$UID = $userID;
		
		$updatesNotificationMail = unserialize($userData['notifications']);
        $updatesNotificationMail = $updatesNotificationMail['updatesNotificationMail'];
		//check setting
		if($force == false){
			if($updatesNotificationMail['frequency'] == 'never' || (empty($updatesNotificationMail['coreUpdates']) && empty($updatesNotificationMail['pluginUpdates']) && empty($updatesNotificationMail['themeUpdates'])) ){
				continue; //updatesNotificationMail is disabled in the settings
			}
		}
		if($force == false && $run == 1){ //for test mail(i.e $force == true) dont need to get new data
			
			Reg::set('currentRequest.actionID', uniqid('', true));
			$lastNHours = time() - (60*60*2);//2 hours

			$getRemainingSiteIDsQuery = "SELECT S.siteID FROM ?:sites S LEFT OUTER JOIN ?:history H on S.siteID = H.siteID and H.type = 'stats' and H.action = 'getStats' and H.microtimeInitiated >= ".DB::esc($lastNHours)." and H.status IN('completed', 'error', 'netError') where H.siteID is null";
			$remainingSiteIDs = DB::getFields($getRemainingSiteIDsQuery);//Assuming for all the sites reload data can happen in 2 hours.

			if(!empty($remainingSiteIDs) && is_array($remainingSiteIDs)){
				foreach($remainingSiteIDs as $siteID) {
					$where = array(
			      		'query' =>  "H.siteID = :siteID AND H.type = ':type' AND H.action = ':action'and H.microtimeInitiated >= ':microtimeInitiated' AND showUser = 'N'",
			      		'params' => array(
			               ':siteID'=>$siteID,
			               ':type'=>'stats',
			               ':action'=>'getStats',
			               ':microtimeInitiated'=>$lastNHours
           				)
        			);
					$isAlreadyAdded = DB::getExists("?:history H", "H.historyID", $where);
					if($isAlreadyAdded){
						continue;//no need to trigger new one, has one is already in place.
					}
					
					$extras = array('directExecute' => true, 'sendAfterAllLoad' => false, 'doNotShowUser' => true);//'directExecute' => true reload data calls will be executed directly blocking mode
					manageClientsFetch::getStatsProcessor(array($siteID), array(), $extras);
					$run = 0;	

					if(($GLOBALS['cronStartTime'] + CRON_TIMEOUT) < time()){ break;}
				}
			}

			$remainingSiteIDs = DB::getFields($getRemainingSiteIDsQuery);
			if(!empty($remainingSiteIDs)){ return false; }//If all sites reload data not completed as per above criteria, then go out of this function. In the next cron call it will continue from where it left. As the above while function this case will come from CRON_TIMEOUT meets.
		}



		//getting updateInformation
		$sitesUpdates = panelRequestManager::getSitesUpdates($UID, false);
		$hiddenUpdates = panelRequestManager::getHide();
		
		foreach($hiddenUpdates as $siteID => $value){
			foreach($value as $d){
				unset($sitesUpdates['siteView'][$siteID][$d['type']][$d['URL']]);
				
				//this will fix the site name shows even all update items are hidden
				if(empty($sitesUpdates['siteView'][$siteID][$d['type']])){
					unset($sitesUpdates['siteView'][$siteID][$d['type']]);
				}
				if(empty($sitesUpdates['siteView'][$siteID])){
					unset($sitesUpdates['siteView'][$siteID]);
				}
			}
		}
		if(!empty($sitesUpdates['siteView']) && ( empty($updatesNotificationMail['coreUpdates']) ||  empty($updatesNotificationMail['pluginUpdates']) || empty($updatesNotificationMail['themeUpdates']) || empty($updatesNotificationMail['translationUpdates']) )){//this will fix  when the "plugin" not selected in settings. Site name shows with empty list when plugin update is available
			foreach($sitesUpdates['siteView'] as $siteID => $value){
				if(empty($updatesNotificationMail['coreUpdates'])){
					unset($sitesUpdates['siteView'][$siteID]['core']);
				}
				if(empty($updatesNotificationMail['pluginUpdates'])){
					unset($sitesUpdates['siteView'][$siteID]['plugins']);				
				}
				if(empty($updatesNotificationMail['themeUpdates'])){
					unset($sitesUpdates['siteView'][$siteID]['themes']);
				}
				if(empty($updatesNotificationMail['translationUpdates'])){
					unset($sitesUpdates['siteView'][$siteID]['translations']);
				}
				if(empty($sitesUpdates['siteView'][$siteID])){
					unset($sitesUpdates['siteView'][$siteID]);
				}
			}
		}
		if (!empty($updatesNotificationMail['WPVulnsUpdates'])) {
			setHook('updateNotificationViews', $sitesUpdates, $UID);
		}
		$params = array('userID' => $userID, 'sitesUpdates' => $sitesUpdates,  'updatesNotificationMail' => $updatesNotificationMail, 'updateNotificationDynamicContent' => getOption('updateNotificationDynamicContent'));
		$isSent = sendAppMail($params, '/templates/email/updatesNotification.tpl.php');
		$where = array(
		      		'query' =>  "userID = ':userID'",
		      		'params' => array(
		               ':userID'=>$userID
       				)
    			);
		if($isSent){
			if(!$force){
				DB::update("?:users", array('updatesNotificationMailLastSent' => time()), $where);
			}
		}
		else{
			if(!$force){
				DB::update("?:users", array('updatesNotificationMailLastSent' => time()),$where);//even mail sending failed mark as sent this will avoid re-trying, customer will be notified if mail not sent using offline notification
			}
		}
	}
	return true;
}


function cronReloadCompCheck($statsInitiateTime){
		
	$limit = DB::getField("?:sites", "count(*)", 1);
	$where = array(
			      		'query' =>  "HAD.historyID = :historyID AND H.action = ':action' AND H.type = ':type' ORDER BY H.historyID DESC LIMIT ".DB::esc($limit),
			      		'params' => array(
			               ':historyID'=>'H.historyID',
			               ':action'=>'getStats',
			               ':type'=>'stats'
           				)
        			);
	$siteIds = DB::getArray("?:sites S LEFT JOIN ?:site_stats SS ON S.siteID = SS.siteID LEFT JOIN ?:history H ON S.siteID = H.siteID, ?:history_additional_data HAD", "HAD.errorMsg, S.siteID, SS.lastUpdatedTime, H.status", $where);
	
	$statsVal = array();
	$statsVal['statsInitiateTime'] = $statsInitiateTime;
	
	foreach($siteIds as $key => $value){
		if($value['lastUpdatedTime'] > $statsInitiateTime && $value['lastUpdatedTime'] < $statsInitiateTime+60*60 && $value['status'] == 'completed'){
			$statsVal['completed'][] = $value;
		}
		elseif($value['status'] == 'netError' || $value['status'] == 'error' || ($value['status'] == 'completed' && !empty($value['errorMsg']))){
			$statsVal['ignore'][] = $value;
		}
		else{
			$statsVal['retry'][] = $value;
		}
	}
	
	updateOption("cronReloadCompCheck", serialize($statsVal));
	return $statsVal;
}

function getAllSitesStatsWithError(){
	//reloading stats from all the sites
	
	//option 1 - While doing cron - latest
	$statsInitiateTime = time(); $siteIDs = '';
	
	//option 1 - While doing cron - latest
	$proceed = cronReloadCompCheck($statsInitiateTime);
	if(!empty($proceed) && isset($proceed['retry']) && !empty($proceed['retry'])){
		return false;
	}
}

function getCachedUpdateDetails(){
	$updateAvailable = getOption('updateAvailable');
	if(!empty($updateAvailable)){
		$updateAvailable = @unserialize($updateAvailable);
		if($updateAvailable == 'noUpdate'){
			return false;
		}
		return $updateAvailable;
	}
	return false;
}

function checkUpdate($force=false, $checkForUpdate=true){
	
	$currentTime = time();
	$updateLastChecked = getOption('updateLastCheck');
	if(!$force){
		$updateCheckInterval = 86400;//86400 => 1 day in seconds
	
		if(stripos(APP_VERSION, 'beta') !== false) $updateCheckInterval = (60 * 60 * 4);//60 * 60 * 4 every 4 hrs. betaClientPlugin update notification comes via checkUpdate

		if( ($currentTime - $updateLastChecked) < $updateCheckInterval ){
			return getCachedUpdateDetails();
		}
	}
	
	if(!$checkForUpdate){
		return false;
	}
	
	$updateLastTried = getOption('updateLastTry');
	if(!$force && $updateLastTried > 0 && $updateLastChecked > 0 && ($currentTime - $updateLastTried) < 600){//auto checkUpdate after 600 secs
		return false;
	}
	if(!$force && $updateLastTried > 0 && ($currentTime - $updateLastTried) <= 10){//no new checkUpdate within 10 secs of last try
		return getCachedUpdateDetails();
	}
	updateOption('updateLastTry', $currentTime);

	$URL = getOption('serviceURL').'checkUpdate.php';	
	$URL .= '?appVersion='.APP_VERSION.'&appInstallHash='.APP_INSTALL_HASH.'&installedHash='.getInstalledHash();
	
	$installedEmail = getOption('installedEmail');
	if(!empty($installedEmail)){
		$URL .= '&installedEmail='.urlencode($installedEmail);
	}
	
	$partnerHost = getOption('partnerHost');
	if(!empty($partnerHost)){
		$URL .= '&partnerHost='.urlencode($partnerHost);
	}
	
	//$installedAddons = getInstalledAddons();
	$addonDetails = Reg::get('addonDetails');
	if(!empty($addonDetails)){
		foreach($addonDetails as $addonSlug => $addon){
			$URL .=  '&checkAddonsUpdate['.$addonSlug.']='.$addon['version'];		
		}
	}
	$currentGeneralSettings = Reg::get('settings');
	if($currentGeneralSettings['participateBeta'] == 1){
		$URL .= '&participateBeta=1';
	}	
	if($force){
		$URL .= '&force=1';
	}
    if(defined('DEV_UPDATE')) {
		$URL .= '&devUpdate='.DEV_UPDATE;
    }
	
	$suiteDetails = unserialize(getOption('suiteDetails'));
	if(empty($suiteDetails) and !is_array($suiteDetails)) {
		$cancelMessageFlag='';
		$addonSuiteMiniActivity='';
	} else {
		$cancelMessageFlag=$suiteDetails['cancelMessageFlag'];
		$addonSuiteMiniActivity=(isset($suiteDetails['addonSuiteMiniActivity']) && $suiteDetails['addonSuiteMiniActivity']!='')?$suiteDetails['addonSuiteMiniActivity']:'';
	}
	$URL .= '&cancelMessageFlag='.$cancelMessageFlag.'&addonSuiteMiniActivity='.$addonSuiteMiniActivity;
	
	$temp = doCall($URL, '', $timeout=30);
	list($result, , , $curlInfo) = $temp;

	$result = getServiceResponseToArray($result);//from 2.0.8 follows <IWPHEADER><IWPHEADEREND> wrap in return value	
	$curlErrors = new curlErrors($curlInfo);
	if(!$curlErrors->isOk()){
		return $curlErrors->getErrorDetails();
	}
	
	if(empty($result)){
		//response error
		return false;
	}
	
	setHook('updateCheck', $result);
	
	if(!empty($result['updateNotificationDynamicContent'])){
		updateOption('updateNotificationDynamicContent', $result['updateNotificationDynamicContent']);
	}
	
	updateOption('updateLastCheck', $currentTime);
	
	if(isset($result['registerd'])){
		updateAppRegistered($result['registerd']);
	}
	
	if(isset($result['addons'])){
		processCheckAddonsUpdate($result['addons']);
	}
	
	if(isset($result['promos'])){
		updateOption('promos', serialize($result['promos']));
	}
	
	if(isset($result['clientPluginBetaUpdate'])){
		updateOption('clientPluginBetaUpdate', serialize($result['clientPluginBetaUpdate']));
	}
        if(isset($result['checkClientUpdate'])){
		updateOption('clientPluginUpdate', serialize($result['checkClientUpdate']));
	}

	if(isset($result['iwpCronInvited'])){
            $isInvitedEarlier = getOption('iwpCronInvited');
            if(!$isInvitedEarlier){
                updateOption('iwpCrontInvitedNotificationReq', true);
            }
            $iwpCronInvited = $result['iwpCronInvited'];
            updateOption('iwpCronInvited', $iwpCronInvited);
	}

	if(isset($result['mark'])){ //codeSprint
		dispatchMark($result['mark']);
	}
	
	if($result['checkUpdate'] == 'noUpdate'){
		updateOption('updateAvailable', '');
		return false;
	}
	else{
		updateOption('updateAvailable', serialize($result['checkUpdate']));
		return $result['checkUpdate'];
	}
	
}

function processAppUpdate(){//download and install update
	$updateAvailable = checkUpdate(false, false);
	if(empty($updateAvailable)){
		return false;	
	}
	
	$newVersion = $updateAvailable['newVersion'];
	
	$optionVer['action'] = 'updated';
	$optionVer['actionTime'] = time();
	$optionVer['prevVer'] = APP_VERSION;
	$optionVer['newVer'] = $newVersion;
	$optionVer['slug'] = 'IWPAdminPanel';
	
	if(version_compare(APP_VERSION, $newVersion) !== -1){
		return false;
	}
	
	$updateDetails = $updateAvailable['updateDetails'][$newVersion];
	
	if(!empty($updateDetails['downloadLink']) && !empty($updateDetails['fileMD5'])){
		
		$updateZip = getTempName('appUpdate.zip');
	
		appUpdateMsg('Downloading package..');
		
		$isDone = downloadURL($updateDetails['downloadLink'], $updateZip);		
		
		if(!$isDone){ //download failed
			appUpdateMsg('Download Failed.', true);
			return false;
		}
				
		if(md5_file($updateZip) != $updateDetails['fileMD5']){
			appUpdateMsg('File MD5 mismatch(damaged package).', true);
			return false;	
		}
		
		if(!initFileSystem()){
			appUpdateMsg('Unable to initiate file system.', true);
			return false;
		}
		
		$unPackResult = unPackToWorkingDir($updateZip);
		if(!empty($unPackResult) && is_array($unPackResult)){
			$source = $unPackResult['source'];
			$remoteSource = $unPackResult['remoteSource'];
		}
		else{
			return false;	
		}
		
		$destination = APP_ROOT;
		if(!copyToRequiredDir($source, $remoteSource, $destination)){
			return false;	
		}
		
		appUpdateMsg('Finalizing update..');	
		if(file_exists(APP_ROOT.'/updateFinalize_v'.$newVersion.'.php')){
			//$updateAvailable variable should be live inside the following file
			include(APP_ROOT.'/updateFinalize_v'.$newVersion.'.php');//run the update file
			
			if($GLOBALS['updateFinalizeStatus'] === true || $updateFinalizeStatus === true){
				
				@unlink($updateZip);
				updateOption('updateAvailable', '');
				
				appUpdateMsg('Updated successfully.', false);
				manipulateOption('versionLogs', $optionVer);
				return true;
			}
			else{
				appUpdateMsg('Update failed.', true);
			}
		}
		else{
			//updateFinalize file not found	
			appUpdateMsg('Update finalizing file missing.', true);
		}
		@unlink($updateZip);
		return false;	
	}	
	
	
}

function unPackToWorkingDir($updateZip){
	
	if(empty($updateZip)) return false;
	
	$workingDir = APP_ROOT.'/updates';
	
	$updatesFolder = $GLOBALS['FileSystemObj']->findFolder($workingDir);
		
	//Clean up contents of upgrade directory beforehand.
	$updatesFolderFiles = $GLOBALS['FileSystemObj']->dirList($updatesFolder);
	if ( !empty($updatesFolderFiles) ) {
		$temp = basename($updateZip);
		foreach ( $updatesFolderFiles as $file ){
			if($temp != $file['name'])
			$GLOBALS['FileSystemObj']->delete($updatesFolder . $file['name'], true);
		}
	}

	//We need a working directory
	//removing the extention
	$updateZipParts = explode('.', basename($updateZip));
	if(count($updateZipParts) > 1) array_pop($updateZipParts);
	$tempFolderName = implode('.', $updateZipParts);
	if(empty($tempFolderName)) return false;
	
	$remoteWorkingDir = $updatesFolder . $tempFolderName;
	$workingDir = addTrailingSlash($workingDir). $tempFolderName;

	// Clean up working directory
	if ( $GLOBALS['FileSystemObj']->isDir($remoteWorkingDir) )
		$GLOBALS['FileSystemObj']->delete($remoteWorkingDir, true);

	// Unzip package to working directory
	$result = $GLOBALS['FileSystemObj']->unZipFile($updateZip, $remoteWorkingDir); //TODO optimizations, Copy when Move/Rename would suffice?

	// Once extracted, delete the package.
	@unlink($updateZip);
	
	if ( $result == false ) {
		$GLOBALS['FileSystemObj']->delete($remoteWorkingDir, true);
		return false;
	}
	
	return array('source' => $workingDir, 'remoteSource' => $remoteWorkingDir);
}

function copyToRequiredDir($source, $remoteSource, $destination){
	
	$remoteSourceFiles = array_keys( $GLOBALS['FileSystemObj']->dirList($remoteSource) );
	if(empty($remoteSourceFiles)){
		appUpdateMsg('Unable to retrieve the directory list('.$remoteSource.').'.(($GLOBALS['FileSystemObj']->getMethod() == 'FTPExt') ? ' Please check APP_FTP_BASE folder path in config.php. It should point to the IWP root folder.' : ''), true);
		return false;
	}
	
	
	@set_time_limit( 300 );
	//$destination = APP_ROOT;
	$remoteDestination = $GLOBALS['FileSystemObj']->findFolder($destination);

	//Create destination if needed
	if ( !$GLOBALS['FileSystemObj']->exists($remoteDestination) )
		if ( !$GLOBALS['FileSystemObj']->mkDir($remoteDestination, FS_CHMOD_DIR) ){
			//return new WP_Error('mkdir_failed', $this->strings['mkdir_failed'], $remoteDestination);
			appUpdateMsg('Unable to create directory '.$remoteDestination, true);
			return false;
		}

	// Copy new version of item into place.
	$result = $GLOBALS['FileSystemObj']->copyDir($remoteSource, $remoteDestination);
	if ( !$result ) {
		$GLOBALS['FileSystemObj']->delete($remoteSource, true);
		return $result;
	}

	//Clear the Working folder?
	$GLOBALS['FileSystemObj']->delete($remoteSource, true);	
	
	return true;
}

function appUpdateMsg($msg, $isError=0){
	echo '<br>'.$msg;
	if($isError === true){
		?>
        <br />Try again by refreshing the panel or contact <a href="mailto:help@infinitewp.com">help@infinitewp.com</a>
         <script>
		window.parent.$("#updates_centre_cont .btn_loadingDiv").remove();
		</script>
		<?php
	}
	elseif($isError === false){
		?>
        <script>
		window.parent.$("#updates_centre_cont .btn_loadingDiv").remove();
		window.parent.$(".updateActionBtn").attr({'btnaction':'reload','target':'_parent', 'href':'<?php echo APP_URL; ?>'}).text('Reload App').removeClass('disabled');
		</script>
		<?php
	}
	?>
	<script>
	window.scrollTo(0, document.body.scrollHeight);
	</script>
    <?php
	ob_flush();
	flush();
}

function runOffBrowserLoad(){
	$GLOBALS['offline'] = true;
	checkUpdate();
	anonymousDataRunCheck();
	checkRawHistoryAndDelete();
	autoSelectConnectionMethod();
	
	if(manageEasyCron::isActive()){
		manageEasyCron::manageCronEnable();
	}

	$temp = array();
	setHook('runOffBrowserLoad', $temp);
}

function runWhileBrowserIdle(){
	getFaviconsPanelIdle();
	processExpiredCacheFiles();
}

function processExpiredCacheFiles(){
	$lastCheck = getOption('lastExpiredCacheFilesCheck');
	$minInterval = 60 * 60 * 24; // once per day
	$nextCheck = $lastCheck + $minInterval;
	if ($nextCheck >= time()) {
		return false;
	}
	include_once(APP_ROOT."/includes/HTMLHeadCache.php");
	$cacheObj = new cacheCommon();
	$cacheObj->processExpiredCacheFiles();
	updateOption('lastExpiredCacheFilesCheck', time());
}

function getFaviconsPanelIdle(){
	include_once(APP_ROOT."/includes/favicon.php");
	return Favicon::getFaviconsPanelIdle();
}

function getResponseMoreInfo($historyID){
	$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			               ':historyID'=>$historyID
           				)
        			);
	$rawData = DB::getRow("?:history_raw_details", "response, callInfo", $where);
	$rawResponseData = $rawData['response'];
	$curlInfo = unserialize($rawData['callInfo']);

	stripHeaders($rawResponseData, $curlInfo);
	
	$startStr = '<IWPHEADER>';
    $endStr = '<ENDIWPHEADER';

	$response_start = stripos($rawResponseData, $startStr);	
	if($response_start === false){
		return $rawResponseData;
	}
	$str = substr($rawResponseData, 0, $response_start);
	
	$response_End = stripos($rawResponseData, $endStr);
	$Estr = substr($rawResponseData, $response_End + strlen($endStr));
	$Estr = (substr($Estr, 0, 1) == '>') ? substr($Estr, 1) : $Estr;
	return $str.$Estr;
}

/*
$type = 'N' -> notice, 'W' -> Warning, 'E' -> Error
$state = 'T' -> Timer, 'U' -> user should close it
* $scheduleTime = Time stamp, When the Notification is triggered
*/
function addNotification($type, $title, $message, $state='T', $callbackOnClose='', $callbackReference='', $scheduleTime=''){


        $offlineNotifications = getOption('offlineNotifications');
        $offlineNotifications = (!empty($offlineNotifications)) ? @unserialize($offlineNotifications) : array();
        $notifications = &$offlineNotifications;
	
	$key =  md5($type.$title.$message);
	if(empty($notifications[$key])){
		$schedule = ($scheduleTime=='')?0:$scheduleTime;
		$notifications[$key] = array('key' => $key,
									 'type' => $type,
									 'title' => $title, 
									 'message' => $message, 
									 'state' => empty($callbackOnClose) ? $state : 'U', 
									 'callbackOnClose' => $callbackOnClose, 
									 'callbackReference' => $callbackReference, 
									 'counter' => 1,
									 'time' => time(),
									 'schedule' => $schedule,
									 'notified' => false);
	}
	else{
		$notifications[$key]['counter']++;
	}
	
	if(!empty($offlineNotifications)){
		//save in db
		updateOption('offlineNotifications', serialize($offlineNotifications));
	}
}

function getNotifications($reloaded=false){
	
	if(empty($GLOBALS['userID'])){ return false; }//No session, dont send any notifications

	$msg = $schedule = $notifications = $callBack = array();
	
	$offlineNotifications = getOption('offlineNotifications');
	
	if(!empty($offlineNotifications)){
		$offlineNotifications = @unserialize($offlineNotifications);
		/*
		 * Code change for schedule start here
		 */
		$offlineNotifications = (array)$offlineNotifications;
		foreach($offlineNotifications as $key => $messages) {
			if($messages['shcedule']<=time()) {
				$msg[$key] = $messages; 		
			} else {
				$schedule[$key] = $messages;
			}
		}
		$offlineNotifications = $msg;
		if(count($schedule)>0) {
			updateOption('offlineNotifications', serialize($schedule));
		} else {
			updateOption('offlineNotifications', NULL);
		}
		/*
		 * Code change for schedule End here
		 */
			
	}	
	
	if(!empty($offlineNotifications) && is_array($offlineNotifications))
	foreach($offlineNotifications as $key => $_notification){
                $notifications[] = $_notification;
		/*if(!empty($_notification['callbackOnClose'])){
			if($reloaded || !$_notification['notified']){
				$offlineNotifications[$key]['notified'] = true;
				$notifications[] = $_notification;
			}
		}
		else{
			unset($offlineNotifications[$key]);
			$notifications[] = $_notification;
		}*/		
	}

	
	return $notifications;	
}

function closeNotification($key){
	//only happens when user logged in
}

function installFolderAlert(){
	if(is_dir(APP_ROOT.'/install')){
		$lastNotify = getOption('lastRemoveInstallFolderNotified');
		$retryTime = 60 * 60 * 24 * 30; // send notification again after one month
		$lastNotify += $retryTime;
		if ($lastNotify >= time()) {
			return false;
		}
		addNotification($type='E', $title='Security Warning!', $message='Please remove or rename the "install" folder.', $state='U', $callbackOnClose='', $callbackReference='');
		updateOption('lastRemoveInstallFolderNotified', time());
	}
}

function userStatus($userID = false){
	if (!$userID) {
		$userID = $GLOBALS['userID'];
	}
	$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=> $userID
           				)
        			);
	$status = DB::getField("?:users", "accessLevel", $where);
	return $status;
}

function setHook($hook, &$arg1 = NULL, &$arg2 = NULL, &$arg3 = NULL, &$arg4 = NULL, &$arg5 = NULL, &$arg6 = NULL, &$arg7 = NULL, &$arg8 = NULL, &$arg9 = NULL){
	
	$num = func_num_args();
	$args = array();
	for ($i = 1; $i < 10; $i++) {//$i = 1, skiping first arg which is always a hook name
		$argName = 'arg' . $i;
		if ($i < $num) {
			$args[$i] = &$$argName;
		}
		unset($$argName, $argName);
	}
	
	$hooks = Reg::get('hooks');
	if(empty($hooks[$hook])){
		return false;
	}
	foreach($hooks[$hook] as $func){
		if(function_exists($func)){
			call_user_func_array($func, $args);
		}
	}
}


function regHooks(){
	
	$args = func_get_args();
	
	$hooks = Reg::get('hooks');
	
	$backtrace = debug_backtrace();
	
	$addonSlug = basename(dirname($backtrace[0]['file']));
	
	foreach($args as $arg){
		$arg = trim($arg);
		if(empty($arg) || !is_string($arg)){
			continue;
		}
		if(!isset($hooks[$arg])){
			$hooks[$arg] = array();
		}
		
		$hooks[$arg][] = $addonSlug.ucfirst($arg);
		
		$hooks[$arg] = array_unique($hooks[$arg]);		
	}	
	Reg::set('hooks', $hooks);
}

function getInstalledAddons($withUpdates=false){
	$addons = DB::getArray("?:addons", "*", "1", 'slug');
        
        if(empty($addons)){
		return array();
	}
	foreach($addons as $slug => $addon){
		$addons[$slug]['version'] = getAddonVersion($slug);	
		unset($addons[$slug]['validityExpires']);//dont trust the data saved in app db so unsetting
	}
	
	if($withUpdates){
		$updateAddonsAvailable = @unserialize(getOption('updateAddonsAvailable'));
			
		if(!empty($updateAddonsAvailable)){
			foreach($updateAddonsAvailable as $slug => $updateAddon){
				if(!empty($addons[$slug])){
					if(!empty($updateAddon['updateAvailable'])){			
						$addons[$slug]['updateAvailable'] = $updateAddon['updateAvailable'];				
					}
					//$addons[$slug]['isValidityExpired'] = $updateAddon['isValidityExpired'];//calculate isValidityExpired instead of getting from IWP Service(why? it can show less accurate data, because 24hrs once app check for update and get these data
					$addons[$slug]['validityExpires'] = $updateAddon['validityExpires'];
					$addons[$slug]['datePurchased'] = $updateAddon['datePurchased'];
					
					
					$addons[$slug]['isValidityExpired'] = false;
					if($addons[$slug]['validityExpires'] < time()){
						$addons[$slug]['isValidityExpired'] = true;
					}
					
					//isLifetime is used in addon/view.tpl.php
					$addons[$slug]['isLifetime'] = false;
					if( ($addons[$slug]['validityExpires']-$addons[$slug]['datePurchased']) > (86400 * 365 * 20)){
						$addons[$slug]['isLifetime'] = true;
					}
				}
			}
		}
	}
	
	return $addons;
}

function regSetInstalledAddonsDetails($activeLoadedAddonsSlugs){
	$installedAddons = getInstalledAddons();
	foreach($installedAddons as $addonSlug => $addonDetails){
		if($activeLoadedAddonsSlugs[$addonSlug]){
			$installedAddons[$addonSlug]['isLoaded'] = true;
		}
		else{
			$installedAddons[$addonSlug]['isLoaded'] = false;
		}
	}
	Reg::set('addonDetails', $installedAddons);
}

function getAddonAlertCount(){

	//get addon updates count
	$i = 0;
	$addons = getInstalledAddons(true);	
	if(!empty($addons)){
		foreach($addons as $slug => $addon){
			if(!empty($addon['updateAvailable'])) $i++;
		}
	}
	$i;

	//get new addons(non installed) count
	$newAddons = getNewAddonsAvailable();
	if(!empty($newAddons)){
		$i += count($newAddons);
	}
	return $i;
}

function getAddonUpdateCount(){
	$i = 0;
	$addons = getInstalledAddons(true);	
	if(!empty($addons)){
		foreach($addons as $slug => $addon){
			if(!empty($addon['updateAvailable'])) $i++;
		}
	}
	return $i;
}
function getAddonVersion($slug){
	$matches = array();
	if(file_exists(APP_ROOT.'/addons/'.$slug.'/addon.'.$slug.'.php')){
				
		$fp = fopen(APP_ROOT.'/addons/'.$slug.'/addon.'.$slug.'.php', 'r');
		$fileData = fread($fp, 8192);
		fclose($fp);
		preg_match( '/^[ \t\/*#@]*' . preg_quote( 'version', '/' ) . ':(.*)$/mi', $fileData, $matches);
		return trim($matches[1]);
	}
	return false;
}

function getOldAddonVersion($slug){ //supported from 2.2.0 //this should be used after replacing old addon with new addon files and then to get old version
	$where = array(
			      		'query' =>  "slug = ':slug'",
			      		'params' => array(
			               ':slug'=>$slug
           				)
        			);
	return DB::getField("?:addons", "updateCurrentVersion", $where);
}

function userAddonsAccess(&$activeAddons){
	
	$permissions = userAllowAccessTest();
	
	if(!empty($permissions) && is_array($permissions)){
		foreach($activeAddons as $activeAddonkey => $activeAddonsSlug){
			if(in_array($activeAddonsSlug['slug'], $permissions['restrict'])){
				unset($activeAddons[$activeAddonkey]);
			}
		}
	}
}


function userAllowAccessTest(){
	$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$GLOBALS['userID']
           				)
        			);
	$getPermissions = DB::getField("?:users", "permissions", $where);

	if(!empty($getPermissions)){
		$permissions = unserialize($getPermissions);
		return $permissions;
	}
	return array();
}


function loadActiveAddons(){
	
	$suiteDetails = unserialize(getOption('suiteDetails'));
	
	if(empty($suiteDetails) and !is_array($suiteDetails)) $addonSuiteMiniActivity='';
	else $addonSuiteMiniActivity=$suiteDetails['addonSuiteMiniActivity'];
	
	if(panelRequestManager::checkIsAddonSuiteMiniLimitExceeded('addonSuiteLimitExceededIllegally') && panelRequestManager::getAddonSuiteMiniActivity()=='installed') {
		Reg::set('addonSuiteLimitExceededIllegally', 1);
		Reg::set('addonSuiteLimitExceededAttemp', 1);
	} else {
		Reg::set('addonSuiteLimitExceededIllegally', 0);
		Reg::set('addonSuiteLimitExceededAttemp', 0);
	}
	
	$activeAddons = DB::getArray("?:addons", "slug, status, addon", "1");
        
        if(userStatus() != 'admin'){
		userAddonsAccess($activeAddons);
	}
	$installedAddons = @unserialize(getOption('updateAddonsAvailable'));
	$newAddons = getNewAddonsAvailable();
	$purchasedAddons = array();
        
	if(!empty($installedAddons)){ $purchasedAddons = array_merge($purchasedAddons, array_keys($installedAddons));  }
	if(!empty($newAddons)){ $purchasedAddons = array_merge($purchasedAddons, array_keys($newAddons));  }
	Reg::set('purchasedAddons', $purchasedAddons);
	$uninstallAddons = $uninstall = $activeLoadedAddonsSlugs = $allPurchasedAddonsNameAndSlug = array();
	foreach($activeAddons as $key => $addon){
		if(!in_array($addon['slug'], $purchasedAddons)){
			$uninstall[] = $addon['slug'];
			$uninstallAddons[]['slug'] = $addon['slug'];
		}
		if($addon['status'] == 'active'){
			
			$allPurchasedAddonsNameAndSlug[$addon['slug']] = $addon['addon'];
			
			if(Reg::get('addonSuiteLimitExceededIllegally') && Reg::get('addonSuiteLimitExceededAttemp')) {
				$activeLoadedAddonsSlugs[$addon['slug']] = array('slug' => $addon['slug']);
			} else {
				//if(file_exists(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php')){
				@include_once(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php');
					
				if(method_exists('addon'.ucfirst($addon['slug']),'init')){
					call_user_func(array('addon'.ucfirst($addon['slug']),'init'));
					$activeLoadedAddonsSlugs[$addon['slug']] = array('slug' => $addon['slug']);
				} else{//file not found deactivate the addon
					unset($activeAddons[$key]);
					//DB::delete("?:addons", "slug='".$addon['slug']."'");
					//addNotification($type='E', $title='Addon file missing', $message='The "'.$addon['addon'].'" addon has been removed, since a file is missing.', $state='U', $callbackOnClose='', $callbackReference='');
				}				
			}
		}
	}
	if(!empty($uninstallAddons) && $addonSuiteMiniActivity!='cancelled'){
		addNotification($type='E', $title='Addon error', $message='Addon(s) are not legitimate.', $state='U', $callbackOnClose='', $callbackReference='');
		uninstallAddons($uninstallAddons);
	}
	regSetInstalledAddonsDetails($activeLoadedAddonsSlugs);
	Reg::set('allPurchasedAddonsNameAndSlug', $allPurchasedAddonsNameAndSlug);
	//Reg::set('activeAddons', $activeLoadedAddonsSlugs);
}


function downloadAndInstallAddons($addons){
	$installedHash = getInstalledHash();
	$currentGeneralSettings = Reg::get('settings');
	foreach($addons as $key => $addon){
		appUpdateMsg('Checking download for '.$addon['slug'].'...');
		$downloadCheckLink = getOption('serviceURL').'download.php?appInstallHash='.APP_INSTALL_HASH.'&installedHash='.$installedHash.'&appVersion='.APP_VERSION.'&type=addon&downloadType=install&download='.$addon['slug'].'&downloadVersion='.$addon['version'].'&downloadToken='.$GLOBALS['downloadToken'];		
		if($currentGeneralSettings['participateBeta'] == 1){
			$downloadCheckLink .= '&participateBeta=1';
		}
				
		$temp = doCall($downloadCheckLink, '', $timeout=30);
		list($result, , , $curlInfo) = $temp;
		
		//$result = base64_decode($result);
		//$result = @unserialize($result);
		$result = getServiceResponseToArray($result);
		
		$cURLErrors = new cURLErrors($curlInfo);
		if($cURLErrors->isOk()){
			if($result['status'] == 'success'){
				$addons[$key]['downloadLink'] = $result['downloadLink'];//$downloadCheckLink.'&checked=true';
			}
			elseif($result['status'] == 'error'){
				unset($addons[$key]);
				appUpdateMsg('Error while downloading addon '.$addon['slug'].': '.$result['errorMsg']);
			}
		}else{
			unset($addons[$key]);
			appUpdateMsg('Error while downloading addon '.$addon['slug'].': Unable to communicate with server(' . $cURLErrors->getErrorMsg() .').');
		}
			
		
		
	}
	downloadAndUnzipAddons($addons);
	installAddons($addons);
	appUpdateMsg('<br>Please <a href="'.APP_URL.'" target="_top">click here</a> to reload the app.'); 
	updateOption('updateLastCheck', 0);//to trigger checkUpdate in next page load
	//$_SESSION['addonAlertCount'] = 0;
	//manageCookies::cookieSet('addonAlertCount',0,array('expire'=>0));
        updateOption('addonAlertCount',  '0');
}


function updateAddons($addons){
	$installedHash = getInstalledHash();
	$currentGeneralSettings = Reg::get('settings');
	foreach($addons as $key => $addon){
		appUpdateMsg('Checking download for '.$addon['slug'].'...');
		$downloadCheckLink = getOption('serviceURL').'download.php?appInstallHash='.APP_INSTALL_HASH.'&installedHash='.$installedHash.'&appVersion='.APP_VERSION.'&type=addon&downloadType=update&download='.$addon['slug'].'&downloadVersion='.$addon['version'];
		if($currentGeneralSettings['participateBeta'] == 1){
			$downloadCheckLink .= '&participateBeta=1';
		}
		
		$temp = doCall($downloadCheckLink, '', $timeout=30);
		list($result, , , $curlInfo) = $temp;
		//$result = base64_decode($result);
		//$result = @unserialize($result);
		$result = getServiceResponseToArray($result);

		$cURLErrors = new cURLErrors($curlInfo);
		if($cURLErrors->isOk()){
			if($result['status'] == 'success'){
				$addons[$key]['downloadLink'] = $result['downloadLink'];//$downloadCheckLink.'&checked=true';
			}
			elseif($result['status'] == 'error'){
				unset($addons[$addon['slug']]);
				appUpdateMsg('Error while downloading addon '.$addon['slug'].': '.$result['errorMsg']);
			}
		}
		else{
			unset($addons[$addon['slug']]);
			appUpdateMsg('Error while downloading addon '.$addon['slug'].': Unable to communicate with server(' . $cURLErrors->getErrorMsg() .').');
		}		
	}
	downloadAndUnzipAddons($addons);
	
	foreach($addons as $addon){
		if($addon['process']['unzipDone']){			
			//$addon['process']['updated'] = true;
			include(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php');
			$where = array(
			      		'query' =>  "slug = ':slug'",
			      		'params' => array(
			               ':slug'=>$addon['slug']
           				)
        			);
			$prevVer = DB::getField("?:addons", "updateCurrentVersion", $where);

			if(method_exists('addon'.ucfirst($addon['slug']),'update')){
				call_user_func(array('addon'.ucfirst($addon['slug']),'update'));
			}
			$newAddonVersion = getAddonVersion($addon['slug']);
			
			$optionVer['action'] = 'updated';
			$optionVer['actionTime'] = time();
			$optionVer['prevVer'] = $prevVer;
			$optionVer['newVer'] = $newAddonVersion;
			$optionVer['slug'] = $addon['slug'];
			manipulateOption('versionLogs', $optionVer);
			
			DB::update("?:addons", array('updateCurrentVersion' => $newAddonVersion), $where);//updating in database that addon is updated
			
			//remove updateAvailable for this addon so that it will stop false update notification in next browser app load
			$updateAddonsAvailable = @unserialize(getOption('updateAddonsAvailable'));
			if(isset($updateAddonsAvailable[ $addon['slug'] ]['updateAvailable']) && !empty($updateAddonsAvailable[ $addon['slug'] ]['updateAvailable'])){
				unset($updateAddonsAvailable[ $addon['slug'] ]['updateAvailable']);
				updateOption('updateAddonsAvailable', serialize($updateAddonsAvailable));
			}
			
			appUpdateMsg('Addon '.$addon['slug'].' successfully updated.');		
		}
	}
	appUpdateMsg('<br>Please <a href="'.APP_URL.'" target="_top">click here</a> to reload the app.');
	updateOption('updateLastCheck', 0);//to trigger checkUpdate in next page load 	
	//$_SESSION['addonAlertCount'] = 0;
	//manageCookies::cookieSet('addonAlertCount',0,array('expire'=>0));
        updateOption('addonAlertCount',  '0');
}

function downloadAndUnzipAddons(&$addons){
	foreach($addons as $key => $addon){
		if(!empty($addon['downloadLink'])/* && !empty($updateDetails['fileMD5'])*/){
		
			$downloadLink = $addon['downloadLink'];
			//$fileMD5 = $updateDetails['fileMD5'];
		
			$zip = getTempName('addon_'.$addon['slug'].'.zip');
			
			
			appUpdateMsg('Downloading '.$addon['slug'].' package...');
			$isDone = downloadURL($downloadLink, $zip);			
			
			if(!$isDone){ //download failed
				appUpdateMsg($addon['slug'].' package download failed.', true);
				continue;
			}
			
			if(!initFileSystem()){
				appUpdateMsg('Unable to initiate file system.', true);
				return false;
			}
			
			$unPackResult = unPackToWorkingDir($zip);
			if(!empty($unPackResult) && is_array($unPackResult)){
				$source = $unPackResult['source'];
				$remoteSource = $unPackResult['remoteSource'];
			}
			else{
				return false;	
			}
			
			$destination = APP_ROOT;
			if(!copyToRequiredDir($source, $remoteSource, $destination)){
				return false;	
			}
			

			appUpdateMsg('Unzipped '.$addon['slug'].' package.');
			$addons[$key]['process']['unzipDone'] = true;

			@unlink($zip);
					
				
		}
	}
	//return $addons;
}

function installAddons($addons){//install and activate the addon

	foreach($addons as $addon){
		if(empty($addon['process']['unzipDone'])){
			continue;	
		}
		$ok=false;
		appUpdateMsg('Installing '.$addon['slug'].' addon...');
		$where = array(
			      		'query' =>  "slug = ':slug'",
			      		'params' => array(
			               ':slug'=>$addon['slug']
           				)
        			);
		$isExist = DB::getField("?:addons", "slug", $where);
		if($isExist){
			appUpdateMsg('The '.$addon['slug'].' addon is already installed.');
			continue;
		}		
		
		if(file_exists(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php')){
			//activating the addon
			$isDone = DB::insert("?:addons", array('slug' => $addon['slug'], 'addon' => $addon['addon'], 'status' => 'active', 'validityExpires' => $addon['validityExpires'], 'initialVersion' => $addon['version'], 'updateCurrentVersion' => $addon['version']));
			if($isDone){
				$ok=true;
				include_once(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php');
				if(method_exists('addon'.ucfirst($addon['slug']), 'install')){

					$isSuccess = call_user_func(array('addon'.ucfirst($addon['slug']),'install'));
					if(!$isSuccess){
						$ok=false;
						$where = array(
				          'query' => "slug = ':slug'",
				          'params' => array(
				               ':slug'=>$addon['slug']
				           )
				        );
						DB::delete("?:addons", $where);
						appUpdateMsg('An error occurred while installing the '.$addon['slug'].' addon.');
					}
				}
				if($ok){
					appUpdateMsg($addon['slug'].' addon successfully installed.');
					
					//remove addon install available for this addon so that it will stop false notification in next browser app load
					//$newAddonsAvailableTemp = @unserialize(getOption('newAddonsAvailable'));
					//if(isset($newAddonsAvailableTemp[ $addon['slug'] ])){
					//	unset($newAddonsAvailableTemp[ $addon['slug'] ]);
					//	updateOption('newAddonsAvailable', serialize($newAddonsAvailableTemp));
					//}//commented because new installed getting deleted in next run
				}
			}
		}
		else{
			appUpdateMsg('A file was found missing while installing the '.$addon['slug'].' addon.');
		}
					
		$optionVer['action'] = 'installed';
		$optionVer['actionTime'] = time();
		$optionVer['prevVer'] = '0';
		$optionVer['newVer'] = $addon['version'];
		$optionVer['slug'] = $addon['slug'];
		manipulateOption('versionLogs', $optionVer);
		
		
	}
	//force update to make new addons to show as installed
	//checkUpdate(true, true);//commented because this deleting old addons

}

function activateAddons($addons){
	$success = array();
	foreach($addons as $key => &$addon){
		$where = array(
			      		'query' =>  "slug = ':slug'",
			      		'params' => array(
			               ':slug'=>$addon['slug']
           				)
        			);
		$isExist = DB::getField("?:addons", "slug", $where);
		if(!$isExist){
			addNotification($type='E', $title='Addon activation failed', $message='The '.$addon['slug'].' addon does not exist in the database.', $state='U', $callbackOnClose='', $callbackReference='');
			$addons[$key]['activate'] = false;
			continue;
		}		
		
		if(file_exists(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php')){
			//activating the addon
			$isDone = DB::update("?:addons", array( 'status' => 'active'), $where);
			if($isDone){
				$addons[$key]['activate'] = true;
				$success[$addon['slug']] = DB::getField("?:addons", "addon", $where);
				include_once(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php');
				if(method_exists('addon'.ucfirst($addon['slug']),'activate')){
					call_user_func(array('addon'.ucfirst($addon['slug']),'activate'));
				}
			}
		}
		else{
			$addons[$key]['activate'] = false;
			addNotification($type='E', $title='Addon activation failed', $message='A file was found missing while activating the '.$addon['slug'].' addon.', $state='U', $callbackOnClose='', $callbackReference='');
		}
	}
	if(!empty($success)){
		addNotification($type='N', $title='Addon has been activated', $message=implode('<br>', $success), $state='U', $callbackOnClose='', $callbackReference='');
	}
	return $addons;
}

function deactivateAddons($addons){
	foreach($addons as $key => $addon){
		$where = array(
			      		'query' =>  "slug = ':slug'",
			      		'params' => array(
			               ':slug'=>$addon['slug']
           				)
        			);
		$isExist = DB::getField("?:addons", "slug", $where);
		if(!$isExist){
			$addons[$key]['deactivate'] = false;
			addNotification($type='E', $title='Addon deactivation failed', $message='The '.$addon['slug'].' addon does not exist in the database.', $state='U', $callbackOnClose='', $callbackReference='');
			continue;
		}		
		
		if(file_exists(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php')){
			//activating the addon
			$isDone = DB::update("?:addons", array( 'status' => 'inactive'), $where);
			if($isDone){
				$addons[$key]['deactivate'] = true;
				$success[$addon['slug']] = DB::getField("?:addons", "addon", $where);
				include_once(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php');
				if(method_exists('addon'.ucfirst($addon['slug']),'deactivate')){
					call_user_func(array('addon'.ucfirst($addon['slug']),'deactivate'));
				}
			}
		}
		else{
			$addons[$key]['deactivate'] = false;
			addNotification($type='E', $title='Addon deactivation failed', $message='A file was found missing while deactivating the '.$addon['slug'].' addon.', $state='U', $callbackOnClose='', $callbackReference='');
		}
	}
	if(!empty($success)){
		addNotification($type='N', $title='Addon has been deactivated', $message=implode('<br>', $success), $state='U', $callbackOnClose='', $callbackReference='');
	}
	return $addons;
}

function uninstallAddons($addons){
	foreach($addons as $addon){
		
		if(file_exists(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php')){
			
			if($isDone){
				include_once(APP_ROOT.'/addons/'.$addon['slug'].'/addon.'.$addon['slug'].'.php');
				if(method_exists('addon'.ucfirst($addon['slug']),'uninstall')){
					call_user_func(array('addon'.ucfirst($addon['slug']),'uninstall'));
				}
				
			}
		}
		
		if(is_object($GLOBALS['FileSystemObj']) || uninstallAddonsInitFileSystem()){
			$GLOBALS['FileSystemObj']->delete(APP_ROOT.'/addons/'.$addon['slug'], true);
		}
		$where = array(
			      		'query' =>  "slug = ':slug'",
			      		'params' => array(
			               ':slug'=>$addon['slug']
           				)
        			);		
		$isDone = DB::delete("?:addons", $where);
		addNotification($type='N', $title='Addon uninstalled', $message='The '.$addon['slug'].' addon has been uninstalled.', $state='U', $callbackOnClose='', $callbackReference='');	
	}
}

function uninstallAddonsInitFileSystem(){
	ob_start();
	$r = initFileSystem();
	ob_end_clean();
	return $r;
}

function getAddonsHTMLHead(){
	$createAddonCache = new createAddonCache();
	$head =  $createAddonCache->initiateCacheProcess();
	return $head;
}

function getInstalledHash(){
	return sha1(rtrim(APP_DOMAIN_PATH,"/")."/".'|--|'.APP_ROOT);
}


function updateAppRegistered($user){//$user => username or email registered with IWP Site
	updateOption('appRegisteredUser', $user);
}

function isAppRegistered(){
	$appRegisteredUser = getOption('appRegisteredUser');
	if(!empty($appRegisteredUser)){
		return true;
	}
	return false;
}

function processCheckAddonsUpdate($addonsUpdate){
	//get updates
	updateOption('updateAddonsAvailable', serialize($addonsUpdate['updateAddons']));
	updateOption('newAddonsAvailable', serialize($addonsUpdate['newAddons']));
	updateOption('promoAddons', serialize($addonsUpdate['promoAddons']));
	updateOption('suiteDetails', serialize($addonsUpdate['suiteDetails']));
	
	addRenewalAlertNotification($addonsUpdate);
}

function addRenewalAlertNotification($addonsUpdate){
	$userHelp = panelRequestManager::getUserHelp();
	$newUserHelp = $newUserHelpEtc = array();
	if(!empty($addonsUpdate['updateAddons'])){//25,6,1
		foreach($addonsUpdate['updateAddons'] as $addonSlug => $addonDetails){
			if( $addonDetails['validityExpires'] > 0 && ($addonDetails['validityExpires'] - (86400 * 30)) < time() ){//about to expire in less then 30 days or already expired
				if(!empty($addonDetails['isValidityExpired'])){
					if(empty($userHelp['renewal_alert_0d_'.$addonDetails['validityExpires']])){
						$newUserHelp['renewal_alert_0d_'.$addonDetails['validityExpires']] = true;
						$newUserHelpEtc['renewal_alert_0d_'.$addonDetails['validityExpires']]['names'][] = $addonDetails['addon'];
					}	
				}
				else if( ($addonDetails['validityExpires'] - (86400 * 1)) > time() && ($addonDetails['validityExpires'] - (86400 * 2)) < time() ){
					if(empty($userHelp['renewal_alert_1d_'.$addonDetails['validityExpires']])){
						$newUserHelp['renewal_alert_1d_'.$addonDetails['validityExpires']] = true;
						$newUserHelpEtc['renewal_alert_1d_'.$addonDetails['validityExpires']]['names'][] = $addonDetails['addon'];
					}				
				}
				else if( ($addonDetails['validityExpires'] - (86400 * 2)) > time() && ($addonDetails['validityExpires'] - (86400 * 6)) < time() ){
					if(empty($userHelp['renewal_alert_6d_'.$addonDetails['validityExpires']])){
						$newUserHelp['renewal_alert_6d_'.$addonDetails['validityExpires']] = true;
						$newUserHelpEtc['renewal_alert_6d_'.$addonDetails['validityExpires']]['names'][] = $addonDetails['addon'];
					}				
				}
				else if( ($addonDetails['validityExpires'] - (86400 * 10)) > time() && ($addonDetails['validityExpires'] - (86400 * 25)) < time() ){
					if(empty($userHelp['renewal_alert_25d_'.$addonDetails['validityExpires']])){
						$newUserHelp['renewal_alert_25d_'.$addonDetails['validityExpires']] = true;
						$newUserHelpEtc['renewal_alert_25d_'.$addonDetails['validityExpires']]['names'][] = $addonDetails['addon'];
					}				
				}				
				
			}
		}
		
		panelRequestManager::updateUserHelp($newUserHelp);
		if(!empty($newUserHelp)){
			foreach($newUserHelp as $key => $value){
				$isExpired = false;
				$thisSingPlurMsg = array('AN ADDON IS EXPIRING', 'The following addon is about to expire. Please renew it immediately.');
				if(strpos($key, "renewal_alert_0d_") !== false){
					$isExpired = true;
					$thisSingPlurMsg = array('AN ADDON HAS EXPIRED', 'The following addon has expired. Please renew it immediately to get continued updates and support.');
				}
				if(count($newUserHelpEtc[$key]['names']) > 1){
					$thisSingPlurMsg[0] = ($isExpired) ? 'A FEW ADDONS HAVE EXPIRED' : 'A FEW ADDONS ARE EXPIRING';
					$thisSingPlurMsg[1] = ($isExpired) ? 'The following addons have expired. Please renew them immediately to get continued updates and support.' : 'The following addons are about to expire. Please renew them immediately.';
				}
				addNotification($type='E', $title=$thisSingPlurMsg[0], $thisSingPlurMsg[1].'<br>'.implode('<br>', $newUserHelpEtc[$key]['names']).'<br><a style="display:none" class="closeRenewalNotification" notifingitem="'.$key.'">Dismiss</a> <a href="'.IWP_SITE_URL.'my-account/" target="_blank" >Renew Now</a>', $state='U');
			}
		}
		
	}
}

function getNewAddonsAvailable(){
	return @unserialize(getOption('newAddonsAvailable'));
}

function getPromoAddons(){
	return @unserialize(getOption('promoAddons'));
}


function initMenus(){
	
	$menus['manage'] 	= array();
	$menus['protect'] 	= array();
	$menus['monitor'] 	= array();
	$menus['maintain'] 	= array();
	$menus['tools'] 	= array();
	
	$menus['manage']['displayName'] 	= 'Manage';
	$menus['manage']['subMenus'][] 		= array('page' => 'updates', 'displayName' => '<span class="float-left">Updates</span><span class="update_count float-left droid700" id="totalUpdateCount">0</span><div class="clear-both"></div>');
	$menus['manage']['subMenus'][] 		= array('page' => 'items', 'displayName' => 'Plugins &amp; Themes');
	
	$menus['protect']['displayName'] 	= 'Protect';
	$menus['protect']['subMenus'][] 	= array('page' => 'backups', 'displayName' => 'Backups');
	
	$menus['monitor']['displayName'] 	= 'Monitor';	
	$menus['maintain']['displayName'] 	= 'Maintain';	
	$menus['tools']['displayName'] 		= 'Tools';
	
	setHook('addonMenus', $menus);
	
	$hooks = Reg::get('hooks');
	
	Reg::set('menus', $menus);
}

function printMenus(){
	$menus = Reg::get('menus');
	$tempAddons = array();
	$tempAddons['displayName']	= 'Addons';
	$tempAddons['subMenus']		= array();
	
	foreach($menus as $key => $group){	
		if(isset($menus[$key]) && !empty($menus[$key]['subMenus']) && is_array($menus[$key]['subMenus'])){	
			$groupName = $menus[$key]['displayName'];
			$singleGroupMenu = $menus[$key]['subMenus'];
			$HTML = TPL::get('/templates/menu/menu.tpl.php', array('groupName' => $groupName, 'singleGroupMenu' => $singleGroupMenu));
			echo $HTML;
		}
		/*elseif(isset($group['displayName'])){
			
		}*/
		elseif(is_string($group)){
			$tempAddons['subMenus'][] = $group;
		}
	}
	
	if(!empty($tempAddons['subMenus'])){
		
		$groupName = $tempAddons['displayName'];
		$singleGroupMenu = $tempAddons['subMenus'];
		$HTML = TPL::get('/templates/menu/menu.tpl.php', array('groupName' => $groupName, 'singleGroupMenu' => $singleGroupMenu));
		echo $HTML;
		
	}
}

function getAddonMenus(){
	$menus = array();
	return implode("\n", $menus);
}


function repositoryTestConnection($account_info){
	if(isset($account_info['iwp_ftp']) && !empty($account_info)) {
		$return = FTPTestConnection($account_info['iwp_ftp']);
	}
	  
	if(isset($account_info['iwp_amazon_s3']) && !empty($account_info['iwp_amazon_s3'])) {
		if(phpversion() >= '5.3.3'){
			require_once(APP_ROOT . '/lib/amazon/s3IWPTestConnection.php');
			$return = repositoryAmazons3($account_info['iwp_amazon_s3']);
		}
		else{
			$return = repositoryAmazons3BwdComp($account_info['iwp_amazon_s3']);
		}
	}
	  
	if (isset($account_info['iwp_dropbox']) && !empty($account_info['iwp_dropbox'])) {
	  $return = repositoryDropbox($account_info['iwp_dropbox']);
	}	
	  
	if (isset($account_info['iwp_gdrive']) && !empty($account_info['iwp_gdrive'])) {
	  $return = repositoryGDrive($account_info['iwp_gdrive']);
	}
	return $return;	
}
	
function FTPTestConnection($args){
	extract($args);	
	$ftp_hostname = $ftp_hostname ? $ftp_hostname : $hostName;
	$ftp_username = $ftp_username ? $ftp_username : $hostUserName;
	$ftp_password = $ftp_password ? $ftp_password : $hostPassword;
	$FTPBase = $ftp_remote_folder ? $ftp_remote_folder : $FTPBase;
	
	if(empty($ftp_hostname)){
		return array('status' => 'error',
			'errorMsg' => 'Inavlid FTP host',
			);	
	}

    if(isset($use_sftp) && $use_sftp == 1) {
    	return SFTPTestConnection($args);
    }
	else{
		return simpleFTPTestConnection($args);
	}

}
function simpleFTPTestConnection($args){
	extract($args);	
	$ftp_hostname = $ftp_hostname ? $ftp_hostname : $hostName;
	$ftp_username = $ftp_username ? $ftp_username : $hostUserName;
	$ftp_password = $ftp_password ? $ftp_password : $hostPassword;
	$FTPBase = $ftp_remote_folder ? $ftp_remote_folder : $FTPBase;
	$ftp_passive = $ftp_passive ? $ftp_passive : $use_pasv;
	$port = $ftp_port ? $ftp_port : 21; //default port is 21
	
	if ($ftp_ssl) {
		if (function_exists('ftp_ssl_connect')) {
			$conn_id = ftp_ssl_connect($ftp_hostname, $port);
			if ($conn_id === false) {
				return array('status' => 'error',
						'errorMsg' => 'Failed to connect to ' . $ftp_hostname,
				);
			}
		}
		else {
			return array('status' => 'error',
			'errorMsg' => 'Your server doesn\'t support FTP SSL',
			);
		}
	} else {
		if (function_exists('ftp_connect')) {
			$conn_id = ftp_connect($ftp_hostname, $port);
			if ($conn_id === false) {
				return array('status' => 'error',
				'errorMsg' => 'Failed to connect to ' . $ftp_hostname,
				);
			}
		}
		else {
			return array('status' => 'error',
			'errorMsg' => 'Your server doesn\'t support FTP',
			);
		}
	}
	$login = @ftp_login($conn_id, $ftp_username, $ftp_password);
	if ($login === false) {
		return array('status' => 'error', 
		'errorMsg' => 'FTP login failed for ' . $ftp_username . ', ' . $ftp_password,
		);
	}
	else{
		if(!empty($ftp_passive)){
			@ftp_pasv($conn_id, true);
		}
		if(empty($FTPBase)){
			return array('status' => 'error', 
				'errorMsg' => 'Invalid FTP base path..',
			);
		}
		$testFile = '/__testFTP2'.time().'.php';
		$uploadFilePath = getTempName($testFile); 
		$FTPPathFile = rtrim($FTPBase, '/').$testFile;
		$uploadTestFile = ftp_put($conn_id, $FTPPathFile, $uploadFilePath, FTP_BINARY);
		$appPathFile = APP_ROOT.$testFile;
		@unlink($uploadFilePath);
		if ($uploadTestFile === false) {
			return array('status' => 'error', 
				'errorMsg' => 'FTP upload failed.',
			);
		}
		$isTestFileExits = file_exists($appPathFile);
		ftp_delete($conn_id, $FTPPathFile);
		if (!$isTestFileExits && $basePathValidation == true) {
			return array('status' => 'error', 
				'errorMsg' => 'Invalid FTP base path..',
			);
		}
		else{
			return array('status' => 'success');
		}
		
		
	}	
}
function SFTPTestConnection($args){
	extract($args);	

	$ftp_hostname = $ftp_hostname ? $ftp_hostname : $hostName;
	$ftp_username = $ftp_username ? $ftp_username : $hostUserName;
	$ftp_password = $ftp_password ? $ftp_password : $hostPassword;
	$FTPBase = $ftp_remote_folder ? $ftp_remote_folder : $FTPBase;
	$port = $ftp_port ? $ftp_port : 22;
	$path = APP_ROOT.'/lib/phpseclib';
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include_once('Net/SFTP.php');

    $sftp = new Net_SFTP($ftp_hostname, $port);
    if(!$sftp) {
        return array('status' => 'error',
                            'errorMsg' => 'Failed to connect to ' . $ftp_hostname,
            );
    }
    if (!$sftp->login($ftp_username, $ftp_password)) {
        return array('status' => 'error', 
			'errorMsg' => 'FTP login failed for ' . $ftp_username . ', ' . $ftp_password,
		);
    }
	else{
		if(empty($FTPBase)){
			return array('status' => 'error', 
				'errorMsg' => 'Invalid FTP base path..',
			);
		}
		if(!$sftp->chdir(trim($FTPBase, '/'))){
			return array('status' => 'error', 
				'errorMsg' => 'FTP upload failed.',
			);	
		}
		$testFile = '/__testFTP2'.time().'.php';
		$uploadFilePath = getTempName($testFile); 
		$uploadTestFile = $sftp->put(basename($testFile),$uploadFilePath,NET_SFTP_LOCAL_FILE);
		@unlink($uploadFilePath);
		$appPathFile = APP_ROOT.$testFile;
		if ($uploadTestFile === false) {
			return array('status' => 'error', 
				'errorMsg' => 'FTP upload failed.',
			);
		}
		$isTestFileExits = file_exists($appPathFile);
		$sftp->delete(basename($testFile));
		if (!$isTestFileExits && $basePathValidation == true) {
			return array('status' => 'error', 
				'errorMsg' => 'Invalid FTP base path..',
			);
		}
		else{
			return array('status' => 'success');
		}
	}
}
function repositoryAmazons3BwdComp($args){
	require_once(APP_ROOT."/lib/s3.php");
	extract($args);
	
	if(!empty($as3_bucket_region)){
		$endpoint = 's3-' . $as3_bucket_region . '.amazonaws.com';
	}
	else{
		$endpoint = 's3.amazonaws.com';
	}
    $s3 = new S3(trim($as3_access_key), trim(str_replace(' ', '+', $as3_secure_key)), false, $endpoint);
	
	try{
		$s3->getBucket($as3_bucket, S3::ACL_PUBLIC_READ);
		return array('status' => 'success');
	}
	catch (Exception $e){
         return array('status' => 'error', 'errorMsg' => $e->getMessage());
		 //$e->getMessage();
	}
}

function repositoryDropbox($args){
	extract($args);

	if(isset($consumer_secret) && !empty($consumer_secret)){
		require_once(APP_ROOT.'/lib/dropbox.oauth.php');
		$dropbox = new Dropbox($consumer_key, $consumer_secret);				
		$dropbox->setOAuthToken($oauth_token);
		$dropbox->setOAuthTokenSecret($oauth_token_secret);
		try{
			$dropbox->accountinfo();
			return array('status' => 'success');			
		}
		catch(Exception $e){
			return array('status' => 'error', 'errorMsg' => $e->getMessage());
		}
	}
	return array('status' => 'error', 'errorMsg' => 'Consumer Secret not available');
}

function repositoryGDrive($args)
{
	if(!empty($args['gDriveEmail']))
	{
		//return googleDriveHelper::testConnectionGoogle($args);
		include_once(APP_ROOT.'/lib/googleAPIs/src/Google_Client.php');
		include_once(APP_ROOT.'/lib/googleAPIs/src/contrib/Google_DriveService.php');
		include_once(APP_ROOT.'/lib/googleAPIs/src/contrib/Google_Oauth2Service.php');
		include_once(APP_ROOT.'/lib/googleAPIs/storage.php');
		include_once(APP_ROOT.'/lib/googleAPIs/authHelper.php');
		
		if(function_exists('backupRepositorySetGoogleDriveArgs')){
			$gDriveArgs = backupRepositorySetGoogleDriveArgs($args);
		}else{
			addNotification($type='E', $title='Cloud backup Addon Missing', $message="Check if cloud backup addon exists and is active", $state='U', $callbackOnClose='', $callbackReference='');
			return array('status' => 'error', 'errorMsg' => 'Cloud backup addon missing');
		}
		if(!empty($gDriveArgs) && !empty($gDriveArgs['clientID']) && !empty($gDriveArgs['clientSecretKey']) )
		{		
			$accessToken = $gDriveArgs['token'];
			
			$client = new Google_Client();
			$client->setClientId($gDriveArgs['clientID']);

			$client->setClientSecret($gDriveArgs['clientSecretKey']);
			$client->setRedirectUri($gDriveArgs['redirectURL']);
			$client->setScopes(array(
			  'https://www.googleapis.com/auth/drive',
			  'https://www.googleapis.com/auth/userinfo.email'));
			
			$accessToken = $gDriveArgs['token'];
			$refreshToken = $accessToken['refresh_token'];
			
			try
			{
				$client->refreshToken($refreshToken);
				return array('status' => 'success');
			}
			catch(Exception $e)
			{	
				echo 'google Error ',  $e->getMessage(), "\n";
				return array('status' => 'error', 'errorMsg' => $e->getMessage());
			}
		}
		return array('status' => 'error', 'errorMsg' => 'API key not available.');
	}
	return array('status' => 'error', 'errorMsg' => 'Repository ID not available.');
}

function getAddonHeadJS(){
	$headJS = array();
	setHook('addonHeadJS', $headJS);	
	return implode("\n", $headJS);
}

function cronNotRunAlert(){
	$lastNotify = getoption('lastCronNotRunAlert');
	$retryTime = 60 * 60 * 24 * 7; // send notification again after one week
	$lastNotify += $retryTime;
	if ($lastNotify >= time()) {
		return false;
	}

	if(manageEasyCron::isActive() || Manage_IWP_Cron::isActive()){
		return;
	}	
	
	//cron job should run every 20 mins
	$cronLastRun = getOption('cronLastRun');
	
	if( $cronLastRun > (time() - (40 * 60)) ){//checking for 40 mins instead of 20 mins here
		return;	
	}	
	
	$requiredFor = array();
	
	$settings = panelRequestManager::getSettings();
	$updatesNotificationMail = $settings['notifications']['updatesNotificationMail'];

	//check setting
	if(!($updatesNotificationMail['frequency'] == 'never' || (empty($updatesNotificationMail['coreUpdates']) && empty($updatesNotificationMail['pluginUpdates']) && empty($updatesNotificationMail['themeUpdates']))) ){
		$requiredFor[] = 'Email Update Notification';
	}
	
	setHook('cronRequiredAlert', $requiredFor);
	
	if(!empty($requiredFor)){
		addNotification($type='E', $title='CRON JOB IS NOT RUNNING', $message='Please set the cron job to run every 20 minutes.<div class="droid700" style="white-space: pre; word-wrap: break-word;">'.APP_PHP_CRON_CMD.APP_ROOT.'/cron.php &gt;/dev/null 2&gt;&1</div><br>It is required for the following -<br>'.@implode('<br>', $requiredFor), $state='U', $callbackOnClose='', $callbackReference='');
		updateOption('lastCronNotRunAlert', time());
	}	
}

function onBrowserLoad(){
    manageCookies::cookieUnset('slowDownAjaxCallFrom');
	installFolderAlert();
	cronNotRunAlert();	
}

function autoSelectConnectionMethod(){
	$settings = Reg::get('settings');
	if($settings['autoSelectConnectionMethod'] == 1){
		$result = fsockSameURLConnectCheck(APP_FULL_URL.'execute.php');
		if($result['status'] && $settings['executeUsingBrowser'] == 1){
			$settings['executeUsingBrowser'] = 0;
			DB::update("?:settings", array('general' => serialize($settings)), "1");
			addNotification($type='N', $title='FSOCK HAS BEEN ENABLED', $message='Your server supports fsock and has been enabled.', $state='U', $callbackOnClose='', $callbackReference='');
		}
		elseif($result['status'] == false && $result['errorNo'] != 'authentication_required' && $settings['executeUsingBrowser'] == 0){
			$settings['executeUsingBrowser'] = 1;
			DB::update("?:settings", array('general' => serialize($settings)), "1");
			addNotification($type='N', $title='FSOCK HAS BEEN DISABLED', $message='Your server doesn\'t support fsock. An alternative method is being used.', $state='U', $callbackOnClose='', $callbackReference='');
		}		
	}
}

function defineAppFullURL(){
	$appFullURL = APP_URL;
	$settings = Reg::get('settings');
	if(!empty($settings['httpAuth']['username'])){
		$appURLParts = parse_url($appFullURL);
		$appURLParts['user'] = urlencode($settings['httpAuth']['username']);
		$appURLParts['pass'] = urlencode($settings['httpAuth']['password']);
		$appFullURL = httpBuildURLCustom($appURLParts);
	}	
	define('APP_FULL_URL', $appFullURL);
}

function getFullWPURL($siteID, $URL){//this will add http auth if it set for the site
	if(is_array($URL))
	{
		$finalURL = array();
		$finalKey = '';
		foreach($URL as $key => $value)
		{
			$finalURL[$key] = $value;
			$siteData = getSiteData($siteID);
			if(!empty($siteData['httpAuth'])){
				$siteHttpAuth = @unserialize($siteData['httpAuth']);

				if(!empty($siteHttpAuth['username'])){
					$URLParts = parse_url($value);
					$URLParts['user'] = urlencode($siteHttpAuth['username']);
					$URLParts['pass'] = urlencode($siteHttpAuth['password']);
					$finalURL[$key] = httpBuildURLCustom($URLParts);
					$finalKey = $key;
				}
			}
		}
		return $finalURL[$key];
	}
	else
	{
		$finalURL = $URL;
		$siteData = getSiteData($siteID);
		if(!empty($siteData['httpAuth'])){
			$siteHttpAuth = @unserialize($siteData['httpAuth']);

			if(!empty($siteHttpAuth['username'])){
				$URLParts = parse_url($URL);
				$URLParts['user'] = urlencode($siteHttpAuth['username']);
				$URLParts['pass'] = urlencode($siteHttpAuth['password']);
				$finalURL = httpBuildURLCustom($URLParts);
			}
		}
		return $finalURL;
	}
}



function checkTriggerStatus(){
		
	$where = array(
			      		'query' =>  "recheck = 0 AND type IN('backup', 'scheduleBackup', 'staging', 'installClone') AND status = ':status'",
			      		'params' => array(
			               ':status'=>'multiCallWaiting'
           				)
        			);
	$data = DB::getArray("?:history", "siteID, historyID, type, action", $where);
		
	foreach($data as $key => $value){
		$where = array(
			      		'query' =>  "parentHistoryID = ':parentHistoryID' AND ((status = ':status' AND error IN('28', '52', '500', '502', '504', 'timeoutClear')) OR (status = 'error' AND error IN('main_plugin_connection_error'))) ORDER BY historyID DESC LIMIT 3",
			      		'params' => array(
			               ':parentHistoryID'=>$value['historyID'],
			               ':status'=>'netError'
           				)
        			);
		$subTaskData = DB::getArray("?:history", "historyID, status, recheck", $where);
		
		$errorCount = 0;
		foreach($subTaskData as $subTaskKey => $subTaskValue){//trying to find three consecutive error
			if($subTaskValue['status'] == 'completed'){
				$errorCount = 0;
			}else{
				$errorCount++;
			}
			$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			               ':historyID'=>$subTaskValue['historyID']
           				)
        			);
			if($errorCount == 3){
				$errorMsg = DB::getRow("?:history_additional_data", "error, errorMsg", $where);
				updateHistory(array('status' => 'error', 'error' => 'consecutiveError', 'recheck' => '1'), $value['historyID'], array('status' => 'error', 'error' => $errorMsg['error'], 'errorMsg' => $errorMsg['errorMsg']));
				break;
			}else{
				if($subTaskValue['recheck'] == 1){ continue; }
				
				$params['parentHistoryID'] = $value['historyID'];
				$actionID = uniqid('', true);
				Reg::set('currentRequest.actionID', $actionID);
				if (($value['type'] == 'staging' && $value['action'] == 'newSite') || ($value['type'] == 'installClone' && $value['action'] == 'newSite') ) {
					manageClientsInstallCloneCommon::triggerBridgeExtractMulticall($params, $value['siteID']);
				}else{
					manageClientsBackup::triggerRecheck($params, $value['siteID']);
				}
				DB::update("?:history", array('recheck' => 1), $where);
				break;
				//DB::update("?:history", array('recheck' => '1'), "historyID =".$value['historyID']);
			}
		}	
	}
}

function isTaskRunningBySiteID($siteID, $type = NULL, $historyID = NULL){
	if (!$type) {
		$where = array(
			'query' => "siteID = ':siteID' AND status NOT IN ('completed', 'netError', 'error') GROUP BY actionID ORDER BY historyID DESC",
			'params' => array(
				':siteID'	=> $siteID,
				)
			);
	} else if ($type == 'backup') {
		$where = array(
			'query'		=> "siteID = :siteID AND ((type = 'backup' AND (action = 'now' OR action = 'multiCallNow')) OR (type = 'scheduleBackup' AND (action = 'runTask' OR action ='multiCallRunTask'))) AND status NOT IN ('completed', 'netError', 'error') GROUP BY actionID ORDER BY historyID DESC",
			'params' 	=> array(
				   ':siteID' => $siteID,
				)
			);
	}
	return DB::getField("?:history", "count(historyID)", $where);
 }

function checkBackupTasks(){
	$maxTime = time() - (60*60); //60 mins before. 
	$data = DB::getArray("?:history", "siteID,historyID,microtimeInitiated", "microtimeInitiated >= ".DB::esc($maxTime)." AND recheck = 0 AND 
	(
		(type = 'backup' AND action = 'now') OR 
		(type = 'scheduleBackup' AND action = 'runTask') OR 
		(type = 'installClone' AND action = 'installCloneBackupNow')
	)
	AND 
	(
		(status = 'netError' AND error IN('28', '52', '500', '502', '504', 'timeoutClear')) OR
		(status = 'error' AND error IN('main_plugin_connection_error'))
	)
	"); // time and task == 'neterror', verifying check == 0 //28 => curl error: operation timeout, 52 => curl error: empty reply form server
	
	
	$checkTill = 35 * 60;//35 mins each 5 min interval
	if(!empty($data)){
		foreach($data as $key => $value){
			$siteIDs = array();
			
			$siteIDs[] = $value['siteID'];
			
			$balanceTime = $value['microtimeInitiated'] + $checkTill - time();
			
			$addTime = 0;
			do{								
				$actionID = uniqid('', true);
				Reg::set('currentRequest.actionID', $actionID);
				
				$params['timeScheduled'] = time() + $addTime;
				$params['status'] = 'scheduled';
				
				//$siteIDs = array($siteID);
				$extras  = array('sendAfterAllLoad' => false, 'doNotShowUser' => true);
				manageClientsFetch::getStatsProcessor($siteIDs, $params, $extras);
				$balanceTime -= 5*60;
				$addTime += 5*60;
			
			}
			while($balanceTime > 0);
			$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			               ':historyID'=>$value['historyID']
           				)
        			);
			DB::update("?:history", array('recheck' => '1'), $where);
			
		}
	}
	
	return;
}

function quote($str) {
	return sprintf('"%s"', $str);
}

function getRealSystemCronRunningFrequency(){
	$cronRecentRunLog = getOption('cronRecentRunLog');
	if(!empty($cronRecentRunLog)){
		$cronRecentRunLogs = unserialize($cronRecentRunLog);
		
		//filter last 90 mins log alone
		$lastNSecs = time() - (90*60);
		$temp = array();
		foreach($cronRecentRunLogs as $key => $value){
			if($value > $lastNSecs) array_push($temp, $value);
		}
		$cronRecentRunLogs = $temp;
		
		$cronRecentRunLogsCount = count($cronRecentRunLogs);
		if($cronRecentRunLogsCount >= 2){
			$tempArray = array();
			$cronRecentRunLogs = array_reverse($cronRecentRunLogs, true); //to take the diff from the last value.
			$lastValue = NULL;
			$i = 0;
			foreach($cronRecentRunLogs as $key => $value){
				if(!empty($lastValue)) $tempArray[] = $lastValue - $value;
				$lastValue = $value;
				$i++;
				if($i >= 5){
					break;
				}
			}
			
			$avgFrequency = array_sum($tempArray)/count($tempArray);
			$avgFrequency = floor($avgFrequency / 60);
			if($avgFrequency == 0) $avgFrequency = 1;
			
			return $avgFrequency;
		}
		elseif($cronRecentRunLogsCount == 1){
			return 20;
		}
		
	}
	return 0;
}

function getSystemCronRunningFrequency(){
	$freq = getRealSystemCronRunningFrequency();
	if($freq > 0 && $freq <= 6){//5 min system cron
		return 5;
	}elseif($freq > 6){
		return 20;
	}
	return 0;
}

/*
*
* use this functinon in cron.php, to identify the cron mode.
*
*/
function cronCheck(){
	
	if($_GET['type'] == 'manage'){//easyCron triggered
		define('CRON_MODE', 'easyCronManage');
		$getNextTaskScheduleTime = getNextTaskScheduleTime();
		if(!empty($getNextTaskScheduleTime) && $getNextTaskScheduleTime < (time() + 30*60)){//if manageCron trigger at 00:00. say a cron task scheduled at 00:10, it will be accepted by this if which checks less then 00:30, cronTask which will disable it self, it will keep triggering for every minutue even it doesnt have any task to execute
			$result = manageEasyCron::taskCronEnable();
			if($result['status'] == 'error'){
				 addNotification($type='E', $title='Easy Cron API Error', $result['error']['message'], $state='U');	
			}
		}
		die();
	}
	elseif($_GET['type'] == 'task'){//easyCron triggered
		define('CRON_MODE', 'easyCronTask');
		define('CRON_TIMEOUT', 30);
	}elseif($_GET['type'] === 'IWP_CRON'){
            $mode = $_GET['mode'];
            Manage_IWP_Cron::handleCronReq($mode);
        }else{
		$freq = getSystemCronRunningFrequency();
		if($freq == 5){//5 min system cron
			define('CRON_MODE', 'systemCronShortTime');
			define('CRON_TIMEOUT', 310);
		}else{//should be 20 min system cron
			define('CRON_MODE', 'systemCronDefault');
			define('CRON_TIMEOUT', 1210);
		}
	}
		
}


function getNextTaskScheduleTime(){
	$nextSchedule = array();
	
	$nextUpdateNotifyTime = updatesNotificationMailRunCheck(true);
	$nextSchedule[] = ($nextUpdateNotifyTime > 0 ? $nextUpdateNotifyTime : $nextSchedule);
	
	setHook('getNextSchedule', $nextSchedule);
	
	//Assuming all values in array are integers and time.
	if(!empty($nextSchedule)){
		return min($nextSchedule);
        }else{
		return false;	
        }
}

function isAnyActiveOrPendingJobs(){
	$where = array(
			      		'query' =>  "(H.status IN ('initiated', 'running', 'pending', 'multiCallWaiting')  OR (H.status = ':status' AND H.timescheduled <= ".time()." AND H.timescheduled > 0))",
			      		'params' => array(
			               ':status'=>'scheduled'
           				)
        			);
	 return DB::getExists("?:history H", "H.historyID", $where);
}

function needFreshCall($secondsPassed=3){
	if(defined('CRON_MODE') && CRON_MODE == 'easyCronTask'){
		if( (microtime(true) - $GLOBALS['cronStartTime']) > $secondsPassed ){
			exit();
		}
	}
}

function showBrowserCloseWarning(){
	$where = array(
		'query' =>  "(H.status = ':status' AND H.action = 'installCloneBackupNow' AND H.type = 'staging') OR (H.status = ':status' AND H.action = 'installCloneBackupNow' AND H.type = 'installClone') OR (H.status = ':status')",
		'params' => array(
			':status'=>'multiCallWaiting'
		)
	);
	$MultiCallJobActiveType = DB::getField("?:history H", "H.type", $where);
	if($MultiCallJobActiveType == 'staging'){
		return 'staging';
	} else if($MultiCallJobActiveType == 'installClone'){
		return 'installClone';
	} else if($MultiCallJobActiveType == 'backup'){
		if(!manageEasyCron::isActive()){
			return 'backup';
		}
	}

	return false;
}

function setMultiCallOptions(&$requestParams)
{
	//set the multicall options from config.php if available else set the default values
	
	if(!defined('MULTICALL_ZIP_SPLIT_SIZE'))
	{
		$requestParams['args']['zip_split_size'] = 1800;	//MB
	}
	else
	{
		$requestParams['args']['zip_split_size'] = MULTICALL_ZIP_SPLIT_SIZE;
	}
	if(!defined('MULTICALL_FILE_BLOCK_SIZE'))
	{
		$requestParams['args']['file_block_size'] = 5;
	}
	else
	{
		$requestParams['args']['file_block_size'] = MULTICALL_FILE_BLOCK_SIZE;
	}
	if(!defined('MULTICALL_LOOP_BREAK_TIME'))
	{
		$requestParams['args']['file_loop_break_time'] = 23;
		$requestParams['args']['db_loop_break_time'] = 23;
		if(!empty($requestParams['secure']['account_info']))
		{
			$requestParams['secure']['account_info']['upload_loop_break_time'] = 23;
		}
	}
	else
	{
		$requestParams['args']['file_loop_break_time'] = MULTICALL_LOOP_BREAK_TIME;
		$requestParams['args']['db_loop_break_time'] = MULTICALL_LOOP_BREAK_TIME;
		if(!empty($requestParams['secure']['account_info']))
		{
			$requestParams['secure']['account_info']['upload_loop_break_time'] = MULTICALL_LOOP_BREAK_TIME;
		}
	}
	if(!empty($requestParams['secure']['account_info']))
	{
		if(!defined('MULTICALL_UPLOAD_BLOCK_SIZE'))
		{
			$requestParams['secure']['account_info']['upload_file_block_size'] = 5;
		}
		else
		{
			$requestParams['secure']['account_info']['upload_file_block_size'] = MULTICALL_UPLOAD_BLOCK_SIZE;
		}
	}
	
}


function dispatchMark($data){
	$notif_count = 0;
	$notif_count_offer = 0;
	//refresh the notify count 
	if(!getOption("notifyCenterRead")){
		$notifyCenterRead = array();
	}
	else{
		$notifyCenterRead = unserialize(getOption("notifyCenterRead"));
	}
	
	if(isset($data['notifyCenter'])){
		updateOption("markNotifyCenter", serialize($data['notifyCenter']));
		$notif_count = count($data['notifyCenter']);
		foreach($data['notifyCenter'] as $ID => $notifyDetail){
			if(array_key_exists($ID, $notifyCenterRead)){
				$notif_count -= 1;
			}
		}
		
	}
	
	if(isset($data['notifyOffer']) && !isset($data['notifyOffer']['error'])){
		updateOption("markNotifyOffer", serialize($data['notifyOffer']));
		$notif_count_offer = count($data['notifyOffer']);
		if(!empty($notif_count_offer)){
			$notif_count_offer = 1;
		}
	}
	else{
		updateOption("markNotifyOffer", '');			//clearing old notification offer
	}
	
	if(isset($data['bottomLines'])){
		updateOption("markBottomLines", jsonEncoder($data['bottomLines']));
	}
	else{
		updateOption("markBottomLines", '');
	}
	
	//update the count
	updateOption("notifCount", $notif_count + $notif_count_offer);

}

function processQueueTweak(&$data, $category, $type){
	if ($category === 'staging' && $type === 'content') {
		if($data['name'] === "newSite") {
			$data['action']= 'newSite';
		}else if ($data['detailedAction'] === 'plugin' || $data['detailedAction'] === 'theme' || $data['detailedAction'] === 'core'){
			$data['type']= 'PTC';
			$data['action'] = 'update';
		} else if($data['name'] === 'clientPluginBranding'){
			$data['type']= 'clientPluginBranding';
			$data['action'] = 'change';
		} else if($data['detailedAction'] === 'readd'){
			$data['type'] = 'site';
			$data['action'] = 'readd';
			$data['isStage'] = 'staging';
		} else if($data['detailedAction'] === 'add'){
			$data['type'] = 'site';
			$data['action'] = 'add';
			$data['isStage'] = 'staging';
		}
	} else if ($category === 'staging' && $type === 'title'){
			if ($data['type'] === 'staging' && $data['action'] !== 'deleteSite'){
				return 'Staging for 1 site';
			} else {
				return false;
			}
	}
}

function stagingTweakInProcessQueue(&$data){
	if ($data['type'] != 'staging') {
		return false;
	}
	foreach ($data['detailedStatus'] as $key => $task) {
		if($task['detailedAction'] == 'add' || $task['detailedAction'] == 'readd'){
			$status = $task['status'];
			$data['statusSummary']['total'] -= 1;
			$data['statusSummary'][$status] -=  1;
			unset($data['detailedStatus'][$key]);
			unset($data['detailedActions']['add']);
			unset($data['detailedActions']['readd']);
		}
	}
}

function getParentHistoryID($historyID){
	return DB::getField("?:history", "parentHistoryID" , "historyID = ".$historyID);
}

function getActionIDByHistoryID($historyID){
	return DB::getField("?:history", "actionID", "historyID = ".$historyID);
}

function isConfigWritable(){
	if (is_writable(APP_ROOT.'/config.php')) {
		return 1;
	}
	else{
		return 0;
	}
}
function isFTPDefinedConfig(){
	if(defined('APP_FTP_HOST') || defined('APP_FTP_PORT') || defined('APP_FTP_BASE') || defined('APP_FTP_USER') || defined('APP_FTP_PASS') || defined('APP_FTP_SSL') || defined('APP_FTP_USE_SFTP')){
		return 1;
	}
	return 0;
}

function isShowBetaWelcome(){
		return getOption('showBetaWelcome');
}
function isShow2_7AddonUpdatePopup(){
		return getOption('show2_7AddonUpdatePopup');
}

function getValidTill($addon=array()) {
	$extra_info = $class = '';
	if(time()-$addon['validityExpires']>=604800) { // greater than or equal to 7 days after expiry
		$class = "gp_over"; 
	} else if($addon['validityExpires']-time()>0 && $addon['validityExpires']-time()<5184000) { // between 60 days before expiry
		$class = "";
	} else if($addon['validityExpires']-time()>0 && $addon['validityExpires']-time()<604800) { // between 7 days before expiry
		$class = "in_gp"; 
	} else if(time()-$addon['validityExpires']>0 && time()-$addon['validityExpires']<604800) { // between 7 days after expiry
		$class = "in_gp"; 
		$checkTime7days = 604800-(time()-$addon['validityExpires']);

		if($checkTime7days>518400 && $checkTime7days<604800) {
			$extra_info = 'Extended to 7 days';
		} else {
			$calcDays = floor(($checkTime7days%2592000)/86400);
			
			if($calcDays==1) $dayPlural="";
			else $dayPlural="s";
			if($calcDays==0) {
				$extra_info = 'Valid till today';
			} else {
				$extra_info = 'Valid till '.$calcDays.' day'.$dayPlural;
			}
		}				
	} else if(time()-$addon['validityExpires']<0 && time()-$addon['validityExpires']>=-2592000) {
		$calcDays = (floor((((time()-$addon['validityExpires']))%2592000)/86400)*-1);
		if($calcDays==1) $dayPlural="";
		else $dayPlural="s";
		$extra_info = 'Expires in '.$calcDays.' day'.$dayPlural;
	}
	$return = array(
		'class'=>$class,
		'extra_info'=>$extra_info
	);
	return($return);
}
function addLoginLog($loginEmail, $loginAuthType = '', $loginAttemptStatus = '', $error = '', $loginRedirect = '',$HeaderInfo = ''){
	$loginTime = time();
	$browserInfo = getBrowser();
	$browser = $browserInfo['name'].' '.$browserInfo['version'];
	$loginIP = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "HTTPS" : "HTTP";
	$loginAttemptFailedCount = getOption('loginAttemptFailedCount');
	$browserAgent = $browserInfo['userAgent'];
	if ($error == 'Invalid credentials.') {
		$loginAttemptFailedCount = !empty($loginAttemptFailedCount)?$loginAttemptFailedCount + 1:1;
		updateOption('loginAttemptFailedCount',$loginAttemptFailedCount);	
	}
	$where = array(
      		'query' =>  "email=':email'",
      		'params' => array(
               ':email'=>trim($loginEmail)
				)
		);
	$userDetails = DB::getRow("?:users", "userID, accessLevel", $where);

	if(!empty($loginEmail)){
		$isUserExists = !empty($userDetails["userID"]) ? true : false;
		if ($isUserExists) {
			$ID = DB::insert('?:login_logs',array('userID' => $userDetails['userID'], 'email' => $loginEmail,'accessLevel' => $userDetails['accessLevel'], 'time' =>$loginTime, 'browserInfo' =>$browser, 'loginAuthType' =>$loginAuthType, 'IP' =>$loginIP, 'error' => $error, 'loginAttemptStatus' => $loginAttemptStatus, 'protocol' => $protocol, 'loginRedirect' => $loginRedirect, 'browser' => $browserAgent));
		}	
		else{
			$ID = DB::insert('?:login_logs',array('email' => $loginEmail, 'time' =>$loginTime, 'browserInfo' =>$browser, 'loginAuthType' =>$loginAuthType, 'IP' =>$loginIP, 'error' => $error, 'loginAttemptStatus' => $loginAttemptStatus, 'protocol' => $protocol, 'loginRedirect' => $loginRedirect, 'browser' => $browserAgent));
		}
	
	}
	
	if ($loginAttemptStatus == 'success') {
		$loginAttemptFailedCount = 0;
		updateOption('loginAttemptFailedCount', 0);
		updateOption('loginSecurityMailDetails', 0);
	}
	if ($loginAttemptFailedCount >= 3) {
		checkAndSendLoginSecurityMail($loginIP, $userDetails["userID"], $browser, $ID);
	}
}

function checkAndSendLoginSecurityMail($loginIP, $userID, $browser,$ID){
	$loginSecurityMailDetails = unserialize(getOption('loginSecurityMailDetails'));
	$time = time()+60*60;
	$isIPPresent = 0;
	$isMailSend = 0;
	$temp = doCall("http://ipinfo.io/{$loginIP}/json", '', $timeout=30);
	list($locationDetails, , , $curlInfo) = $temp;
	$mailParams = array('loginDetails' => array('time' => time(),'loginIP' => $loginIP, 'locationDetails' =>$locationDetails, 'browserInfo'=> $browser), 'userID' => $userID);

	if (empty($loginSecurityMailDetails)) {
		$loginSecurityMailDetails = array(0 =>array('loginIP' =>$loginIP , 'nextMailSendTime' => $time));
		$isMailSend = sendLoginSecurityMail($mailParams, $userID);
		
	}
	else{
			foreach ($loginSecurityMailDetails as $key => $details) {
				if($loginIP == $details['loginIP'] ){
					$isIPPresent = 1;
					if(time() >= $details['nextMailSendTime']){
						$isMailSend = sendLoginSecurityMail($mailParams, $userID);
						$loginSecurityMailDetails[$key]['nextMailSendTime'] = $time;
					}
				}
			}
	}
	if ($isIPPresent == 0 && !$isMailSend ) {
		$isMailSend = sendLoginSecurityMail($mailParams, $userID);
		$loginSecurityMailDetails = array_merge($loginSecurityMailDetails,array(array('loginIP' =>$loginIP , 'nextMailSendTime' => $time)));
	}
		
	if ($isMailSend) {
		if(empty($locationDetails['city'])){
			addNotification($type='W', $title='FAILED LOGIN ATTEMPTS', $message='Someone tried to login to your InfiniteWP admin panel with incorrect credentials 3 times recently.<br><br>Time of first attempt:'.date('M d, Y @ h:ia', time()).'<br>IP:'.$loginIP.'<br>Browser: '.$browser.'<br><br>If the information above looks familiar, you can disregard this.<br> If you have not recently not tried to login to your panel, someone may be trying to.<br><br>To secure your admin panel, go to Settings ? Security. <a id="view_login_detail" loginID='.$ID.'>View more details</a>', $state='U', $callbackOnClose='', $callbackReference='');
		}else{ 
			addNotification($type='W', $title='FAILED LOGIN ATTEMPTS', $message='Someone tried to login to your InfiniteWP admin panel with incorrect credentials 3 times recently.<br><br>Time of first attempt:'.date('M d, Y @ h:ia', time()).'<br>IP:'.$loginIP.'<br>Location: '.$locationDetails['city'].', '.$locationDetails['country'].'.<br>Browser: '.$browser.'<br><br>If the information above looks familiar, you can disregard this.<br> If you have not recently not tried to login to your panel, someone may be trying to.<br><br>To secure your admin panel, go to Settings ? Security.<a id="view_login_detail" loginID='.$ID.'>View more details</a>', $state='U', $callbackOnClose='', $callbackReference='');
		}
		updateOption('loginSecurityMailDetails', serialize($loginSecurityMailDetails));
	}

}

function sendLoginSecurityMail($mailParams, $userID){
	$adminUserID = DB::getField("?:users", "userID", "accessLevel = 'admin' AND status = 1 ORDER BY userID ASC LIMIT 1");
	if($adminUserID == $userID || empty($userID)){
		$mailParams['userID'] = $adminUserID;
		$isMailSent = sendAppMail($mailParams, '/templates/email/loginFailedAttemptNotification.tpl.php');
	}
	else{
		$isMailSent = sendAppMail($mailParams, '/templates/email/loginFailedAttemptNotification.tpl.php');
		$mailParams['userID'] = $adminUserID;
		$isMailSent = sendAppMail($mailParams, '/templates/email/loginFailedAttemptNotification.tpl.php');
	}

	return $isMailSent;
}
function loginLogHistoryViewBeautifier($value){
	switch ($value) {
		case 'email':
			return 'Email';
		case 'browserInfo':
			return 'Browser';
		case 'loginAuthType':
			return 'Login type';
		case 'IP':
			return 'Logged IP';
		case 'error':
			return 'Error message';
		case 'loginAttemptStatus':
			return 'Login attempt status';
		case 'protocol':
			return 'Protocol';
		case 'accessLevel':
			return 'Access level';
		case 'loginRedirect':
			return 'Login redirect';
	}
}
function addURLHeader($params){
	return '__URL_PREFIX__'.$params['folderPath'].$params['fileName'];
}

function replaceURLHeader($URL){
	$headerPos = strpos($URL, '__URL_PREFIX__');
	if($headerPos !== false){
		return str_replace('__URL_PREFIX__', APP_FULL_URL, $URL);
	}
	return $URL;
}

function copyUploadsToFavoritesFolder(&$params){
	$favoritesPath = 'uploads/favorites/';
	$fullFavoritesPath = APP_ROOT.'/'.$favoritesPath;
	$currentPath = APP_ROOT.'/'.$params['folderPath'].''.$params['fileName'];
	$newPath = APP_ROOT.'/'.$favoritesPath.''.$params['fileName'];
	if (!file_exists($fullFavoritesPath)) {
		mkdir($fullFavoritesPath);
	}
	if (!copy($currentPath, $newPath)) {
		return false;
	}
	$params['folderPath'] = $favoritesPath;
	$params['URL'] = addURLHeader($params);
}

function getSuperAdminDetails(){
	$where = "accessLevel = 'admin' AND status = '1' ORDER BY userID ASC LIMIT 1";
	return DB::getArray("?:users", "*", $where);
}

function isClientReportingInstalled() {
	
	$where = array(
					'query' =>  "slug = ':slug'",
					'params' => array(
					   ':slug'=> 'clientReporting'
					)
				);
	$isExist = DB::getField("?:addons", "slug", $where);
	return $isExist ? true : false;
}

function isSaveActivityLogInClient(){
	return isClientReportingInstalled();
}

function isFailedBackup($backupData){
	if ($backupData['what'] === NULL && $backupData['time'] === NULL && $backupData['downloadURL'] === NULL && $backupData['size'] === NULL) {
		return true;
	}
	return false;
}

function createHTMLCacheAfterUpdate(){
	if (function_exists('loadActiveAddons')) {
		loadActiveAddons();
	}
	if (function_exists('getAddonsHTMLHead')) {
		getAddonsHTMLHead();
	}
}

function printGroupsForReloaData(){
	$groupDetails =  panelRequestManager::getGroupsSites();
	$content = '';
	if (empty($groupDetails)) {
		$content = '<li class ="l2"><a style = "font-style: italic;color: #aaaaaa;">No groups</a></li>';
	} else {
		foreach ($groupDetails as $key => $group) {
			if (!empty($group['siteIDs'])) {
				$content = $content .'<li class ="l2 groupReloadStats" groupID = '.$group['groupID'].' ><a>'.$group['name'].'</a></li>';
			}else{
				$content = $content.'<li class ="l2 disabled emptyGroups" groupID = '.$group['groupID'].' ><a style = "font-style: italic;color: #aaaaaa;">'.$group['name'].'</a></li>';
			}
		} 
	}
	return $content;
}

