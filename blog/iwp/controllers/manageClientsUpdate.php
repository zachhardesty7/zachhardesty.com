<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

class manageClientsUpdate {
	
	public static function updateAllProcessor($siteIDs, $allParams){
		

		if(empty($allParams)) return false;

		$updateInStaging = 0;
		if ( isset($allParams['type']) && $allParams['type'] == 'staging') {
			if (function_exists("stagingUpdateInExistingStaging")) {
				$result = stagingUpdateInExistingStaging($allParams);
				$stagingParams = $result['stagingSite'];
				$allParams = $result['mainSite'];
				$stagingSiteID = $result['stagingSiteID'];
				$updateInStaging = 1;
			}
		}
		$requestAction = 'do_upgrade';
		$type = 'PTC';
		$action = 'update';
		$sitesStats = DB::getFields("?:site_stats", "stats, siteID", "siteID IN (".implode(',', array_keys(DB::esc($allParams))).")", "siteID");
		
		foreach($sitesStats as $siteID => $sitesStat){
			$sitesStats[$siteID] = unserialize(base64_decode($sitesStat));
		}
		
		if ($updateInStaging == 1) {
			$sitesData = getSitesData(array_keys($stagingParams));
		} else {
			$sitesData = getSitesData(array_keys($allParams));
		}
		
		foreach($allParams as $siteID => $siteParams){
			$siteIDs = array($siteID);
			$events = 0;
			$requestParams = $historyAdditionalData = array();
			$timeout = DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT;
			
			//for staging
			$parentHistoryID = $siteParams['parentHistoryID'];
			if(!empty($siteParams['parentActionID'])){
				$parentActionID = $siteParams['parentActionID'];
			}
			foreach($siteParams as $PTC => $PTCParams){				
				
				if($PTC == 'plugins'){
					
					if(!empty($sitesStats[$siteID]['premium_updates']))
					{
						foreach($sitesStats[$siteID]['premium_updates'] as $item){						
							if(in_array($item['slug'], $PTCParams)){
								$uniqueName = $item['Name'];
								$requestParams['upgrade_plugins'][] = array_change_key_case($item, CASE_LOWER);
								$historyAdditionalData[] = array('uniqueName' => $uniqueName, 'detailedAction' => 'plugin');
								$timeout += 20;
								$events++;
							}
						}
					}
					
					if(!empty($sitesStats[$siteID]['upgradable_plugins']))
					{
						foreach($sitesStats[$siteID]['upgradable_plugins'] as $item){
							if(in_array($item->file, $PTCParams)){
								 $uniqueName = $item->file ;
								 $requestParams['upgrade_plugins'][] = $item;
								 $historyAdditionalData[] = array('uniqueName' => $uniqueName, 'detailedAction' => 'plugin');
								 $timeout += 20;
								 $events++;
							}
						}
					}
				}
				
				elseif($PTC == 'themes'){
					foreach($sitesStats[$siteID]['upgradable_themes'] as $item){
						if(in_array($item['theme_tmp'], $PTCParams) || in_array($item['name'], $PTCParams)){
							$requestParams['upgrade_themes'][] = $item;
							$uniqueName = $item['theme_tmp'] ? $item['theme_tmp'] : $item['name'];
							$historyAdditionalData[] = array('uniqueName' => $uniqueName, 'detailedAction' => 'theme');
							$timeout += 20;
							$events++;
						}
					}
				}
				elseif($PTC == 'core'){
					if($sitesStats[$siteID]['core_updates']->current == $PTCParams){
						$requestParams['wp_upgrade'] = $sitesStats[$siteID]['core_updates'];
						$historyAdditionalData[] = array('uniqueName' => 'core', 'detailedAction' => 'core');
						$timeout += 120;
						$events++;
					}
				}
				elseif($PTC == 'translations'){
					if ($sitesStats[$siteID]['upgradable_translations']) {
						$requestParams['upgrade_translations'] = true;
						$historyAdditionalData[] = array('uniqueName' => 'translations', 'detailedAction' => 'translations');
						$timeout += 60;
						$events++;
					}
				}
			}
			if ($updateInStaging == 1) {
				$siteData = $sitesData[$stagingSiteID];
			} else {
				$siteData = $sitesData[$siteID];
			}
			
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['events'] 			= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['timeout'] 		= $timeout;
			$PRP['doNotExecute'] 			= false;
			$PRP['sendAfterAllLoad'] = true;
			$PRP['parentHistoryID'] = $parentHistoryID;
			if (!empty($parentActionID)) {
				$PRP['actionID'] = $parentActionID;
			}
			prepareRequestAndAddHistory($PRP);
		}	
	}
	
	public static function updateAllResponseProcessor($historyID, $responseData){
		
		$isTranslationUpdate = fasle;
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		
		if(!empty($responseData['success']['error']) || !empty($responseData['success']['failed'])){
			
			$errorMsg = !empty($responseData['success']['error']) ? $responseData['success']['error'] : $responseData['success']['failed'];
			$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			               ':historyID'=>$historyID
           				)
        			);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $errorMsg, 'error' => $responseData['success']['error_code']), $where);	
		}		  
		else{
			foreach($responseData['success'] as $PTC => $PTCResponse){
				
				if($PTC == 'core') $itemType = 'core';
				elseif($PTC == 'plugins') $itemType = 'plugin';
				elseif($PTC == 'themes') $itemType = 'theme';				
				elseif($PTC == 'translations') $itemType = 'translations';				
				
				if(!empty($PTCResponse['error'])){
					
					$historyAdditionalUpdateData['status'] = 'error';
					$historyAdditionalUpdateData['errorMsg'] = $PTCResponse['error'];						
					$historyAdditionalUpdateData['error'] = $PTCResponse['error_code'];	
					$where = array(
			      		'query' =>  "historyID=':historyID'  AND detailedAction=':itemType'",
			      		'params' => array(
			               ':historyID'=>$historyID,
			               ':itemType'=>$itemType
           				)
        			);					
					DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
				}
				else{
					
					if($PTC == 'core'){
						$historyAdditionalUpdateData = array();
						$historyAdditionalUpdateData['status']= 'error';
						
						if(trim($PTCResponse['upgraded']) == 'updated'){
							$historyAdditionalUpdateData['status'] = 'success';
						}
						$where = array(
				      		'query' =>  "historyID=':historyID' AND uniqueName = ':uniqueName'",
				      		'params' => array(
				               ':historyID'=>$historyID,
				               ':uniqueName'=>'core'
	           				)
	        			);
						DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
					}
					elseif($PTC == 'plugins' || $PTC == 'themes'){

						foreach($PTCResponse['upgraded'] as $name => $success){
							$where = array(
						      		'query' =>  "historyID=':historyID' AND (uniqueName=':uniqueName' OR MD5(uniqueName)=':uniqueName') AND detailedAction=':detailedAction'",
						      		'params' => array(
						               ':historyID'=>$historyID,
						               ':uniqueName'=>$name,
						               ':detailedAction'=>$itemType
			           				)
			        			);		
							if($success == 1){
								$status = 'success';
								DB::update("?:history_additional_data", array('status' => $status), $where);
							}
							elseif(!empty($success)){
								$status = 'error';
								DB::update("?:history_additional_data", array('status' => $status, 'errorMsg' => $success['error'], 'error' => $success['error_code']), $where);
							}
							else{
								$status = 'error';
								DB::update("?:history_additional_data", array('status' => $status, 'error' => 'unknown', 'errorMsg' => 'An Unknown error occurred.', 'error' => 'unknown_error_occurred_updateall_res_proc'), $where);
							}
						}
					}
					elseif( $PTC == 'translations'){
						$isTranslationUpdate = true;
						$historyAdditionalUpdateData = array();
						$historyAdditionalUpdateData['status']= 'error';
						if(trim($PTCResponse['upgraded']) == 'updated'){
							$historyAdditionalUpdateData['status'] = 'success';
							$where = array(
					      		'query' =>  "historyID=':historyID' AND uniqueName = ':uniqueName'",
					      		'params' => array(
					               ':historyID'=>$historyID,
					               ':uniqueName'=>'translations'
		           				)
		        			);
							DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
						}
					}
				}
			}
		}
	
		//---------------------------callback process------------------------>
		$where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
		$siteID = DB::getField("?:history", "siteID", $where);
		$params = array();
		if ($isTranslationUpdate) {
			$params = array('forceRefresh' => 1);
		}

		$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'params' => $params, 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));

		//Staging site will not be removed after updates
		// if (method_exists('manageClientsInstallCloneCommon', 'removeStagingSiteAfterUpdate')) {
		// $isRemove = manageClientsInstallCloneCommon::removeStagingSiteAfterUpdate($historyID, $siteID);
		// }

		panelRequestManager::handler($allParams);
	}
	
	public static function updateClientProcessor($siteIDs, $params){		
		$requestAction = 'update_client';
		$type = 'clientPlugin';
		$action = 'update';
		$events = 1;
		
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('detailedAction' => 'update', 'uniqueName' => 'clientPlugin');
		foreach($siteIDs as $siteID){
				$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
       				)
    			);
				$currentVersion = DB::getField("?:sites", "pluginVersion", $where);
				if(version_compare($currentVersion,  $params['clientUpdateVersion'] ) == -1){
					$siteData = getSiteData($siteID);
					$requestParams = array('download_url' =>$params['clientUpdatePackage']);
					$PRP = array();
					$PRP['requestAction'] 	= $requestAction;
					$PRP['requestParams'] 	= $requestParams;
					$PRP['siteData'] 		= $siteData;
					$PRP['type'] 			= $type;
					$PRP['action'] 			= $action;
					$PRP['events'] 			= $events;
					$PRP['historyAdditionalData'] 	= $historyAdditionalData;
					$PRP['sendAfterAllLoad'] 		= true;			
					
					prepareRequestAndAddHistory($PRP);
			}
		}
	}
	
	public static function updateClientResponseProcessor($historyID, $responseData){
			
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
					'query' =>  "historyID=':historyID'",
					'params' => array(
						':historyID'=>$historyID,
					)
				);
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);
		}
		if(!empty($responseData['success']['success'])){
			DB::update("?:history_additional_data", array('status' => 'success'), $where);

			//---------------------------callback process------------------------>
			$siteID = DB::getField("?:history", "siteID", $where);
			// 'directExecute'=>true for client plugin update notification
			$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false,'directExecute' => true, 'doNotShowUser' => true))); 
			panelRequestManager::handler($allParams);
			
		}
	}
}

manageClients::addClass('manageClientsUpdate');
