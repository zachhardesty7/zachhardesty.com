<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

class panelRequestManager{
	
	private static $addonFunctions = array();
	private static $rawSitesStatsCache = array();

	public static function handler($requestData){
		$requestStartTime = microtime(true);
		
                    /* Checking the condition for this action is allowed to user (JS INJECTION ) */
                $where = array(
		      		'query' =>  "userID=':userID'",
		      		'params' => array(
		               ':userID'=>$GLOBALS['userID']
       				)
    			);
                $userData = DB::getRow("?:users", "userID, accessLevel, permissions", $where);
                $Restrict=FALSE;
                if($userData['accessLevel']!='admin')
                {
                    if(function_exists('userRestrictChecking')){
                        $Restrict=userRestrictChecking($userData,$requestData);
                    }
                }	
		//$GLOBALS['printAll'] = true;
		
		$clearPrint = empty($GLOBALS['printAll']) ? true :  false;
		
		if($clearPrint){
			ob_start();
		}
		
		$actionResult = $data = array();
		$requestData = self::decodeRequestParameters($requestData);
		$action 	= $requestData['action'];
		$siteIDs 	= $requestData['args']['siteIDs'];
		$params 	= $requestData['args']['params'];
		$extras 	= $requestData['args']['extras'];
		$requiredData = $requestData['requiredData'];		
		$actionID = uniqid('', true);
		Reg::set('currentRequest.actionID', $actionID);
                if(!$Restrict){  //Checking restriction here
		if(manageClients::methodExists($action)){
			manageCookies::cookieUnset('slowDownAjaxCallFrom');
			
			if(!empty($siteIDs) && function_exists('multiUserAccess')){
				multiUserAccess($siteIDs);
			}
						
			manageClients::execute($action, array('siteIDs' => $siteIDs, 'params' => $params, 'extras' => $extras));
			
			if(Reg::get('currentRequest.exitOnComplete') === true){
				if(Reg::get('settings.executeUsingBrowser') != 1){//to fix update notification going "everything up to date" for fsock users
					executeJobs();
				}
				$exitOnCompleteT = microtime(true);
				exitOnComplete();
				$exitOnCompleteTT = microtime(true) - $exitOnCompleteT;
			}
			if(Reg::get('currentRequest.sendAfterAllLoad') === true){
				sendAfterAllLoad($requiredData);	
			}
			
			$actionResult = self::getActionStatus($actionID, $action);
		}
		if(Reg::get('settings.executeUsingBrowser') != 1 && !defined('CRON_MODE') && !defined('IS_EXECUTE_FILE')){// && !defined('CRON_MODE') && !defined('IS_EXECUTE_FILE') //panelRequestManager::handler() has been called in many places in app. inorder to avoid executeJobs()
			executeJobs();
		}
                }
		$data = self::requiredData($requiredData);
		$finalResponse = array();
		$finalResponse = array('actionResult' => $actionResult, 'data' => $data);		
		
		if(empty($requestData['noGeneralCheck'])){
			self::generalCheck($finalResponse);
		}
		
		$finalResponse['sendNextAjaxCallAfter'] = self::getSendNextAjaxCallAfter();

		if ($finalResponse['sendNextAjaxCallAfter'] >= 60) {
			self::runWhileBrowserIdle();
		} 
		$finalResponse['showBrowserCloseWarning'] = showBrowserCloseWarning();
		
		
		if($clearPrint){
			$printedText = ob_get_clean();
		}
		
		$finalResponse['debug'] = array('exitOnCompleteTimeTaken' => $exitOnCompleteTT, 
										'currentRequest.exitOnComplete' => var_export(Reg::get('currentRequest.exitOnComplete'), true),
										'totalRequestTimeTaken' => (microtime(true) - $requestStartTime),
										/*'printedText' => $printedText,*/
										);
	    return jsonEncoder($finalResponse);
	}
	
	public static function userAccess($siteIDs){
		$count = count($siteIDs);
		$where = array(
	      		'query' =>  "userID = ':userID' AND siteID IN (':siteID')" ,
	      		'params' => array(
	               ':userID'=>$GLOBALS['userID'],
	               ':siteID'=>implode(', ', $siteIDs)
   				)
			);
		$accessSitesCount = DB::getfield("?:user_access", "count(siteID)", $where );
		if($accessSitesCount == $count && $count > 0){
			return true;
		}		
		return false;
	}
	
	public static function requiredData($requiredData){
            if(empty($requiredData)){
                return array();
            }
            Reg::tplSet('sitesData', self::getSites());
            $data = array();
            foreach($requiredData as $action => $args){
            	if(self::checkDataIsValid($action)){
                	$data[$action] = self::evaluateMethod($action, $args);
            	}else{
            		return null;
            	}
            }
            return $data;
	}
        
        public static function evaluateMethod($action, $args){
            if(method_exists('panelRequestManager', $action)){
                if($action == 'getSitesUpdates'){
                    return self::$action($GLOBALS['userID']);
                }else{
                    return self::$action($args);
                }         
            }elseif(in_array($action, self::$addonFunctions) && function_exists($action)){
                return call_user_func($action, $args);
            }elseif(strpos($action, '::') !== false && in_array($action, self::$addonFunctions)){
                $tempMethod = explode('::', $action);
                $className = $tempMethod[0];
                $method = $tempMethod[1];
                if(method_exists($className, $method)){
                    return call_user_func($action, $args);
                }
            }
        }
	
	public static function addFunctions(){
		$args = func_get_args();
		self::$addonFunctions = array_merge(self::$addonFunctions, $args);
	}

	
	public static function getBackups($siteID, $refresh=false){//viewBackups
		
		if($refresh){
			manageClients::getStatsProcessor(array($siteID));
		}
		$where = array(
	      		'query' =>  "stats IS NOT NULL AND siteID = ':siteID'",
	      		'params' => array(
	               ':siteID'=>$siteID
   				)
			);
		$sitesStatRaw = DB::getRow("?:site_stats", "*", $where);	
		$backups = unserialize(base64_decode($sitesStat['stats']['iwp_backups'])); 
		
		return $backups;
	}
	
	
	public static function addSiteSetGroups($siteID, $groupsPlainText, $groupIDs){
		
		if(empty($siteID)) return false;
		
		if(empty($groupIDs)){ $groupIDs = array(); }
		$where = array(
			      		'query' =>  "siteID = ':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
		DB::delete("?:groups_sites", $where);//for updating
		
		$groupNames = explode(',', $groupsPlainText);
		array_walk($groupNames, 'trimValue');
		$groupNames = array_filter($groupNames);
		if(!empty($groupNames)){
			$where = array(
	      		'query' =>  "name IN (':groupNames')",
	      		'params' => array(
	               ':groupNames'=>implode("', '", $groupNames)
   				)
			);
			$existingGroups = DB::getArray("?:groups", "*", $where, "name");
			foreach($groupNames as $groupName){
				if(isset($existingGroups[$groupName])){
					array_push($groupIDs, $existingGroups[$groupName]['groupID']);
				}
				else{
					$newGroupID = self::addGroup($groupName);
					array_push($groupIDs, $newGroupID);
				}
			}			
		}
		$groupIDs = array_filter(array_unique($groupIDs));
		
		if(!empty($groupIDs)){
			foreach($groupIDs as $groupID){
				DB::replace("?:groups_sites", array('groupID' => $groupID, 'siteID' => $siteID));
			}
		}		
	}
	
	public static function manageGroups($groupsData){
		
		$newGroups = $groupsData['new'];//array('new-0' => 'name', 'new-1' => 'groupname2');
		if(!empty($newGroups)){
			$newGroups = array_filter($newGroups);
		}
		$deleteGroups = $groupsData['delete'];//array(1, 2);//groupIDS
		$updateGroupsSites = (!empty($groupsData['updateSites'])) ? $groupsData['updateSites'] : array();//array(5 => array(1,2), 'new-1' => array(2,4));//'new-1' => its new group this key will be replaced by it id, before processing this array
		$updateGroupsNames  = $groupsData['updateNames'];//array(101 => 'newname', 102 => 'newname2');
		
		if(!empty($newGroups)){
			foreach($newGroups as $newGroupKey => $newGroupName){
				$newGroupID = self::addGroup($newGroupName);
				if($newGroupID){
					$updateGroupsSites[$newGroupID] = $updateGroupsSites[$newGroupKey];//here new-0 will be replaced by groupID
					unset($updateGroupsSites[$newGroupKey]);
				}
			}
		}
		
		if(!empty($updateGroupsSites)){
			$tempUpdateGroupsSites = $updateGroupsSites;
			foreach($tempUpdateGroupsSites as $groupID => $temp){
				if(!is_numeric($groupID)){ unset($updateGroupsSites[$groupID]); }
			}
			self::updateGroupsSites($updateGroupsSites);
		}
		
		if(!empty($updateGroupsNames)){
			foreach($updateGroupsNames as $groupID => $groupName){
				self::updateGroup($groupID, $groupName);
			}
		}
		
		if(!empty($deleteGroups)){
			foreach($deleteGroups as $groupID){
				self::deleteGroup($groupID);
			}
		}
		return true;		
	}
	
	private static function updateGroupsSites($params){
		if(empty($params)){ return false; }
		foreach($params as $groupID => $siteIDs){
			if(empty($siteIDs)){ continue; }
			$where = array(
			      		'query' =>  "groupID = ':groupID'",
			      		'params' => array(
			               ':groupID'=>$groupID
           				)
        			);
			DB::delete("?:groups_sites", $where);
			foreach($siteIDs as $siteID){
				if(is_numeric($siteID)){
					DB::replace("?:groups_sites", array('groupID' => $groupID, 'siteID' => $siteID));
				}
			}
		}
		return true;		
	}
	
	public static function getFavouritesGroups(){
		$groups = $favourites = array();
		$favourites = DB::getArray("?:favourite_groups FG, ?:favourites_groups_map F", "F.groupID, F.favouriteID", "F.groupID =  FG.groupID ");
		$groups = DB::getArray("?:favourite_groups", "groupID, name, type", "1 ORDER BY groupID", "groupID");
		foreach($favourites as $favourite){
			$groups[ $favourite['groupID'] ]['IDs'][] = $favourite['favouriteID'];
		}
		return $groups;
	}
	public static function getGroupsSites(){
		$groups = $groupsSites = array();
		
		$where = " ";
		$where2 = "1";
		$where3 = array(
	      		'query' =>  "GS.groupID =  :groupID ".$where,
	      		'params' => array(
	               ':groupID'=>'G.groupID'
   				)
			);
		$groupsSites = DB::getArray("?:groups_sites GS, ?:groups G", "GS.siteID, GS.groupID", $where3);
		$groups = DB::getArray("?:groups", "groupID, name", $where2." ORDER BY groupID", "groupID");
		
		
		foreach($groupsSites as $groupSites){
			$groups[ $groupSites['groupID'] ]['siteIDs'][] = $groupSites['siteID'];
		}
		return $groups;
	}
	
	private static function addGroup($name){
		return DB::insert("?:groups", array('name' => $name));
	}
	
	private static function updateGroup($groupID, $name){
		$where = array(
	      		'query' =>  "groupID = ':groupID'",
	      		'params' => array(
	               ':groupID'=>$groupID
   				)
			);
		
		return DB::update("?:groups", array('name' => $name), $where);
	}
	
	private static function deleteGroup($groupID){
		$where = array(
	      		'query' =>  "groupID = ':groupID'",
	      		'params' => array(
	               ':groupID'=>$groupID
   				)
			);
		$done = DB::delete("?:groups", $where);
		if($done){
			$done = DB::delete("?:groups_sites", $where);
		}
		return $done;
	}
	
	public static function getRawSitesStats($siteIDs=array(), $userID = '', $doCache = true){
		$cacheSlug = 'all';
		if(!empty($siteIDs)){
			sort($siteIDs);
			$cacheSlug =  implode('-', $siteIDs);
		}
		if(!empty(self::$rawSitesStatsCache[$cacheSlug]) && $doCache){
			return self::$rawSitesStatsCache[$cacheSlug];
		}
		$where = "";
		$sitesStats = array();
		
		if(function_exists('multiUserRawSitesStats')){
			multiUserRawSitesStats($sitesStats, $siteIDs, $userID);
		}
		else{
			if(!empty($siteIDs)){
				$siteIDs = DB::esc($siteIDs);
				$where = " AND SS.siteID IN (". implode(',', $siteIDs) .")";
			}
					
			$sitesStats = DB::getArray("?:site_stats SS, ?:sites S", "SS.*", "S.siteID = SS.siteID AND SS.stats IS NOT NULL AND S.type = 'normal' ".$where, "siteID");
		}
		
		if(empty($sitesStats)){ return array(); }
		foreach($sitesStats as $siteID => $sitesStat){
			$sitesStats[$siteID]['stats'] = unserialize(base64_decode($sitesStat['stats']));
		}
		

		self::$rawSitesStatsCache[$cacheSlug]=$sitesStats;
		return $sitesStats;
	}
	
	public static function getSitesBackups($siteIDs=array()){
		$sitesStats = self::getRawSitesStats($siteIDs);
		
		$sitesBackups = array();
		
		foreach($sitesStats as $siteID => $siteStats){
			
			$backupKeys = @array_keys($siteStats['stats']['iwp_backups']);
			if(!empty($backupKeys) && is_array($backupKeys)){	
			foreach($backupKeys as $key => $backupKey){
				
				$backupTaskType = 'backupNow';
				if($backupKey != 'Backup Now'){
					$where = array(
				      		'query' =>  "BS.scheduleKey = ':backupKey' AND BSL.siteID=':siteID' AND BS.scheduleID = :scheduleID",
				      		'params' => array(
				               ':backupKey'=>$backupKey,
				               ':siteID'=>$siteID,
				               ':scheduleID'=>'BSL.scheduleID'
			   				)
						);
					$siteExist = DB::getExists("?:backup_schedules_link BSL, ?:backup_schedules BS", "siteID", $where);
					if($siteExist){ continue;  }					
					$backupTaskType = 'otherBackup';
				}
				
				if(empty($siteStats['stats']['iwp_backups'][$backupKey])){
					continue;
				}
				
				$siteBackupsTemp = $siteStats['stats']['iwp_backups'][$backupKey];
				$siteBackups[$siteID] = array();
				krsort( $siteBackupsTemp );
				
				foreach($siteBackupsTemp as $referenceKey => $siteBackupTemp){
					if(!empty($siteBackupTemp['error']) || isFailedbackup($siteBackupTemp) === true){ continue; }
					
					$otherParts = '';
													
					if(empty($siteBackupTemp['server']['file_url']) && !empty($siteBackupTemp['ftp'])){
						$otherParts = $siteBackupTemp['ftp'];
					}
					if(empty($siteBackupTemp['server']['file_url']) && !empty($siteBackupTemp['amazons3'])){
						$otherParts = $siteBackupTemp['amazons3'];
					}
					if(empty($siteBackupTemp['server']['file_url']) && !empty($siteBackupTemp['dropbox'])){
						$otherParts = $siteBackupTemp['dropbox'];
					}
					if(empty($siteBackupTemp['server']['file_url']) && !empty($siteBackupTemp['gDriveOrgFileName'])){
						$otherParts = $siteBackupTemp['gDriveOrgFileName'];
					}
					if((is_array($siteBackupTemp['server']['file_url']))||(is_array($otherParts)))
					{
						$fileURLParts = explode('/', $siteBackupTemp['server']['file_url'][0] ? $siteBackupTemp['server']['file_url'][0] : $otherParts[0]);
					}
					else
					{
						$fileURLParts = explode('/', $siteBackupTemp['server']['file_url'] ? $siteBackupTemp['server']['file_url'] : $otherParts);
					}
					$fileName = array_pop($fileURLParts);
					$fileNameParts = explode('_', $fileName);
					$what = $fileNameParts[2];
					
					$repo = '';
					//only showing files which are available
					if(array_key_exists('server', $siteBackupTemp))
					{
						$repo = "Server";
					}
					if(array_key_exists('ftp', $siteBackupTemp))
					{
						$repo = "FTP";
					}
					if(array_key_exists('amazons3', $siteBackupTemp))
					{
						$repo = "Amazon S3";
					}
					if(array_key_exists('dropbox', $siteBackupTemp))
					{
						$repo = "Dropbox";
					}
					if(array_key_exists('gDriveOrgFileName', $siteBackupTemp))
					{
						$repo = "G Drive";
					}
										
					$sitesBackups[$siteID][$backupTaskType][] = array('time' => $siteBackupTemp['time'],
																  'type' => 'backupNow',
																  'downloadURL' => $siteBackupTemp['server']['file_url'],
																  'size' => $siteBackupTemp['size'],
																  'what' => $what,
																  'referenceKey' => $referenceKey,
																  'backupName' => $siteBackupTemp['backup_name'],
																  'siteID' => $siteID,
																  'repository' => $repo,
																  'backupTaskType' => $backupTaskType,
																  'data' => array('scheduleKey' => $backupKey));
				}
		}
			}
		}
		
		return $sitesBackups;
	}
	
	public static function getSitesBackupsHTML(){
		$sitesBackups = self::getSitesBackups();
		$HTML = TPL::get('/templates/backup/view.tpl.php', array('sitesBackups' => $sitesBackups));
		return $HTML;
	}
	
	public static function getSiteBackupsHTML($siteID){
		$sitesBackups['manualBackups'] = self::getSitesBackups(array($siteID));
		setHook('getSiteBackups', $sitesBackups, $siteID);
		$HTML = TPL::get('/templates/backup/sitePopup.tpl.php', array('siteBackups' => $sitesBackups['manualBackups'], 'siteID' => $siteID , 'scheduleBackups' => $sitesBackups['scheduleBackups']));
		return $HTML;
	}

	public static function siteIsWritable(){
		
		$sitesStats = self::getRawSitesStats();
		foreach($sitesStats as $siteID){
			$siteIsWritable[$siteID['siteID']] = $siteID['stats']['writable'];			
		}
	    return $siteIsWritable;
	}
	
	public static function updatePageEmailCronReqNotification($totalUpdateCount){

		$isCronNotified = getOption('isUpdatePageEmailCronReqNotified');
		if (!empty($isCronNotified)){
			return '';
		}

		$settings = self::getSettings();
		$updatesNotificationMail = $settings['notifications']['updatesNotificationMail'];
		if($updatesNotificationMail['frequency'] == 'never' || (empty($updatesNotificationMail['coreUpdates']) && empty($updatesNotificationMail['pluginUpdates']) && empty($updatesNotificationMail['themeUpdates']) && empty($updatesNotificationMail['translationUpdates']))){
			return '';
		}

		$cronFrequency =getRealSystemCronRunningFrequency();
		if ($cronFrequency > 0) {
			return '';
		}

		$isEasyCronActive = manageEasyCron::isActive();
		if ($isEasyCronActive == true) {
			return '';
		}

		$isIWPCronActive = Manage_IWP_Cron::isActive();
		if ($isIWPCronActive == true) {
			return '';
		}

		if ($totalUpdateCount > 0) {
			return '1';
		}
	}

	public static function getSitesUpdates($userID = '', $doCache = true){
		
		$siteView = $pluginView = $themeView = $coreView = $translationsView = array();
		$sitesStats = self::getRawSitesStats(array(), $userID, $doCache);
		$hiddenUpdateCount = 0;

		foreach($sitesStats as $siteID){
			$siteID['stats']['premium_updates'] = (array)$siteID['stats']['premium_updates'];
			foreach($siteID['stats']['premium_updates'] as $item){			
				$isHiddenItem = false;
				$where = array(
		      		'query' =>  "URL = ':URL' AND siteID = ':siteID'",
		      		'params' => array(
		               ':URL'=>$item['slug'],
		               ':siteID'=>$siteID['siteID']
	   				)
				);
				$ignoredUpdates = DB::getField("?:hide_list", "URL", $where); 
				
				$pluginView['plugins'][$item['slug']][$siteID['siteID']] = $siteView[$siteID['siteID']]['plugins'][$item['slug']] = array_change_key_case($item, CASE_LOWER);				
				
				if($ignoredUpdates){ 
					$hiddenUpdateCount ++;
					$pluginView['plugins'][$item['slug']][$siteID['siteID']]['hiddenItem'] = $siteView[$siteID['siteID']]['plugins'][$item['slug']]['hiddenItem'] = true;
				} 
			}
			
			$siteID['stats']['upgradable_plugins'] = (array)$siteID['stats']['upgradable_plugins'];
			foreach($siteID['stats']['upgradable_plugins'] as $item){			
				$temp = objectToArray($item);
				if(!is_array($temp))
				$temp=array();
				
				$isHiddenItem = false;
				$where = array(
		      		'query' =>  "URL = ':URL' AND siteID = ':siteID'",
		      		'params' => array(
		               ':URL'=>$item->file,
		               ':siteID'=>$siteID['siteID']
	   				)
				);
				$ignoredUpdates = DB::getField("?:hide_list", "URL", $where); 
				if($ignoredUpdates){ 
					$hiddenUpdateCount ++;
					$isHiddenItem = true;
				} 
				$temp['hiddenItem'] = $isHiddenItem;
				
				$pluginView['plugins'][$item->file][$siteID['siteID']] = $siteView[$siteID['siteID']]['plugins'][$item->file] = $temp;
			}
			
			$siteID['stats']['upgradable_translations'] = (array)$siteID['stats']['upgradable_translations'];
			foreach($siteID['stats']['upgradable_translations'] as $item){			
				$temp = objectToArray($item);
				if(!is_array($temp))
				$temp=array();
				
				$isHiddenItem = false;
				$where = array(
		      		'query' =>  "type = ':type' AND siteID = ':siteID'",
		      		'params' => array(
		               ':type'=>'translations',
		               ':siteID'=>$siteID['siteID']
	   				)
				);
				$ignoredUpdates = DB::getField("?:hide_list", "ID", $where);

				if($ignoredUpdates){ 
					$hiddenUpdateCount++;
					$isHiddenItem = true;
				} 
				$temp['hiddenItem'] = $isHiddenItem;
				
				$translationsView['translations'][$item['name']][$siteID['siteID']] = $siteView[$siteID['siteID']]['translations'][$item['name']]= $temp;
			}

			$siteID['stats']['upgradable_themes'] = (array)$siteID['stats']['upgradable_themes'];
			foreach($siteID['stats']['upgradable_themes'] as $item){
				
				$isHiddenItem = false;
				$where = array(
			      		'query' =>  "URL = ':URL' AND siteID = ':siteID'",
			      		'params' => array(
			               ':URL'=>$item['theme_tmp'],
			               ':siteID'=>$siteID['siteID']
		   				)
					);
				$ignoredUpdates = DB::getField("?:hide_list", "URL", $where); 
				if($ignoredUpdates){ 
					$isHiddenItem = true;
					$hiddenUpdateCount++;
				} 
				$item['hiddenItem'] = $isHiddenItem;
				
				$themeView['themes'][$item['theme_tmp']][$siteID['siteID']] = $siteView[$siteID['siteID']]['themes'][$item['theme_tmp']] = $item;
							
			}
			
			if(!empty($siteID['stats']['core_updates'])){
				
				$item = $siteID['stats']['core_updates'];
				$temp = objectToArray($item);
				if(!is_array($temp))
				$temp=array();
				$isHiddenItem = false;
				$where = array(
			      		'query' =>  "URL = ':URL' AND siteID = ':siteID'",
			      		'params' => array(
			               ':URL'=>$item->current,
			               ':siteID'=>$siteID['siteID']
		   				)
					);
				$ignoredUpdates = DB::getField("?:hide_list", "URL", $where); 
				if($ignoredUpdates){ 
					$hiddenUpdateCount++;
					$isHiddenItem = true;
				}
				$temp['hiddenItem'] = $isHiddenItem;
				
				$coreView['core'][$item->current][$siteID['siteID']] = $siteView[$siteID['siteID']]['core'][$item->current] = $temp;
			}	
		}
		
		
		ksortTree($siteView, 3);
		ksortTree($pluginView, 2);
		ksortTree($themeView, 2);
		ksortTree($coreView, 2);
		
		$siteViewCount = array();//count of plugins, themes, core by site view
		$totalUpdateCount = $allUpdatesCount = 0;
		foreach($siteView as $siteID => $siteValues){
			$siteViewCount[$siteID]['core'] = $siteViewCount[$siteID]['themes'] = $siteViewCount[$siteID]['plugins'] = $siteViewCount[$siteID]['translations'] = 0;
			foreach($siteValues as $type => $items){
				foreach($items as $item){
					if(empty($item['hiddenItem'])){						
						$siteViewCount[$siteID][$type]++;
						$totalUpdateCount++;
					}
				}
			}
			
		}
		
		$lastReloadTime = DB::getField("?:site_stats", "lastUpdatedTime", "1 ORDER BY lastUpdatedTime LIMIT 1");
		$lastReloadTime = ($lastReloadTime > 0) ? @date('M d @ h:ia', $lastReloadTime) : '';
		
		//to get error message in sitesData
		getAllSitesStatsWithError();
		
		//adding error message to the sites Data
		$cronReloadCompCheckData = unserialize(getOption("cronReloadCompCheck"));
		
		if($cronReloadCompCheckData && is_array($cronReloadCompCheckData) && !empty($cronReloadCompCheckData['ignore'])){
			foreach($cronReloadCompCheckData['ignore'] as $key => $data){
				foreach ($sitesStats as $siteStats) {
					if ($siteStats['siteID'] == $data['siteID']) {
						if (empty($siteView[$data['siteID']])) {
							$siteView[$data['siteID']]['error'] = $data['errorMsg'];
						}
						break;
					}
				}
			}
		}
		$updatePageEmailCronReqNotification = self::updatePageEmailCronReqNotification($totalUpdateCount);
		$updatePageEmailCronReqNotification = self::updatePageEmailCronReqNotification($totalUpdateCount);
		$updateViews =array('siteView' => $siteView, 'pluginsView' => $pluginView, 'themesView' => $themeView, 'translationsView' => $translationsView, 'coreView' => $coreView, 'siteViewCount' => $siteViewCount, 'totalUpdateCount' => $totalUpdateCount, 'lastReloadTime' => $lastReloadTime, 'updatePageEmailCronReqNotification' => $updatePageEmailCronReqNotification,  'hiddenUpdateCount' => $hiddenUpdateCount);
		setHook('updateViews', $updateViews);
		return $updateViews;
	}
	
	public static function getSites(){
		
		$sitesData = array();
		
		if(function_exists('multiUserGetSites')){
			multiUserGetSites($sitesData);
		}
		else{
			$sitesData = DB::getArray("?:sites", "siteID, URL, adminURL, name, IP, WPVersion, adminUsername, isOpenSSLActive, network, parent, httpAuth, callOpt, connectURL, connectionStatus, links, notes, favicon, lastFaviconUpdate, stagingFtpDetails, ftpDetails", "type = 'normal' ORDER BY name", "siteID");
		}
		
		$groupsSites = DB::getArray("?:groups_sites", "*", "1");
		if(!empty($groupsSites)){
			foreach($groupsSites as $groupSite){
				if(!empty($sitesData[$groupSite['siteID']])){
					$sitesData[$groupSite['siteID']]['groupIDs'][] = $groupSite['groupID'];
				}
			}
		}
		if(is_array($sitesData)){
			foreach($sitesData as $siteID => $siteData){
				if(!empty($siteData['httpAuth'])){
					$sitesData[$siteID]['httpAuth'] = @unserialize($siteData['httpAuth']);
				}
				if(!empty($siteData['callOpt'])){
					$sitesData[$siteID]['callOpt'] = @unserialize($siteData['callOpt']);
				}if(!empty($siteData['links'])){
					$sitesData[$siteID]['links'] = explode(",",$siteData['links']);
				} if ($siteData['favicon'] == 'default' || $siteData['favicon'] == '' || $siteData['favicon'] == NULL ) {
					$sitesData[$siteID]['favicon'] = 'images/custom_wp_favicon.ico';
				}
				if(!empty($siteData['stagingFtpDetails'])){
					$stagingFtpDetails = @unserialize($siteData['stagingFtpDetails']);
					$stagingType = key($stagingFtpDetails);
					$stagingFtpDetails[$stagingType]['humanReadTime'] = @date(Reg::get('dateFormatLong'), $stagingFtpDetails[$stagingType]['createdTime']);				
					$sitesData[$siteID]['stagingFtpDetails'] = $stagingFtpDetails;
				}
				if(!empty($siteData['ftpDetails'])){
					$sitesData[$siteID]['ftpDetails'] = @unserialize($siteData['ftpDetails']);
				}
			}
		}
		return $sitesData;
	}
	
	public static function getSitesList(){
		$sitesData = array();
		
		if(function_exists('multiUserSitesList')){
			multiUserSitesList($sitesData);
		}
		else{
			$sitesData = DB::getArray("?:sites", "siteID, URL, adminURL, name, IP, adminUsername, isOpenSSLActive, network, parent", "type ='normal' ORDER BY name", "siteID");
		}
		
		if(is_array($sitesData)){
			foreach($sitesData as $k => $v){
				$sitesData['s'.$k] = $v;
				unset($sitesData[$k]);
			}
		}
		return $sitesData;
	}
	
	public static function getSearchedPluginsThemes(){
		
		$actionID = Reg::get('currentRequest.actionID');
		$where = array(
	      		'query' =>  "type = ':type' AND paramID = ':paramID'",
	      		'params' => array(
	               ':type'=>'getPluginsThemes',
	               ':paramID'=>$actionID
   				)
			);
		
		$datas = DB::getFields("?:temp_storage", "data", $where);
		$where = array(
	      		'query' =>  "type = 'getPluginsThemes' AND paramID = ':actionID'",
	      		'params' => array(
	               ':actionID'=>$actionID
   				)
			);
		DB::delete("?:temp_storage", $where);
		
		if(empty($datas)){
			return array();
		}
		$finalData = array();
		foreach($datas as $data){
			$finalData = array_merge_recursive($finalData, (array)unserialize($data));	
		}
	
		arrayMergeRecursiveNumericKeyHackFix($finalData);		
		ksortTree($finalData);	
		
		//finding not installed for site view only	
		$typeItems = array_keys($finalData['typeView']);		
		foreach($typeItems as $item){
			foreach($finalData['siteView'] as $siteID => $value){
				if(empty($value['active'][$item]) && empty($value['inactive'][$item])){
					$typeViewTemp = reset($finalData['typeView'][$item]);
					$finalData['siteView'][$siteID]['notInstalled'][$item] = reset($typeViewTemp);
				}
			}		
		}
		
		return $finalData;
	}
	
	public static function updateSettings($settings){
		
		
		$updateSettings = array();
		
		if(!empty($settings['general'])){
			$currentGeneralSettings = Reg::get('settings');
			if($settings['general']['autoSelectConnectionMethod'] == 1){
				$settings['general']['executeUsingBrowser'] = $currentGeneralSettings['executeUsingBrowser'];
				if($settings['general']['TIMEZONE'] != ''){
					@date_default_timezone_set( $settings['general']['TIMEZONE']);
				}
			}
				if(!empty($currentGeneralSettings['httpAuth'])){
					$settings['general']['httpAuth'] = $currentGeneralSettings['httpAuth'];
				}
			if(!empty($currentGeneralSettings['enableHTTPS'])){
				$settings['general']['enableHTTPS'] = $currentGeneralSettings['enableHTTPS'];
			}
			$updateSettings['general'] = serialize($settings['general']);
		}
		if(!empty($settings['notifications'])){
			$where = array(
	      		'query' =>  "userID = ':userID' ",
	      		'params' => array(
	               ':userID'=>$GLOBALS['userID']
   				)
			);
			DB::update("?:users", array("notifications" => serialize($settings['notifications'])), $where);
		}
		
		if(!empty($settings['emailSettings'])){
			$emailSettings = unserialize(getOption('emailSettings'));
			if($settings['emailSettings']['smtpSettings']['useSmtp'] == 0){
				$settings['emailSettings']['smtpSettings']['smtpHost'] = $emailSettings['smtpSettings']['smtpHost'];
				$settings['emailSettings']['smtpSettings']['smtpPort'] = $emailSettings['smtpSettings']['smtpPort'];
				$settings['emailSettings']['smtpSettings']['smtpAuthUsername'] = $emailSettings['smtpSettings']['smtpAuthUsername'];
				$settings['emailSettings']['smtpSettings']['smtpAuthPassword'] = $emailSettings['smtpSettings']['smtpAuthPassword'];
				$settings['emailSettings']['smtpSettings']['smtpEncryption'] = $emailSettings['smtpSettings']['smtpEncryption'];
				$settings['emailSettings']['smtpSettings']['smtpAuth'] = $emailSettings['smtpSettings']['smtpAuth'];
			}
			return updateOption('emailSettings', serialize($settings['emailSettings']));
		}
		
		if(!empty($updateSettings)){
			$updateSettings['timeUpdated'] = time();
			return DB::update("?:settings", $updateSettings, "1");
		}
	}
	public static function updateSecuritySettings($settings){
		if(isset($settings['allowedLoginIPsCount'])){
			DB::delete("?:allowed_login_ips", "1");
			if(!empty($settings['allowedLoginIPs'])){
				foreach($settings['allowedLoginIPs'] as $IP){
					DB::insert("?:allowed_login_ips", array('IP' => $IP));
				}
			}
		}
		$updateSettings = array();
		$currentGeneralSettings = Reg::get('settings');
		if(!empty($settings['httpAuth'])){		
			$settings['httpAuth']['username'] = trim($settings['httpAuth']['username']);
			$settings['general'] = $currentGeneralSettings;
			$settings['general']['httpAuth'] = $settings['httpAuth'];
			// $updateSettings['general'] = serialize($settings['general']);
		}else{
			$settings['general'] = $currentGeneralSettings;
			unset($settings['general']['httpAuth']);

		}
		$settings['general']['enableHTTPS'] =  $settings['enableHTTPS'];
		$updateSettings['general'] = serialize($settings['general']);

                updateOption('loginAuthType',  $settings['loginAuthType']);
                setHook('securitySettingsUpdate', $settings);
        
		if(!empty($updateSettings)){
			$updateSettings['timeUpdated'] = time();
			return DB::update("?:settings", $updateSettings, "1");
		}
		
	}
	
	public static function updateSettingsMerge($settings){//currently supports general setting (app settings) alone //changes will be updated, keeping old settings using array merge
		$updateSettings = array();
		if(!empty($settings['general'])){
			$currentGeneralSettings = Reg::get('settings');
			$settings['general'] = array_merge($currentGeneralSettings, $settings['general']);
			$updateSettings['general'] = serialize($settings['general']);
		}
		if(!empty($updateSettings)){
			return DB::update("?:settings", $updateSettings, "1");
		}
		
	}

	public static function initialSetupUpdateUsageStats($updateSettings){
		$settings['general'] = Reg::get('settings');
		if(!empty($updateSettings['enableHTTPS'])){
			$settings['general']['enableHTTPS'] = 1 ;
		} 
		if(!empty($updateSettings['sendAnonymous'])){
			$settings['general']['sendAnonymous'] = 1 ;
		} 
		$settings['general'] = serialize($settings['general']);
		if(!empty($settings)){
			$settings['timeUpdated'] = time();
			 return DB::update("?:settings", $settings, "1");
		}
	}

	public static function getSettings(){
		$settings =  array();
		$settings['allowedLoginIPs'] = DB::getFields("?:allowed_login_ips", "IP", "1", "IP");
		$settings['loginAuthType'] = getOption('loginAuthType');
		$settings['mainStagingFtpDetails'] = unserialize(getOption('mainStagingFtpDetails'));
		//for general settings.
		$settingsRow = DB::getRow("?:settings", "*", "1");
		$settings['general'] = @unserialize($settingsRow['general']);
                
		//for email, smtp settings
		$this_email_settings = getOption('emailSettings');
		if(!empty($this_email_settings)){
			$settings['emailSettings'] = @unserialize($this_email_settings);
        }
		//for user based settings
                $userID=$GLOBALS['userID'];
                $where = array(
		      		'query' =>  "userID = ':userID'",
		      		'params' => array(
		               ':userID'=>$userID
	   				)
				);
                $settingsRow = DB::getRow("?:users", "notifications", $where);
		$settings['notifications'] = @unserialize($settingsRow['notifications']);
		return $settings;
	}
	
	public static function getSettingsAll(){
		
		return array('settings' => self::getSettings(),
					 'accountSettings' => self::getAccountSettings($GLOBALS['userID']));
	}

	public static function updateEmailCronReqNotification(){
		updateOption('isUpdatePageEmailCronReqNotified', 1);
	}
	public static function getRecentHistory(){
		$limit = 10;
		$where = array(
		      		'query' =>  "showUser=':showUser' GROUP BY actionID ORDER BY historyID DESC LIMIT :limit",
		      		'params' => array(
		               ':showUser'=>'Y',
		               ':limit'=>$limit
	   				)
				);
		$actionIDs = DB::getFields("?:history", "actionID", $where);	
		if(empty($actionIDs)){ return array(); }
		$actionHistory = array();
		foreach($actionIDs as $actionID){
			$actionHistory[ $actionID ] = self::getActionStatus();
		}
		return $actionHistory;
	}
	
	public static function getActionStatus($actionID, $action=''){
		
		$where = array(
		      		'query' =>  "actionID = ':actionID' ORDER BY historyID ASC",
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
		$historyDatas = DB::getArray("?:history", "historyID, siteID, type, userID, action, status, error, microtimeAdded",$where, "historyID");		

		if(empty($historyDatas)){ return false;	}
		
		$totalRequest 			= count($historyDatas);
		$where = array(
		      		'query' =>  "actionID = ':actionID' AND status != ':status'",
		      		'params' => array(
		               ':actionID'=>$actionID,
		               ':status'=>'completed'
	   				)
				);
		$totalNonSuccessRequest = DB::getField("?:history", "count(status)", $where);
		$where = array(
		      		'query' =>  "actionID = ':actionID' AND status IN ('pending','initiated', 'running', 'processingResponse')",
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
		$totalPendingRequest = DB::getField("?:history", "count(status)", $where);
		$where = array(
		      		'query' => "actionID = ':actionID' AND status = ':status'",
		      		'params' => array(
		               ':actionID'=>$actionID,
		               ':status'=>'completed'
	   				)
				);
		$totalSuccessRequest = DB::getField("?:history", "count(status)", $where);
		$where = array(
		      		'query' =>  "actionID = ':actionID' AND status IN ('error','netError' )" ,
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
		$totalErrorRequest = DB::getField("?:history", "count(status)", $where );
			$where = array(
		      		'query' =>  "actionID = ':actionID' AND status IN ('retry')",
			      	'params' => array(
		               ':actionID'=>$actionID
					)
				);
		$totalRertyRequest 		= DB::getField("?:history", "count(status)", $where);
		$where = array(
		      		'query' =>  "actionID = ':actionID' AND status = ':status'",
		      		'params' => array(
		               ':actionID'=>$actionID,
		               ':status'=>'multiCallWaiting'
	   				)
				);
		$totalMultiCallRequest = DB::getField("?:history", "count(status)", $where);

		if($totalPendingRequest > 0 || $totalRertyRequest > 0 ){ $status = 'pending';  }
		elseif($totalMultiCallRequest > 0){ $status = 'multiCallWaiting'; }	 //Modified to get status= 'multiCallWaitig' in processQueue.tpl && view.tpl.
		elseif($totalNonSuccessRequest == 0){ $status = 'success'; }
		elseif($totalNonSuccessRequest < $totalRequest){ $status = 'partial'; }
		elseif($totalNonSuccessRequest == $totalRequest){ $status = 'error'; }
		
		$historyStatusSummary = array('total' => $totalRequest,
									  'pending' => $totalPendingRequest,
									  'nonSuccess' => $totalNonSuccessRequest,
									  'error' => $totalErrorRequest,
									  'success' => $totalSuccessRequest);
	
		$historyData = reset($historyDatas);
		$type = $historyData['type'];//getting type from first history only, assuming type is common for one actionID
		$action = $historyData['action'];
		$time = $historyData['microtimeAdded'];//getting time from first history ordered by historyID ASC
		$actionSitesCount = count($actionHistory);
		$userID = $historyData['userID']; //who creaed this history.
		
		$historyIDs = array_keys($historyDatas);
		
		$where = array(
		      		'query' =>  "H.actionID = ':actionID' AND HAD.historyID = :historyID",
		      		'params' => array(
		               ':actionID'=>$actionID,
		               ':historyID'=>'H.historyID'
	   				)
				);
		$historyAdditionalDatas = DB::getArray("?:history_additional_data HAD, ?:history H", "HAD.*, H.siteID, H.URL, H.microtimeInitiated,H.status AS mainStatus", $where);		
		if(empty($historyAdditionalDatas)){ return false; }
		
		$historyAdditionalDatasStatusArray = DB::getFields("?:history_additional_data HAD, ?:history H", "count(HAD.historyID), HAD.status", "H.actionID = '".$actionID."' AND HAD.historyID = H.historyID GROUP BY status", "status");		
		if(empty($historyAdditionalDatasStatusArray)){
			$historyAdditionalDatasStatusArray = array();
		}
		$historyAdditionalDatasStatusArray['total'] = count($historyAdditionalDatas);
		$where = array(
		      		'query' =>  "H.actionID = ':actionID' AND HAD.historyID = H.historyID GROUP BY detailedAction ",
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
		$detailedActions = DB::getArray("?:history_additional_data HAD, ?:history H", "count(DISTINCT HAD.historyID) as sitesCount, count(DISTINCT HAD.uniqueName) as detailedActionCount,  HAD.detailedAction, HAD.uniqueName", $where,"detailedAction");
		
		
		if($status == 'success'){//up to this line status only check connection is done successfully after this we will check task completed or not
			if(empty($historyAdditionalDatasStatusArray['success'])){
				$status = 'error';
			}
			elseif($historyAdditionalDatasStatusArray['total'] > $historyAdditionalDatasStatusArray['success']){
				$status = 'partial';
			}
		}
			
		$actionResult = array(
						'userID' => $userID,
						'status' => $status,
						'statusMsg' => $status,
						'actionID' => $actionID,
						'historyID' => $historyID,
						'statusSummary' => $historyAdditionalDatasStatusArray,
						'historyStatusSummary' => $historyStatusSummary,
						'detailedStatus' => $historyAdditionalDatas,
						'detailedActions' => $detailedActions, 
						'type' => $type,
						'action' => $action,
						'time' => (int)$time,
						'actionSitesCount' => $actionSitesCount,
						'errors' => $errors,
						);
		
		return $actionResult;
	}
	
	public static function getWaitData($params=array()){
        $waitActions= manageCookies::cookieGet('waitActions');
        if(empty($waitActions)) {
            $waitActions = array();
        }
		if(!empty($params)){
			foreach($params as $actionID => $value){
				if($value == 'sendData'){
					$waitActions[$actionID]['sendData'] = true;
				}
			}
            manageCookies::cookieSet('waitActions',$waitActions,array('expire'=>0));
			return true;
		}
		
			
        if(count($waitActions)==0) return false;
		$result = array();
		
		foreach($waitActions as $actionID => $waitAction){
			$sendData = false;
			$result[$actionID] = array();
			
			if( !empty($waitAction['sendData']) || ($waitAction['timeInitiated'] > 0 && $waitAction['timeInitiated'] < (time() - (5 *60))) ){
				$sendData = true;
			}
			$where = array(
		      		'query' =>  "actionID=':actionID'",
		      		'params' => array(
		               ':actionID'=>$actionID
       				)
    			);
			$totalRequest = DB::getField("?:history", "count(status)", $where);
			$where = array(
		      		'query' =>  "actionID = ':actionID' AND status IN ('pending', 'running', 'initiated', 'processingResponse')",
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
			$totalPendingRequest = DB::getField("?:history", "count(status)", $where);
			$where = array(
		      		'query' =>  "actionID = ':actionID' AND status = ':status'",
		      		'params' => array(
		               ':actionID'=>$actionID,
		               ':status'=>'completed'
	   				)
				);
			$totalSuccessRequest = DB::getField("?:history", "count(status)", $where);
			
			$result[$actionID]['total'] = $totalRequest;
			$result[$actionID]['loaded'] = $totalSuccessRequest;
			
			
			if($totalPendingRequest == 0) $sendData = true;
			
			if($sendData){
				$currentActionID = Reg::get('currentRequest.actionID');
				Reg::set('currentRequest.actionID', $actionID);
				$result[$actionID]['requiredData'] = $waitActions[$actionID]['requiredData'];			
				$result[$actionID]['data'] = self::requiredData($result[$actionID]['requiredData']);
				$result[$actionID]['actionResult'] = self::getActionStatus($actionID);
				Reg::set('currentRequest.actionID', $currentActionID);
			}
			
			if($sendData || $waitAction['timeExpiresFromSession'] < time()){
				unset($waitActions[$actionID]);
			}			
		}
		manageCookies::cookieSet('waitActions',$waitActions,array('expire'=>0));
		return $result;
		
	}	
	
	public static function getHistoryPageHTML($args){
		$searching = false;
		$itemsPerPage = 20;		
		$page = (isset($args['page']) && !empty($args['page'])) ? $args['page'] : 1;
		$where = "showUser='Y'";
		if(!empty($args['dates'])){
			$dates 		= explode('-', $args['dates']);
			$fromDate 	= strtotime(trim($dates[0]));
			$toDate		= strtotime(trim($dates[1]));
			if(!empty($fromDate) && !empty($toDate) && $fromDate != -1 && $toDate != -1){
				$searching = true;
				$toDate += 86399;
				$where .= " AND microtimeAdded >= ".DB::esc($fromDate)." AND  microtimeAdded <= ".DB::esc($toDate)." ";
			}
		}
		$getKeyword = "";
		if(!empty($args['getKeyword'])){
			$searching = true;
			$keyword = "'".implode("','", explode(',', DB::esc($args['getKeyword']) ) )."'" ;
			$getKeyword = " AND type IN (".$keyword.") ";
		}
		if (!empty($args['actionID'])) {
			$where .= " AND actionID = '".DB::esc($args['actionID'])."' ";
		}
		if(!empty($args['userID'])){
			$searching = true;
			$where .= " AND userID = '".DB::esc($args['userID'])."' ";
		}
		$where2 = " ";
		if(empty($args['searchByUser']) )
			setHook('historyHTML', $where2);

		$total = DB::getField("?:history", "SQL_CALC_FOUND_ROWS actionID ", $where2.$where.$getKeyword. " GROUP BY actionID");
		$total = DB::getField("SELECT FOUND_ROWS()");
		$limitSQL = paginate($page, $total, $itemsPerPage);
		
		$actionIDs = DB::getFields("?:history", "actionID ", $where2.$where.$getKeyword. " GROUP BY actionID ORDER BY historyID DESC ".$limitSQL);
		if(!empty($actionIDs)){ 
			$actionsHistoryData = array();
			foreach($actionIDs as $actionID){
				$actionsHistoryData[ $actionID ] = self::getActionStatus($actionID);
			}
		}
		if( $searching ){
			$searching = $total;
		}
		$HTML = TPL::get('/templates/history/view.tpl.php', array('actionsHistoryData' => $actionsHistoryData,'searching'=>$searching));
		
		return $HTML;
	}
	
	
	public static function removeFavicon($siteID){
		include_once(APP_ROOT."/includes/favicon.php");
		Favicon::removeFavicon($siteID);
	}
	public static function getSiteFavicon($siteID){
		include_once(APP_ROOT."/includes/favicon.php");
		return Favicon::refreshFaviconSite($siteID);
	}
	public static function getHistoryPanelHTML(){
		$itemsPerPage = 10;
		
		$where = '';
		
		setHook('historyHTML', $where);
		$where2 = array(
		      		'query' =>  $where. " showUser=':showUser' GROUP BY actionID ORDER BY historyID DESC LIMIT :itemsPerPage",
		      		'params' => array(
		               ':showUser'=>'Y',
		               ':itemsPerPage'=>$itemsPerPage
	   				)
				);
		$actionIDs = DB::getFields("?:history", "actionID", $where2);
		if(empty($actionIDs)){ $actionIDs = array(); }
		$actionsHistoryData = array();
		$showInProgress = false;
		foreach($actionIDs as $actionID){
			$actionsHistoryData[ $actionID ] = self::getActionStatus($actionID);
			if(($actionsHistoryData[ $actionID ]['status'] == 'pending')||($actionsHistoryData[ $actionID ]['status'] == 'multiCallWaiting')){ $showInProgress = true; }
		}

		$HTML = TPL::get('/templates/history/processQueue.tpl.php', array('actionsHistoryData' => $actionsHistoryData, 'showInProgress' => $showInProgress));
		
		return $HTML;
	}

	public static function getLogPageHTML($args){
		$where = " ";
		$getKeyword = " ";
		$where2 = " ";
		$itemsPerPage = 20;
		$page = (isset($args['page']) && !empty($args['page'])) ? $args['page'] : 1;
		$total = DB::getField("?:login_logs", "SQL_CALC_FOUND_ROWS ID ", 1);
		$total = DB::getField("SELECT FOUND_ROWS()");
		$limitSQL = paginate($page, $total, $itemsPerPage,'logPagination');
		if (!empty($args['ID'])) {
			$where = array(
	      		'query' =>  "ID=':ID'",
	      		'params' => array(
	               ':ID'=>$args['ID']
					)
			);
			$logDetails = DB::getArray("?:login_logs", "ID,email,accessLevel,time,browserInfo,loginAuthType,IP,error,loginAttemptStatus,protocol,loginRedirect" ,$where);
		}else{
			$where = array(
	      		'query' =>  "1 ORDER BY ID DESC :limit",
	      		'params' => array(
	               ':limit'=>$limitSQL
					)
			);
			$logDetails = DB::getArray("?:login_logs", "ID,email,accessLevel,time,browserInfo,loginAuthType,IP,error,loginAttemptStatus,protocol,loginRedirect",$where);
		}
		$HTML = TPL::get('/templates/history/viewLogHistory.tpl.php', array('logDetails' => $logDetails));
		return $HTML;
	}
	
	public static function addHide($params){
		
		if(empty($params)){
			 return false; 
		}
		foreach($params as $siteID => $value){			
			DB::insert("?:hide_list", array('type' => $value['type'], 'siteID' => $siteID, 'name' => $value['name'], 'URL' => $value['path']));			
		}
	}
	
	public static function getHide(){
	
		$getHide = DB::getArray("?:hide_list", "*", "1");
		$hide = array();
		foreach($getHide as $v){
			$hide[$v["siteID"]][] = array('type' => $v["type"], 'name' => $v["name"], 'URL' => $v["URL"]);	
		}
		return $hide;
	}
	
	public static function removeHide($params){
		
		if(empty($params)){
			 return false; 
		}		
		foreach($params as $siteID => $value){
			$where = array(
	          'query' => "type = ':type' AND siteID = ':siteID' AND URL  = ':URL' ",
	          'params' => array(
	               ':type'=>$value['type'],
	               ':siteID'=>$siteID,
	               ':URL'=>$value['path']

	           )
	        );
			$isDone = DB::delete("?:hide_list", $where);
		}
		return $isDone;
	}
	
	public static function addFavourites($params){
		if(empty($params)){
			 return false; 
		}
		if(!empty($params['directUpload'])){
			$params['URL'] = addURLHeader($params);
		}
		else if (!empty($params['folderPath'])) {
			copyUploadsToFavoritesFolder($params);
		}
		
		return DB::insert("?:favourites", array('type' => $params['type'], 'name' => $params['name'], 'URL' => $params['URL'], 'slug' => $params['slug']));
	}

	public static function addToFavouritesGroup($params){
		if(empty($params)){
			 return false; 
		}
		$groupID =  DB::insert("?:favourite_groups", array('type' => lcfirst($params['type']), 'name' => $params['gname']));
		foreach ($params['items'] as $item) {
			DB::insert("?:favourites_groups_map", array('groupID' => $groupID, 'favouriteID' => ltrim ($item, 'f')));
		}
	}
	
	public static function getFavourites(){
		
		$getFavourites = DB::getArray("?:favourites", "*", 1);
		$favourites = array();
		foreach($getFavourites as $v){
			$favourites[$v["type"]][] = array('name' => $v["name"], 'ID' => $v['ID'], 'type' => $v['type'], 'URL' => $v["URL"], 'slug' => $v["slug"], 'groupID' => $v['groupID']);			
		}
		return $favourites;
	}
	
	public static function removeFavourites($params){
		$where1 = array(
			'query' =>  "type = ':type' AND URL  = ':URL'",
			'params' => array(
				':type' => $params['type'],
				':URL' => $params['URL'],
				)
			);
		$favouriteID = DB::getField('?:favourites', 'ID', $where1);
		DB::delete("?:favourites", $where1);
		$where2 = array(
			'query' =>  "favouriteID = ':favouriteID'",
			'params' => array(
				':favouriteID' => $favouriteID,
				)
			);
		DB::delete("?:favourites_groups_map", $where2);
	}
	
	public static function removeFavouritesGroups($params){
		$where = array(
			'query' =>  "groupID = ':groupID'",
			'params' => array(
				':groupID' => $params['groupID'],
				)
			);
		DB::delete("?:favourite_groups", $where);
		DB::delete("?:favourites_groups_map", $where);
	}
	public static function updateAccountSettings($params){
		$userData = array();
		$userID = $GLOBALS['userID'];
		$where = "userID = ".DB::esc($userID);
		
		if( !empty($params['currentPassword']) && !empty($params['newPassword']) ){
			
			$where .= " && password='".DB::esc(sha1($params['currentPassword']))."'";
			$isPasswordCorrect = DB::getExists("?:users", "userID", $where);
			if(!$isPasswordCorrect){
				return array('status' => 'error', 'error' => 'invalid_password', 'errorArray' => array('currentPassword' => 'invalid'));
			}
			
			$userData['password'] = sha1($params['newPassword']);
		}
		if( !empty($params['email']) ){
			$userData['email'] = $params['email'];
		}
		if(empty($userData)){
			return array('status' => 'error', 'error' => 'empty', 'errorArray' => array('currentPassword' => 'invalid', 'email' => 'invalid'));
		}
		
		$isUpdated = DB::update("?:users", $userData, $where);
		if($isUpdated){
			return array('status' => 'success', 'error' => '');	
		}
		return array('status' => 'error', 'error' => 'db_error');
	}
	
	public static function getAccountSettings($userID){
		$where = array(
		      		'query' =>  "userID = ':userID'",
		      		'params' => array(
		               ':userID'=>$userID
	   				)
				);
		return DB::getRow("?:users", "email", $where);
	}

	public static function getWPRepositoryHTML($params){
		
		$searchVar = $params['searchVar'];
		$searchItem = $params['searchItem'];
		$type = $params['type'];
		if($type =='plugins')
		{
			$action='query_plugins';
			$URL= 'http://api.wordpress.org/plugins/info/1.0/';
		}
		if($type=='themes')
		{
			$action='query_themes';
			$URL= 'http://api.wordpress.org/themes/info/1.0/';
		}
		$args = (object)$args;
		//$args->search= 'WP ecommerce';
		if($searchVar==1)
		$args->search=$searchItem;
		else
		$args->browse=$searchItem;
		$args->per_page=30;
		$args->page=1;
		$args->fields['downloadlink'] = true;
		$Array['action']=$action;
		$Array['request']=serialize($args);
		
	
		$return = unserialize(repoDoCall($URL,$Array));
		
		$return=$return->{$params['type']};
		foreach($return as $item)
		{
			//Limit description to 400char, and remove any HTML.
			$description = strip_tags( $item->description);
			if ( strlen( $description ) > 400 )
			{
				if(function_exists('mb_substr'))
					$description = mb_substr( $description, 0, 400 ) . '&#8230;';
				else
					$description = substr( $description, 0, 400 ) . '&#8230;';
			}
			//remove any trailing entities
			$description = preg_replace( '/&[^;\s]{0,6}$/', '', $description );
			//strip leading/trailing & multiple consecutive lines
			$description = trim( $description );
			$description = preg_replace( "|(\r?\n)+|", "\n", $description );
			//\n => <br>
			$description = nl2br( $description );	
			$where = array(
		      		'query' =>  "type = ':type' AND name = ':name'",
		      		'params' => array(
		               ':type'=>$type,
		               ':name'=>$item->name
	   				)
				);
			$existFav = DB::getField("?:favourites", "count(ID)", $where);
						
			if($type=='plugins')
			{
				$content = $content.'<div class="tr"><div class="name">'.$item->name.'<div class="wp_repository_search_results_actions"><a class="installItem multiple" dlink='.$item->download_link.' plugin_themes_slug="'.$item->slug.'">Install</a>';
				$content = $content.'<a href="http://wordpress.org/plugins/'.$item->slug.'/" target="_blank">Details</a>';
				if($existFav == 1){
				$content = $content.'<a class="addToFavorites disabled" style="position:relative">Favourite</a>'; 
				}
				else 
				$content = $content.'<a class="addToFavorites" utype="'.$type.'" iname="'.$item->name.'" islug="'.$item->slug.'" dlink="'.$item->download_link.'" >Add to Favorites</a>';
				$content = $content.'</div></div> <div class="version">'.$item->version.'</div> <a class="rating" title="(based on '.$item->num_ratings.' ratings)"><div class="rating_fill" style="width:'.$item->rating.'%;"></div><div class="stars"></div></a>   <div class="descr">'.$description.'</div>
                  <div class="clear-both"></div>
                </div>';
			}
			else
			{
				$content=$content.'<div class="theme_column"> <div class="thumb" preview="'.$item->preview_url.'"><div class="icon_preview rep_sprite_backup">Preview</div><div class="btn_preview"></div><img src="'.$item->screenshot_url.'"  /></div><div class="theme_name droid700">'.$item->name.'</div>
                <div class="wp_repository_search_results_actions"><a class="installItem multiple" dlink='.$item->download_link.'  plugin_themes_slug="'.$item->slug.'">Install</a>';
				
			$content=$content.'<a href="http://wordpress.org/themes/'.$item->slug.'/" target="_blank">Details</a>';
			  if($existFav == 1){
				$content = $content.'<a class="addToFavorites disabled" style="position:relative">Favourite</a>'; 
				}
	 			else
			$content=$content.'	<a class="addToFavorites" utype="'.$type.'" iname="'.$item->name.'" islug="'.$item->slug.'" dlink="http://wordpress.org/themes/download/'.$item->slug.'.'.$item->version.'.zip" >Add to Favorites</a>';
			$content = $content.'</div>
                <div class="clear-both"></div>
                <div class="theme_descr">'.$description.'</div>
              </div>';
			
			}
		}
		return utf8_encode($content);
	}
	
	public static function getFavDownloadLinks($params){
		$linksArray = array();
		foreach($params['searchItem'] as $key => $value)
		{
		
		    //$searchVar = $params['searchVar'];
			
			//$is_url = parse_URL($searchItem);
		
			if(!empty($value['slug']))
			{
				$searchItem = $value['slug'];
				$type = $params['type'];
				if($type =='plugins')
				{
					$action='plugin_information';
					$URL= 'http://api.wordpress.org/plugins/info/1.0/';
				}
				if($type=='themes')
				{
					$action='theme_information';
					$URL= 'http://api.wordpress.org/themes/info/1.0/';
				}
				$args = (object)$args;
				//$args->search= 'WP ecommerce';
				
				$args->slug=$searchItem;
				//$args->page=1;
				$args->fields['downloadlink'] = true;
				$Array['action']=$action;
				$Array['request']=serialize($args);
				
			
				$return = unserialize(repoDoCall($URL,$Array));
				if(!empty($return->download_link))
				{
					$linksArray[$key] = $return->download_link;
				}
				else{
					$linksArray[$key] = $value['downloadLink'];
				}				
			}
			else
			{//this $value should be URL
				$linksArray[$key] = $value['downloadLink'];
			}
			//$return=$return->$params['type'];
		}
		return $linksArray;
	}
	
	public static function installNotInstalledPlugin($params)
	{
		$plugin_slug = $params['plugin_slug'];
		$siteID = $params['siteID'];
		//$searchItem = $params['searchItem'];
		$type = $params['type'];
		if($type =='plugins')
		{
			$action='plugin_information';
			$URL= 'http://api.wordpress.org/plugins/info/1.0/';
		}
		if($type=='themes')
		{
			$action='theme_information';
			$URL= 'http://api.wordpress.org/themes/info/1.0/';
		}
		$args = (object)$args;
		//$args->search= 'WP ecommerce';
		/* if($searchVar==1)
		$args->search=$searchItem;
		else
		$args->browse=$searchItem;
		$args->per_page=30;
		$args->page=1; */
		$args -> slug = $plugin_slug;
		$args->fields['downloadlink'] = true;
		$Array['action']=$action;
		$Array['request']=serialize($args);
		

		$link = unserialize(repoDoCall($URL,$Array));
		$return = array($link,$siteID);
		//$return=$return->$params['type'];
		
		return $return;
		
	}
	public static function updateInitialSetupCompletedStatus($updateValue){
		updateOption('isInitialSetupCompleted', $updateValue);
	}
	public static function isInitialSetupCompleted(){
		if (getOption('isInitialSetupCompleted')) {
		 	return 'true';
		 }
		return 'false';
	}
	
	public static function getUserHelp(){
		$where = array(
		      		'query' =>  "userID = ':userID'",
		      		'params' => array(
		               ':userID'=>$GLOBALS['userID']
	   				)
				);
		$help = DB::getField("?:users", "help", $where);
		if(empty($help)){
			return array();	
		}
		return (array)unserialize($help);
	}
	
	public static function updateUserHelp($params){
		$oldHelp = self::getUserHelp();
		$params = array_merge($oldHelp, (array)$params);
		$where = array(
		      		'query' =>  "userID = ':userID'",
		      		'params' => array(
		               ':userID'=>$GLOBALS['userID']
	   				)
				);
		$help = DB::update("?:users", array('help' => serialize($params)), $where);
		return $help;
	}
	
	public static function getReportIssueData($actionID){
		$issue = getReportIssueData($actionID);
		$issue['report'] = serialize($issue['report']);
		return $issue;
	}
	
	public static function updatesNotificationMailTest(){
		return updatesNotificationMailSend(true);	
	}
	

	public static function getClientUpdateAvailableSiteIDs(){
		
		$rawSiteStats = self::getRawSitesStats(array(), $GLOBALS['userID']);

		if(function_exists('multiUserGetSites')){
					$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$GLOBALS['userID']
		   				)
					);
                    $userAccess=DB::getRow("?:users", "permissions,accessLevel", $where);
                    if(($userAccess['accessLevel']!='admin')&&!empty($userAccess['permissions']))
                    {
                        $permissions=  unserialize($userAccess['permissions']);
                        if(!in_array('updates', $permissions['access']))
                        {
                            return FALSE;
                        }
                    }
		}
		foreach($rawSiteStats as $siteID => $statsArray){
               
			$stats = $statsArray['stats'];

			//check iwp-client plugin have any updates
			if( !empty($stats['client_new_version']) || version_compare($stats['client_version'], '0.1.4') != 1 ){
				if(!isset($clientUpdates)){
					$clientUpdates = array();
				}				
				
				if( !empty($stats['client_new_version']) && version_compare($stats['client_version'], $stats['client_new_version']) == -1 ){//fixed repeated Client update popup
					if(!isset($clientUpdates['clientUpdateVersion']) || version_compare($clientUpdates['clientUpdateVersion'], $stats['client_new_version']) == -1){
						$clientUpdates['clientUpdateVersion'] = $stats['client_new_version'];
						$clientUpdates['clientUpdatePackage'] = base64_encode($stats['client_new_package']);
					}
				}
				elseif( version_compare($stats['client_version'], '0.1.4') != 1 ){
					$clientUpdates['clientUpdateVersion'] = '1.0.0';
					$clientUpdates['clientUpdatePackage'] = base64_encode('http://downloads.wordpress.org/plugin/iwp-client.zip');
				}
			}
		}
		
		$clientPluginBetaUpdate = getOption('clientPluginBetaUpdate');
		if(!empty($clientPluginBetaUpdate)){
			$clientPluginBetaUpdate = unserialize($clientPluginBetaUpdate);
			if(!empty($clientPluginBetaUpdate['version']) && !empty($clientPluginBetaUpdate['downloadURL'])){
				if(empty($clientUpdates['clientUpdateVersion']) || (!empty($clientUpdates['clientUpdateVersion']) && version_compare($clientUpdates['clientUpdateVersion'], $clientPluginBetaUpdate['version']) == -1)){
					$clientUpdates['clientUpdateVersion'] = $clientPluginBetaUpdate['version'];
					$clientUpdates['clientUpdatePackage'] = base64_encode($clientPluginBetaUpdate['downloadURL']);			
				}
			}
		}
                
        $clientPluginUpdate = getOption('clientPluginUpdate');
		if(!empty($clientPluginUpdate)){
			$clientPluginUpdate = unserialize($clientPluginUpdate);
			if(!empty($clientPluginUpdate['newVersion']) && !empty($clientPluginUpdate['updateDetails'][$clientPluginUpdate['newVersion']]['downloadLink'])){
				if(empty($clientUpdates['clientUpdateVersion']) || (!empty($clientUpdates['clientUpdateVersion']) && version_compare($clientUpdates['clientUpdateVersion'], $clientPluginUpdate['newVersion']) == -1)){
					$clientUpdates['clientUpdateVersion'] = $clientPluginUpdate['newVersion'];
					$clientUpdates['clientUpdatePackage'] = base64_encode($clientPluginUpdate['updateDetails'][$clientPluginUpdate['newVersion']]['downloadLink']);			
					$clientUpdates['clientUpdateChangeLog'] = $clientPluginUpdate['updateDetails'][$clientPluginUpdate['newVersion']]['changeLog'];			
				}
			}
		}
		
		if(empty($clientUpdates['clientUpdateVersion'])){
        	return false;
		}
		
		$clientUpdates['siteIDs'] = array();
		foreach($rawSiteStats as $siteID => $statsArray){			
			$stats = $statsArray['stats'];
			//check iwp-client plugin have any updates
			$where = array(
		      		'query' =>  "siteID = ':siteID' AND (network = 0 OR (network = 1 AND parent = 1)) AND (connectionStatus = 1 OR connectionStatus = 2)",
		      		'params' => array(
		               ':siteID'=>$siteID
	   				)
				);
			if(version_compare($stats['client_version'], $clientUpdates['clientUpdateVersion']) == -1  && DB::getExists("?:sites", "siteID", $where)){
				$clientUpdates['siteIDs'][] = $siteID;
			}
		}
			
		return (!empty($clientUpdates['siteIDs']) ? $clientUpdates : false);
		
	}
	
	public static function generalCheck(&$finalResponse){
		
		if($updateAvailable = checkUpdate()){
			if( getOption('updateHideNotify') != $updateAvailable['newVersion'] && getOption('updateNotifySentToJS') != $updateAvailable['newVersion'] ){
				$finalResponse['updateAvailable'] = $updateAvailable;
				updateOption('updateNotifySentToJS', $updateAvailable['newVersion']);
			}
		}

		$notifications = getNotifications(true);
		if(!empty($notifications)){
			$finalResponse['notifications'] = $notifications;
		}
		
		$waitData = self::getWaitData();
		if(!empty($waitData)){
			$finalResponse['data']['getWaitData'] = $waitData;
		}
		$fireQueue = self::processFireQueue();
		if (!empty($fireQueue) && is_array($fireQueue)) {
			$finalResponse['fireQueue'] = $fireQueue;
		}
		$alertCount = getAddonAlertCount();
		//$cookieAlertCount = manageCookies::cookieGet('addonAlertCount');
                $cookieAlertCount = getOption('addonAlertCount');
		if($cookieAlertCount !== $alertCount){
			//manageCookies::cookieSet('addonAlertCount',$alertCount,array('expire'=>0));
                        updateOption('addonAlertCount',  $alertCount);
			$finalResponse['addonAlertCount'] = $alertCount;
		}
		
	}
	
	public static function updateHideNotify($version){//IWP update
		return updateOption('updateHideNotify', $version);
	}
	
	public static function isUpdateHideNotify(){
		$updateAvailable = checkUpdate(false, false);
		if(!empty($updateAvailable)){
			if($updateAvailable['newVersion'] == getOption('updateHideNotify')){
				return true;	
			}
		}
		return false;
	}
	
	public static function isAddonSuiteLimitExceededAttempt() {
		return(jsonEncoder(Reg::get('addonSuiteLimitExceededAttemp')));
	}
	
	public static function getAddonSuiteMiniLimit() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		if(empty($suiteDetails) and !is_array($suiteDetails)) return(0);
		return($suiteDetails['addonSuiteMiniLimit']);
	}
	
	public static function getIDForAddonSuite() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		if(empty($suiteDetails) and !is_array($suiteDetails)) return(0);
		return($suiteDetails['IDForAddonSuite']);		
	}	
	
	public static function getIDToBeUpgradedFromMini() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		if(empty($suiteDetails) and !is_array($suiteDetails)) return(0);
		return($suiteDetails['IDToBeUpgradedFromMini']);		
	}
	
	public static function getPriceForSuiteUpgradedFromMini() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		return($suiteDetails['priceForSuiteUpgradedFromMini']);		
	}	
	
	public static function getPriceForAddonSuite() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		return($suiteDetails['priceForAddonSuite']);		
	}
	
	public static function getCurrentTimestamp() {
		return(time());
	}
	
	public static function getAddonSuiteOrMiniPurchasedDate() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		return($suiteDetails['addonSuiteOrMiniPurchasedDate']);		
	}
	
	public static function isAddonSuiteMiniCancelMessage() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		if(empty($suiteDetails) and !is_array($suiteDetails)) return('');
		
		$addonSuiteMiniActivity=$suiteDetails['addonSuiteMiniActivity'];
		$cancelMessageFlag=$suiteDetails['cancelMessageFlag'];
		
		if($addonSuiteMiniActivity=='cancelled' && $cancelMessageFlag!='shown') {
			$suiteDetails['cancelMessageFlag']='shown';
			updateOption('suiteDetails',serialize($suiteDetails));
			return('show');
		}
		return('');
	}
	
	public static function getAddonSuiteMiniActivity() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		
		if(empty($suiteDetails) and !is_array($suiteDetails)) return('');
		
		$addonSuiteMiniActivity=$suiteDetails['addonSuiteMiniActivity'];
		$cancelMessageFlag=$suiteDetails['cancelMessageFlag'];
		
		if($addonSuiteMiniActivity=='cancelled' && $cancelMessageFlag!='shown') uninstallAddons($suiteDetails['addonSuiteMiniAddons'],0);
		return($addonSuiteMiniActivity);
	}
	
	public static function checkIsMiniExpired() {
		$suiteDetails = unserialize(getOption('suiteDetails'));
		$installedAddons = getInstalledAddons(true);
		
		if(empty($suiteDetails) and !is_array($suiteDetails)) return(0);
		
		
		if($suiteDetails['addonSuiteOrMiniPurchased']=='addonSuiteMini' && $suiteDetails['addonSuiteMiniActivity']=='installed') {
			foreach($installedAddons as  $addon){
				if($addon['slug']!='multiUser' && $addon['isValidityExpired'] && time()-$addon['validityExpires']>=604800) return(1);
			}
		}
		return(0);
	}
	
	public static function checkIsAddonSuiteMiniLimitExceeded($mode='') { 
		
		$suiteDetails = unserialize(getOption('suiteDetails'));

		if(empty($suiteDetails) and !is_array($suiteDetails)) return(0);
		
		$addonSuiteMiniLimit=$suiteDetails['addonSuiteMiniLimit'];
		$addonSuiteOrMiniPurchased=$suiteDetails['addonSuiteOrMiniPurchased'];		
		
		$siteCount = DB::getField("?:sites", "count(*)", 1);	
		
		if(in_array($mode,array('addonSuiteLimitExceededIllegally')) && $siteCount>$addonSuiteMiniLimit && $addonSuiteOrMiniPurchased=='addonSuiteMini') return(1);
		else if(!in_array($mode,array('addonSuiteLimitExceededAttemp','addonSuiteLimitExceededIllegally')) && $siteCount>=$addonSuiteMiniLimit && $addonSuiteOrMiniPurchased=='addonSuiteMini') return(1);
		else return(0);
	}
	
	public static function forceCheckUpdate(){
                if(userStatus()=="admin")
                    return checkUpdate(true);
                else 
                    return false;
	}
	
	public static function sendReportIssue($params){
		return sendReportIssue($params);
	}
	
	public static function getResponseMoreInfo($historyID){
		return getResponseMoreInfo($historyID);
	}
	
	public static function updateSite($params){
		
		if(empty($params['siteID'])){ return false; }
		if (empty($params['siteName'])) {
			$params['siteName'] = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', $params['URL']);
		}
		$siteData = array( "adminURL" 		=> $params['adminURL'],
						   "adminUsername"	=> $params['adminUsername'],
						   "name"	        => $params['siteName'],
						   "URL"			=> $params['URL'],
						   "connectURL"		=> $params['connectURL'],
						  ); // save data
						  
		if(!empty($params['httpAuth']['username'])){
			  $siteData['httpAuth'] = serialize($params['httpAuth']);
		}
		else{
			$siteData['httpAuth'] = '';
		}
		
		if(!empty($params['callOpt'])){
			$siteData['callOpt'] = serialize($params['callOpt']);
		}
		else{
			$siteData['callOpt'] = '';
		}
		$where = array(
		      		'query' =>  "siteID = ':siteID'",
		      		'params' => array(
		               ':siteID'=>$params['siteID']
	   				)
				);
	  
		$isDone = DB::update('?:sites', $siteData, $where); 
		
		if($isDone){
			panelRequestManager::addSiteSetGroups($params['siteID'], $params['groupsPlainText'], $params['groupIDs']);	
			//Updating the userAccess
			if(function_exists('updateUserAccessOnSiteEdit')){
				$managerIDs = (isset($params['managerID']))?$params['managerID']:array();
				updateUserAccessOnSiteEdit($params['siteID'],$managerIDs);
		}
		}
		return $isDone;
	}	
	public static function repositoryTestConnection($params){
		return repositoryTestConnection($params);
	}
	public static function FTPTestConnection($params){
		return FTPTestConnection($params);
	}
	public static function getAddonsPageHTML(){
		
		$data = array();
		$data['installedAddons'] = getInstalledAddons(true);
		$data['newAddons'] = getNewAddonsAvailable();
		$data['promoAddons'] = getPromoAddons();
		$data['promos'] = getOption('promos');
		$data['isAppRegistered'] = isAppRegistered();
		$data['isMiniExpired'] = panelRequestManager::checkIsMiniExpired();
		$data['priceForSuiteUpgradedFromMini'] = panelRequestManager::getPriceForSuiteUpgradedFromMini();
		$data['priceForAddonSuite'] = panelRequestManager::getPriceForAddonSuite();		
		
		$suiteDetails = unserialize(getOption('suiteDetails'));

		if(empty($suiteDetails) and !is_array($suiteDetails)) {
			$data['addonSuiteMiniLimit'] = 0;
			$data['addonSuiteOrMiniPurchased'] = $data['addonSuiteMiniActivity'] = $data['IDToBeUpgradedFromMini']=$data['IDForAddonSuite']='';			
		} else {
			$data['addonSuiteMiniLimit'] = $suiteDetails['addonSuiteMiniLimit'];	
			$data['addonSuiteOrMiniPurchased'] = $suiteDetails['addonSuiteOrMiniPurchased'];	
			$data['addonSuiteMiniActivity'] = $suiteDetails['addonSuiteMiniActivity'];
			$data['IDToBeUpgradedFromMini'] = $suiteDetails['IDToBeUpgradedFromMini'];
			$data['IDForAddonSuite'] = $suiteDetails['IDForAddonSuite'];
		}		
		
		$HTML = TPL::get('/templates/addons/view.tpl.php', $data);
		return $HTML;
	}
	
	public static function activateAddons($params){
		return activateAddons($params['addons']);
	}
	
	public static function deactivateAddons($params){
		return deactivateAddons($params['addons']);
	}
	
	public static function IWPAuthUser($params){
		
		$registerURL = IWP_SITE_URL.'app-login/';
		$noCache      = '?no-cache-iwp='.md5(microtime(true).uniqid('',true).substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(20,60)));
		$registerURL .= $noCache;

		$data = array('appInstallHash' => APP_INSTALL_HASH,
					  'installedHash' => getInstalledHash());

		$params['appDetails'] = base64_encode(serialize($data));
		
		list($rawResponseData, , , $curlInfo)  = doCall($registerURL, $params, $timeout=30, array('normalPost' => 1));

		$cURLErrors = new cURLErrors($curlInfo);
		if(!$cURLErrors->isOk() && $curlInfo['info']['http_code'] != 403){
			$errorResponse = array();
			$errorResponse['netError'] = 1;
			$errorResponse['errorMsg'] = $cURLErrors->getErrorMsg();
			return $errorResponse;
		}
		return json_decode($rawResponseData);
	}
	
	public static function runOffBrowserLoad($params){
		
		if(Reg::get('settings.executeUsingBrowser') != 1){//using fsock
			callURLAsync(APP_FULL_URL.EXECUTE_FILE, array('runOffBrowserLoad' => 'true'));
		}
		elseif(Reg::get('settings.executeUsingBrowser') == 1){
			Reg::set('currentRequest.runOffBrowserLoad', 'true');
		}
	}
	
	public static function runWhileBrowserIdle($params = NULL){

		if(Reg::get('settings.executeUsingBrowser') != 1){//using fsock
			callURLAsync(APP_FULL_URL.EXECUTE_FILE, array('runWhileBrowserIdle' => 'true'));
		}
		elseif(Reg::get('settings.executeUsingBrowser') == 1){
			Reg::set('currentRequest.runWhileBrowserIdle', 'true');
		}
	}

	public static function getSendNextAjaxCallAfter(){
		$time = time();
		$where = array(
		      		'query' =>  "(H.status IN('writingRequest','pending','multiCallWaiting','initiated','running','processingResponse') OR (H.status = ':status' AND H.timescheduled <= :timescheduled AND H.timescheduled > 0)) LIMIT 1",
		      		'params' => array(
		               ':status'=>'scheduled',
		               ':timescheduled'=>($time - 120)
	   				)
				);
		$isTaskActive = DB::getExists("?:history H", "H.historyID", $where);
        $slowDownAjaxCallFrom = manageCookies::cookieGet('slowDownAjaxCallFrom');
		
		if($isTaskActive){
			manageCookies::cookieUnset('slowDownAjaxCallFrom');
			return 0;
		}
		elseif(!empty($slowDownAjaxCallFrom)) {
			if($slowDownAjaxCallFrom['sec60'] < $time){
				return 60;
			}
			elseif($slowDownAjaxCallFrom['sec30'] < $time){
				return 30;
			}
			elseif($slowDownAjaxCallFrom['sec10'] < $time){
				return 10;
			}
			
		}
		else{
            $slowDownAjaxCallFrom = array();
			$slowDownAjaxCallFrom['sec10'] = $time + 12;//two calls of 10 sec
			$slowDownAjaxCallFrom['sec30'] = $time + 35;//two calls of 30 sec
			$slowDownAjaxCallFrom['sec60'] = $time + 105;//from there 60 sec each call
            manageCookies::cookieSet('slowDownAjaxCallFrom',$slowDownAjaxCallFrom,array('expire'=>0));
			return 0;
		}
		
		return 0;//safe		
	}
	
	public static function getSystemCronRunningFrequency(){
		return getSystemCronRunningFrequency();
	}
	
	// public static function terminateBackupMulticallProcess($params){
	// 	if(!empty($params['actionID'])){
	// 		$historyDatas = DB::getArray("?:history", "historyID, siteID", "actionID = '".$params['actionID']."' AND (status = 'multiCallWaiting' OR status = 'scheduled') "); 
			
	// 		if(!empty($historyDatas) && is_array($historyDatas)){
	// 			foreach($historyDatas as $key => $historyData){
	// 				updateHistory(array("status" => "error", "error" => "task_killed"), $historyData['historyID'], array("status" => "error", "errorMsg" => "Task killed by user"));
	// 				$allParams = array('action' => 'removeBackup', 'args' => array('params' => array('resultID' => $historyData['historyID']), 'siteIDs' => array($historyData['siteID']), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true, 'runCondition' => true)));
	// 				panelRequestManager::handler($allParams);
	// 			}
	// 		}
	// 	}
	// }

	public static function terminatePendingProcess($params){
		$condition = "";
		if(isset($params['actionID']) && !empty($params['actionID'])){
			DB::esc($params['actionID']);
			$condition = "actionID = '".$params['actionID']."'";
		}elseif(isset($params['historyID']) && !empty($params['historyID'])){
			DB::esc($params['historyID']);
			$condition = "historyID = '".$params['historyID']."'";
		}		
		if($condition != ""){

			$historyDatas = DB::getArray("?:history", "historyID, siteID", $condition." AND status NOT IN ('completed','error','netError') "); 
			
			if(!empty($historyDatas) && is_array($historyDatas)){
				foreach($historyDatas as $key => $historyData){
					updateHistory(array("status" => "error", "error" => "task_killed"), $historyData['historyID'], array("status" => "error", "errorMsg" => "Task killed by user"));
					if($params["multiCall"]){
						$historyAdditionalData = array();
						$historyAdditionalData[] = array('uniqueName' => "Backup Now", 'detailedAction' => "remove");
						$PRP = array('requestAction' => "delete_backup",'requestParams' => array( 'task_name' => "Backup Now",'result_id' => $historyData['historyID']),'siteData' => getSiteData($historyData['siteID']),'type' => "backup",'action' => "remove",'status' => "scheduled",'events' => 1,'historyAdditionalData' => $historyAdditionalData,'doNotShowUser' => true,'sendAfterAllLoad' => false,'timeScheduled' => time()+30);
						prepareRequestAndAddHistory($PRP);
					}
				}
			}
		}
	}
	
	public static function fetchRecentPluginsStatus(){
		$plugins_status = array();
		$sitesStats = panelRequestManager::getRawSitesStats();
		foreach ($sitesStats as $siteID => $data) {
			$plugins_status[$siteID] = $data['stats']['plugins_status'];
		}
		return $plugins_status;
	}
	
	public static function fetchRecentThemesStatus(){
		$themes_status = array();
		$sitesStats = panelRequestManager::getRawSitesStats();
		foreach ($sitesStats as $siteID => $data) {
			$themes_status[$siteID] = $data['stats']['themes_status'];
		}
		return $themes_status;
	}
        
        public static function getRecentPluginsStatus(){
		$plugins_status = array();
		$sitesStats = panelRequestManager::getRawSitesStats();
		foreach ($sitesStats as $siteID => $data) {
			$plugins_status[$siteID] = $data['stats']['plugins_status'];
		}
		return $plugins_status;
	}
	
	public static function getRecentThemesStatus(){
		$themes_status = array();
		$sitesStats = panelRequestManager::getRawSitesStats();
		foreach ($sitesStats as $siteID => $data) {
			$themes_status[$siteID] = $data['stats']['themes_status'];
		}
		return $themes_status;
	}
	
	public static function pluginInintializationReload($params){
		addNotification($type='N', $title='Installations & Activations started', $message='You can start using the addon after completion of installation & activation of the plugin and processing '.$params.' for the first time.', $state='U', $callbackOnClose='', $callbackReference='');
	}

	public static function googleServicesSaveAPIKeys($params)
	{
		if(!empty($params)){
			if ( updateOption('googleAPIKeys', serialize(array('clientID' => $params['clientID'], 'clientSecretKey' => $params['clientSecretKey']))) ){
				return array('clientID' => $params['clientID'], 'clientSecretKey' => $params['clientSecretKey']);
			}else{
				return false;
			}
		}
	}
	public static function googleServicesGetAPIKeys(){
		$googleServicesAPIKeys = getOption('googleAPIKeys');
		if(!empty($googleServicesAPIKeys)){
			$googleServicesAPIKeys = unserialize($googleServicesAPIKeys);
			return $googleServicesAPIKeys;
		}
	}
	public static function getConfigFTP()
	{
		$ftpDetails = array();
		if($GLOBALS['isFTPDefinedConfig']){
			$ftpDetails['HOST'] 	= APP_FTP_HOST;
			$ftpDetails['PORT'] 	= APP_FTP_PORT;
			$ftpDetails['BASE'] 	= APP_FTP_BASE;
			$ftpDetails['USER'] 	= APP_FTP_USER;
			$ftpDetails['PASS'] 	= APP_FTP_PASS;
			$ftpDetails['SSL'] 		= APP_FTP_SSL;
			$ftpDetails['SFTP']  	= APP_FTP_USE_SFTP;
			$ftpDetails['config'] 	= 1;
		}
		return $ftpDetails;
		
	}

	public static function saveAppUpdateSettings($params){
		$getConfigFTP = self::getConfigFTP();
		$FTPDetails = array();
		$FTPDetails['FTPValues'] = unserialize(getOption('FTPCredentials'));
		updateOption('isDirectFS', $params['isDirectFS']);
 		if(!empty($getConfigFTP) && $params['isDirectFS'] == 'N'){
 			self::removeConfigFTPDetails();
 		}
 		if($params['isDirectFS'] == 'Y' && empty($getConfigFTP) ){
			updateOption('FTPCredentials', serialize($FTPDetails['FTPValues']));
			$FTPDetails['isDirectFS'] = $params['isDirectFS'];
			return $FTPDetails;
		}
		updateOption('FTPCredentials', serialize($params['FTPValues']));	
		return $params;
	}

	public static function removeConfigFTPDetails(){	
		$configFileContents = file_get_contents(APP_ROOT.'/config.php');
		if (!empty($configFileContents) && is_writable('config.php')) {	
			$configFileContents = preg_replace('/define\(\'APP_FTP.*\R/', '', $configFileContents);
			return file_put_contents(APP_ROOT.'/config.php', $configFileContents);
		}
		else{
			return false;
		}
	}
	
	public static function isFSValidBasePath__DUMMY(){
		
		$testFile = '/__testFTP'.time().'.php';
		$appPathFile = APP_ROOT.$testFile;
		$FTPPathFile = rtrim($params['BASE'], '/').$testFile;
		
        require_once APP_ROOT.'/includes/fileSystemBase.php';
        $args = array(
            'hostname'       => $params['HOST'],
            'port'           => $params['PORT'],
            'username'       => $params['USER'],
            'password'       => $params['PASS'],
            'base'           => rtrim($params['BASE'], '/'),
            'connectionType' => $params['SSL']?'ftps':'');

        if ($params['SFTP']) {
            require_once APP_ROOT.'/includes/fileSystemSFTPExt.php';
            $directFSObj = new filesystemSFTPExt($args);
        } else {
            require_once APP_ROOT.'/includes/fileSystemFTPExt.php';
            $directFSObj = new filesystemFTPExt($args);
        }
		
		
		
				//Define the timeouts for the connections. Only available after the construct is called to allow for per-transport overriding of the default.
		if ( ! defined('FS_CONNECT_TIMEOUT') )
			define('FS_CONNECT_TIMEOUT', 30);
		if ( ! defined('FS_TIMEOUT') )
			define('FS_TIMEOUT', 30);

		//if ( is_error($FileSystemObj->errors) && $FileSystemObj->errors->get_error_code() )
		//	return false;

		if ( !$directFSObj->connect() )
			return false; //There was an error connecting to the server.

		// Set the permission constants if not already set.
		if ( ! defined('FS_CHMOD_DIR') )
			define('FS_CHMOD_DIR', 0755 );
		if ( ! defined('FS_CHMOD_FILE') )
			define('FS_CHMOD_FILE', 0644 );
		
		
		
		
        $testFileCreated = $directFSObj->putContents($FTPPathFile, 'tesdklfnsd', FS_CHMOD_FILE);
		sleep(1);
		
		if(!file_exists($appPathFile)){
			addNotification($type='N', $title='Invalid FTP Details', $message='Folder path is wrong', $state='E', $callbackOnClose='', $callbackReference='');
		}
		if($testFileCreated){
			$directFSObj->delete($FTPPathFile);
		}
		
	}
	public static function getFTPValues(){
		$FTPCreds = @unserialize(getOption('FTPCredentials'));
		return $FTPCreds;
	}
	
	private function generate_timezone_list(){
            $timezone_list = array('Pacific/Midway'=>'(UTC-11:00) Pacific/Midway','Pacific/Niue'=>'(UTC-11:00) Pacific/Niue','Pacific/Pago_Pago'=>'(UTC-11:00) Pacific/Pago_Pago','Pacific/Johnston'=>'(UTC-10:00) Pacific/Johnston','Pacific/Honolulu'=>'(UTC-10:00) Pacific/Honolulu','Pacific/Rarotonga'=>'(UTC-10:00) Pacific/Rarotonga','Pacific/Tahiti'=>'(UTC-10:00) Pacific/Tahiti','Pacific/Marquesas'=>'(UTC-09:30) Pacific/Marquesas','Pacific/Gambier'=>'(UTC-09:00) Pacific/Gambier','America/Adak'=>'(UTC-09:00) America/Adak','America/Metlakatla'=>'(UTC-08:00) America/Metlakatla','America/Juneau'=>'(UTC-08:00) America/Juneau','Pacific/Pitcairn'=>'(UTC-08:00) Pacific/Pitcairn','America/Sitka'=>'(UTC-08:00) America/Sitka','America/Anchorage'=>'(UTC-08:00) America/Anchorage','America/Nome'=>'(UTC-08:00) America/Nome','America/Yakutat'=>'(UTC-08:00) America/Yakutat','America/Santa_Isabel'=>'(UTC-07:00) America/Santa_Isabel','America/Hermosillo'=>'(UTC-07:00) America/Hermosillo','America/Phoenix'=>'(UTC-07:00) America/Phoenix','America/Dawson_Creek'=>'(UTC-07:00) America/Dawson_Creek','America/Creston'=>'(UTC-07:00) America/Creston','America/Dawson'=>'(UTC-07:00) America/Dawson','America/Whitehorse'=>'(UTC-07:00) America/Whitehorse','America/Los_Angeles'=>'(UTC-07:00) America/Los_Angeles','America/Vancouver'=>'(UTC-07:00) America/Vancouver','America/Tijuana'=>'(UTC-07:00) America/Tijuana','America/Denver'=>'(UTC-06:00) America/Denver','America/Belize'=>'(UTC-06:00) America/Belize','America/Regina'=>'(UTC-06:00) America/Regina','Pacific/Galapagos'=>'(UTC-06:00) Pacific/Galapagos','America/Edmonton'=>'(UTC-06:00) America/Edmonton','America/Guatemala'=>'(UTC-06:00) America/Guatemala','America/Ojinaga'=>'(UTC-06:00) America/Ojinaga','America/Mazatlan'=>'(UTC-06:00) America/Mazatlan','America/El_Salvador'=>'(UTC-06:00) America/El_Salvador','America/Managua'=>'(UTC-06:00) America/Managua','America/Inuvik'=>'(UTC-06:00) America/Inuvik','Pacific/Easter'=>'(UTC-06:00) Pacific/Easter','America/Swift_Current'=>'(UTC-06:00) America/Swift_Current','America/Yellowknife'=>'(UTC-06:00) America/Yellowknife','America/Chihuahua'=>'(UTC-06:00) America/Chihuahua','America/Cambridge_Bay'=>'(UTC-06:00) America/Cambridge_Bay','America/Costa_Rica'=>'(UTC-06:00) America/Costa_Rica','America/Boise'=>'(UTC-06:00) America/Boise','America/Tegucigalpa'=>'(UTC-06:00) America/Tegucigalpa','America/Eirunepe'=>'(UTC-05:00) America/Eirunepe','America/Menominee'=>'(UTC-05:00) America/Menominee','America/Jamaica'=>'(UTC-05:00) America/Jamaica','America/North_Dakota/Beulah'=>'(UTC-05:00) America/North_Dakota/Beulah','America/Chicago'=>'(UTC-05:00) America/Chicago','America/Cancun'=>'(UTC-05:00) America/Cancun','America/Merida'=>'(UTC-05:00) America/Merida','America/Mexico_City'=>'(UTC-05:00) America/Mexico_City','America/North_Dakota/Center'=>'(UTC-05:00) America/North_Dakota/Center','America/Bahia_Banderas'=>'(UTC-05:00) America/Bahia_Banderas','America/Atikokan'=>'(UTC-05:00) America/Atikokan','America/Cayman'=>'(UTC-05:00) America/Cayman','America/Bogota'=>'(UTC-05:00) America/Bogota','America/Monterrey'=>'(UTC-05:00) America/Monterrey','America/Panama'=>'(UTC-05:00) America/Panama','America/Rio_Branco'=>'(UTC-05:00) America/Rio_Branco','America/Resolute'=>'(UTC-05:00) America/Resolute','America/North_Dakota/New_Salem'=>'(UTC-05:00) America/North_Dakota/New_Salem','America/Lima'=>'(UTC-05:00) America/Lima','America/Indiana/Knox'=>'(UTC-05:00) America/Indiana/Knox','America/Winnipeg'=>'(UTC-05:00) America/Winnipeg','America/Indiana/Tell_City'=>'(UTC-05:00) America/Indiana/Tell_City','America/Rainy_River'=>'(UTC-05:00) America/Rainy_River','America/Rankin_Inlet'=>'(UTC-05:00) America/Rankin_Inlet','America/Matamoros'=>'(UTC-05:00) America/Matamoros','America/Guayaquil'=>'(UTC-05:00) America/Guayaquil','America/Caracas'=>'(UTC-04:30) America/Caracas','America/Curacao'=>'(UTC-04:00) America/Curacao','America/Indiana/Petersburg'=>'(UTC-04:00) America/Indiana/Petersburg','America/Indiana/Marengo'=>'(UTC-04:00) America/Indiana/Marengo','America/Indiana/Vevay'=>'(UTC-04:00) America/Indiana/Vevay','America/Iqaluit'=>'(UTC-04:00) America/Iqaluit','America/Indiana/Winamac'=>'(UTC-04:00) America/Indiana/Winamac','America/Indiana/Vincennes'=>'(UTC-04:00) America/Indiana/Vincennes','America/Martinique'=>'(UTC-04:00) America/Martinique','America/Indiana/Indianapolis'=>'(UTC-04:00) America/Indiana/Indianapolis','America/Marigot'=>'(UTC-04:00) America/Marigot','America/Detroit'=>'(UTC-04:00) America/Detroit','America/Guyana'=>'(UTC-04:00) America/Guyana','America/Guadeloupe'=>'(UTC-04:00) America/Guadeloupe','America/Havana'=>'(UTC-04:00) America/Havana','America/Grand_Turk'=>'(UTC-04:00) America/Grand_Turk','America/Cuiaba'=>'(UTC-04:00) America/Cuiaba','America/Grenada'=>'(UTC-04:00) America/Grenada','America/Dominica'=>'(UTC-04:00) America/Dominica','America/Asuncion'=>'(UTC-04:00) America/Asuncion','America/Kralendijk'=>'(UTC-04:00) America/Kralendijk','America/Santiago'=>'(UTC-04:00) America/Santiago','America/Santo_Domingo'=>'(UTC-04:00) America/Santo_Domingo','America/Kentucky/Monticello'=>'(UTC-04:00) America/Kentucky/Monticello','America/Puerto_Rico'=>'(UTC-04:00) America/Puerto_Rico','America/Port_of_Spain'=>'(UTC-04:00) America/Port_of_Spain','America/Porto_Velho'=>'(UTC-04:00) America/Porto_Velho','America/La_Paz'=>'(UTC-04:00) America/La_Paz','America/St_Barthelemy'=>'(UTC-04:00) America/St_Barthelemy','America/Thunder_Bay'=>'(UTC-04:00) America/Thunder_Bay','America/Tortola'=>'(UTC-04:00) America/Tortola','America/St_Vincent'=>'(UTC-04:00) America/St_Vincent','America/St_Thomas'=>'(UTC-04:00) America/St_Thomas','America/St_Kitts'=>'(UTC-04:00) America/St_Kitts','America/St_Lucia'=>'(UTC-04:00) America/St_Lucia','America/Port-au-Prince'=>'(UTC-04:00) America/Port-au-Prince','America/Kentucky/Louisville'=>'(UTC-04:00) America/Kentucky/Louisville','America/Lower_Princes'=>'(UTC-04:00) America/Lower_Princes','America/Toronto'=>'(UTC-04:00) America/Toronto','America/Aruba'=>'(UTC-04:00) America/Aruba','America/Barbados'=>'(UTC-04:00) America/Barbados','America/Blanc-Sablon'=>'(UTC-04:00) America/Blanc-Sablon','America/Campo_Grande'=>'(UTC-04:00) America/Campo_Grande','America/Boa_Vista'=>'(UTC-04:00) America/Boa_Vista','America/Montserrat'=>'(UTC-04:00) America/Montserrat','America/Nassau'=>'(UTC-04:00) America/Nassau','America/Anguilla'=>'(UTC-04:00) America/Anguilla','Antarctica/Palmer'=>'(UTC-04:00) Antarctica/Palmer','America/Antigua'=>'(UTC-04:00) America/Antigua','America/Pangnirtung'=>'(UTC-04:00) America/Pangnirtung','America/New_York'=>'(UTC-04:00) America/New_York','America/Nipigon'=>'(UTC-04:00) America/Nipigon','America/Manaus'=>'(UTC-04:00) America/Manaus','America/Montevideo'=>'(UTC-03:00) America/Montevideo','Antarctica/Rothera'=>'(UTC-03:00) Antarctica/Rothera','Atlantic/Stanley'=>'(UTC-03:00) Atlantic/Stanley','Atlantic/Bermuda'=>'(UTC-03:00) Atlantic/Bermuda','America/Thule'=>'(UTC-03:00) America/Thule','America/Sao_Paulo'=>'(UTC-03:00) America/Sao_Paulo','America/Paramaribo'=>'(UTC-03:00) America/Paramaribo','America/Recife'=>'(UTC-03:00) America/Recife','America/Santarem'=>'(UTC-03:00) America/Santarem','America/Moncton'=>'(UTC-03:00) America/Moncton','America/Maceio'=>'(UTC-03:00) America/Maceio','America/Argentina/Salta'=>'(UTC-03:00) America/Argentina/Salta','America/Argentina/San_Juan'=>'(UTC-03:00) America/Argentina/San_Juan','America/Argentina/San_Luis'=>'(UTC-03:00) America/Argentina/San_Luis','America/Argentina/Tucuman'=>'(UTC-03:00) America/Argentina/Tucuman','America/Argentina/Rio_Gallegos'=>'(UTC-03:00) America/Argentina/Rio_Gallegos','America/Argentina/Mendoza'=>'(UTC-03:00) America/Argentina/Mendoza','America/Argentina/Cordoba'=>'(UTC-03:00) America/Argentina/Cordoba','America/Argentina/Jujuy'=>'(UTC-03:00) America/Argentina/Jujuy','America/Argentina/La_Rioja'=>'(UTC-03:00) America/Argentina/La_Rioja','America/Argentina/Buenos_Aires'=>'(UTC-03:00) America/Argentina/Buenos_Aires','America/Argentina/Ushuaia'=>'(UTC-03:00) America/Argentina/Ushuaia','America/Bahia'=>'(UTC-03:00) America/Bahia','America/Fortaleza'=>'(UTC-03:00) America/Fortaleza','America/Glace_Bay'=>'(UTC-03:00) America/Glace_Bay','America/Goose_Bay'=>'(UTC-03:00) America/Goose_Bay','America/Halifax'=>'(UTC-03:00) America/Halifax','America/Argentina/Catamarca'=>'(UTC-03:00) America/Argentina/Catamarca','America/Araguaina'=>'(UTC-03:00) America/Araguaina','America/Belem'=>'(UTC-03:00) America/Belem','America/Cayenne'=>'(UTC-03:00) America/Cayenne','America/St_Johns'=>'(UTC-02:30) America/St_Johns','Atlantic/South_Georgia'=>'(UTC-02:00) Atlantic/South_Georgia','America/Noronha'=>'(UTC-02:00) America/Noronha','America/Miquelon'=>'(UTC-02:00) America/Miquelon','America/Godthab'=>'(UTC-02:00) America/Godthab','Atlantic/Cape_Verde'=>'(UTC-01:00) Atlantic/Cape_Verde','Africa/Bissau'=>'(UTC+00:00) Africa/Bissau','Africa/Conakry'=>'(UTC+00:00) Africa/Conakry','Africa/Freetown'=>'(UTC+00:00) Africa/Freetown','Africa/Banjul'=>'(UTC+00:00) Africa/Banjul','Africa/Dakar'=>'(UTC+00:00) Africa/Dakar','Atlantic/Azores'=>'(UTC+00:00) Atlantic/Azores','Atlantic/Reykjavik'=>'(UTC+00:00) Atlantic/Reykjavik','Atlantic/St_Helena'=>'(UTC+00:00) Atlantic/St_Helena','Africa/Abidjan'=>'(UTC+00:00) Africa/Abidjan','Africa/Bamako'=>'(UTC+00:00) Africa/Bamako','America/Scoresbysund'=>'(UTC+00:00) America/Scoresbysund','Africa/Accra'=>'(UTC+00:00) Africa/Accra','Africa/Lome'=>'(UTC+00:00) Africa/Lome','Africa/Nouakchott'=>'(UTC+00:00) Africa/Nouakchott','Africa/Sao_Tome'=>'(UTC+00:00) Africa/Sao_Tome','Africa/Ouagadougou'=>'(UTC+00:00) Africa/Ouagadougou','Africa/Monrovia'=>'(UTC+00:00) Africa/Monrovia','America/Danmarkshavn'=>'(UTC+00:00) America/Danmarkshavn','Africa/Niamey'=>'(UTC+01:00) Africa/Niamey','Africa/Brazzaville'=>'(UTC+01:00) Africa/Brazzaville','Europe/Lisbon'=>'(UTC+01:00) Europe/Lisbon','Atlantic/Canary'=>'(UTC+01:00) Atlantic/Canary','Europe/Dublin'=>'(UTC+01:00) Europe/Dublin','Africa/Porto-Novo'=>'(UTC+01:00) Africa/Porto-Novo','Africa/Tunis'=>'(UTC+01:00) Africa/Tunis','Africa/Windhoek'=>'(UTC+01:00) Africa/Windhoek','Atlantic/Madeira'=>'(UTC+01:00) Atlantic/Madeira','Atlantic/Faroe'=>'(UTC+01:00) Atlantic/Faroe','Africa/Casablanca'=>'(UTC+01:00) Africa/Casablanca','Europe/London'=>'(UTC+01:00) Europe/London','Africa/Bangui'=>'(UTC+01:00) Africa/Bangui','Africa/Ndjamena'=>'(UTC+01:00) Africa/Ndjamena','Africa/Luanda'=>'(UTC+01:00) Africa/Luanda','Europe/Isle_of_Man'=>'(UTC+01:00) Europe/Isle_of_Man','Europe/Jersey'=>'(UTC+01:00) Europe/Jersey','Europe/Guernsey'=>'(UTC+01:00) Europe/Guernsey','Africa/Malabo'=>'(UTC+01:00) Africa/Malabo','Africa/El_Aaiun'=>'(UTC+01:00) Africa/El_Aaiun','Africa/Lagos'=>'(UTC+01:00) Africa/Lagos','Africa/Libreville'=>'(UTC+01:00) Africa/Libreville','Africa/Douala'=>'(UTC+01:00) Africa/Douala','Africa/Algiers'=>'(UTC+01:00) Africa/Algiers','Africa/Kinshasa'=>'(UTC+01:00) Africa/Kinshasa','Europe/Monaco'=>'(UTC+02:00) Europe/Monaco','Europe/Amsterdam'=>'(UTC+02:00) Europe/Amsterdam','Europe/Oslo'=>'(UTC+02:00) Europe/Oslo','Europe/Prague'=>'(UTC+02:00) Europe/Prague','Europe/Podgorica'=>'(UTC+02:00) Europe/Podgorica','Europe/Gibraltar'=>'(UTC+02:00) Europe/Gibraltar','Europe/Paris'=>'(UTC+02:00) Europe/Paris','Europe/Andorra'=>'(UTC+02:00) Europe/Andorra','Europe/Budapest'=>'(UTC+02:00) Europe/Budapest','Europe/Brussels'=>'(UTC+02:00) Europe/Brussels','Europe/Ljubljana'=>'(UTC+02:00) Europe/Ljubljana','Europe/Busingen'=>'(UTC+02:00) Europe/Busingen','Europe/Belgrade'=>'(UTC+02:00) Europe/Belgrade','Europe/Berlin'=>'(UTC+02:00) Europe/Berlin','Europe/Bratislava'=>'(UTC+02:00) Europe/Bratislava','Europe/Madrid'=>'(UTC+02:00) Europe/Madrid','Europe/Luxembourg'=>'(UTC+02:00) Europe/Luxembourg','Europe/Copenhagen'=>'(UTC+02:00) Europe/Copenhagen','Europe/Malta'=>'(UTC+02:00) Europe/Malta','Africa/Ceuta'=>'(UTC+02:00) Africa/Ceuta','Africa/Johannesburg'=>'(UTC+02:00) Africa/Johannesburg','Africa/Kigali'=>'(UTC+02:00) Africa/Kigali','Africa/Harare'=>'(UTC+02:00) Africa/Harare','Africa/Gaborone'=>'(UTC+02:00) Africa/Gaborone','Africa/Cairo'=>'(UTC+02:00) Africa/Cairo','Africa/Lubumbashi'=>'(UTC+02:00) Africa/Lubumbashi','Africa/Lusaka'=>'(UTC+02:00) Africa/Lusaka','Africa/Tripoli'=>'(UTC+02:00) Africa/Tripoli','Africa/Mbabane'=>'(UTC+02:00) Africa/Mbabane','Africa/Maseru'=>'(UTC+02:00) Africa/Maseru','Africa/Maputo'=>'(UTC+02:00) Africa/Maputo','Europe/Rome'=>'(UTC+02:00) Europe/Rome','Africa/Bujumbura'=>'(UTC+02:00) Africa/Bujumbura','Europe/Tirane'=>'(UTC+02:00) Europe/Tirane','Europe/Vaduz'=>'(UTC+02:00) Europe/Vaduz','Europe/Stockholm'=>'(UTC+02:00) Europe/Stockholm','Europe/Skopje'=>'(UTC+02:00) Europe/Skopje','Europe/San_Marino'=>'(UTC+02:00) Europe/San_Marino','Europe/Sarajevo'=>'(UTC+02:00) Europe/Sarajevo','Europe/Vatican'=>'(UTC+02:00) Europe/Vatican','Europe/Vienna'=>'(UTC+02:00) Europe/Vienna','Europe/Zurich'=>'(UTC+02:00) Europe/Zurich','Africa/Blantyre'=>'(UTC+02:00) Africa/Blantyre','Europe/Zagreb'=>'(UTC+02:00) Europe/Zagreb','Europe/Warsaw'=>'(UTC+02:00) Europe/Warsaw','Europe/Athens'=>'(UTC+03:00) Europe/Athens','Europe/Bucharest'=>'(UTC+03:00) Europe/Bucharest','Indian/Comoro'=>'(UTC+03:00) Indian/Comoro','Europe/Uzhgorod'=>'(UTC+03:00) Europe/Uzhgorod','Europe/Tallinn'=>'(UTC+03:00) Europe/Tallinn','Europe/Sofia'=>'(UTC+03:00) Europe/Sofia','Europe/Vilnius'=>'(UTC+03:00) Europe/Vilnius','Europe/Zaporozhye'=>'(UTC+03:00) Europe/Zaporozhye','Indian/Mayotte'=>'(UTC+03:00) Indian/Mayotte','Indian/Antananarivo'=>'(UTC+03:00) Indian/Antananarivo','Europe/Simferopol'=>'(UTC+03:00) Europe/Simferopol','Europe/Riga'=>'(UTC+03:00) Europe/Riga','Europe/Istanbul'=>'(UTC+03:00) Europe/Istanbul','Europe/Helsinki'=>'(UTC+03:00) Europe/Helsinki','Europe/Kaliningrad'=>'(UTC+03:00) Europe/Kaliningrad','Europe/Kiev'=>'(UTC+03:00) Europe/Kiev','Europe/Minsk'=>'(UTC+03:00) Europe/Minsk','Europe/Mariehamn'=>'(UTC+03:00) Europe/Mariehamn','Europe/Chisinau'=>'(UTC+03:00) Europe/Chisinau','Antarctica/Syowa'=>'(UTC+03:00) Antarctica/Syowa','Asia/Beirut'=>'(UTC+03:00) Asia/Beirut','Asia/Riyadh'=>'(UTC+03:00) Asia/Riyadh','Asia/Bahrain'=>'(UTC+03:00) Asia/Bahrain','Asia/Baghdad'=>'(UTC+03:00) Asia/Baghdad','Asia/Nicosia'=>'(UTC+03:00) Asia/Nicosia','Asia/Damascus'=>'(UTC+03:00) Asia/Damascus','Asia/Qatar'=>'(UTC+03:00) Asia/Qatar','Asia/Kuwait'=>'(UTC+03:00) Asia/Kuwait','Asia/Jerusalem'=>'(UTC+03:00) Asia/Jerusalem','Asia/Hebron'=>'(UTC+03:00) Asia/Hebron','Asia/Gaza'=>'(UTC+03:00) Asia/Gaza','Asia/Aden'=>'(UTC+03:00) Asia/Aden','Asia/Amman'=>'(UTC+03:00) Asia/Amman','Africa/Khartoum'=>'(UTC+03:00) Africa/Khartoum','Africa/Dar_es_Salaam'=>'(UTC+03:00) Africa/Dar_es_Salaam','Africa/Kampala'=>'(UTC+03:00) Africa/Kampala','Africa/Juba'=>'(UTC+03:00) Africa/Juba','Africa/Mogadishu'=>'(UTC+03:00) Africa/Mogadishu','Africa/Nairobi'=>'(UTC+03:00) Africa/Nairobi','Africa/Addis_Ababa'=>'(UTC+03:00) Africa/Addis_Ababa','Africa/Asmara'=>'(UTC+03:00) Africa/Asmara','Africa/Djibouti'=>'(UTC+03:00) Africa/Djibouti','Asia/Muscat'=>'(UTC+04:00) Asia/Muscat','Europe/Samara'=>'(UTC+04:00) Europe/Samara','Indian/Mauritius'=>'(UTC+04:00) Indian/Mauritius','Europe/Volgograd'=>'(UTC+04:00) Europe/Volgograd','Indian/Reunion'=>'(UTC+04:00) Indian/Reunion','Indian/Mahe'=>'(UTC+04:00) Indian/Mahe','Europe/Moscow'=>'(UTC+04:00) Europe/Moscow','Asia/Dubai'=>'(UTC+04:00) Asia/Dubai','Asia/Yerevan'=>'(UTC+04:00) Asia/Yerevan','Asia/Tbilisi'=>'(UTC+04:00) Asia/Tbilisi','Asia/Tehran'=>'(UTC+04:30) Asia/Tehran','Asia/Kabul'=>'(UTC+04:30) Asia/Kabul','Asia/Samarkand'=>'(UTC+05:00) Asia/Samarkand','Asia/Baku'=>'(UTC+05:00) Asia/Baku','Asia/Dushanbe'=>'(UTC+05:00) Asia/Dushanbe','Asia/Oral'=>'(UTC+05:00) Asia/Oral','Asia/Aqtau'=>'(UTC+05:00) Asia/Aqtau','Indian/Maldives'=>'(UTC+05:00) Indian/Maldives','Asia/Tashkent'=>'(UTC+05:00) Asia/Tashkent','Indian/Kerguelen'=>'(UTC+05:00) Indian/Kerguelen','Antarctica/Mawson'=>'(UTC+05:00) Antarctica/Mawson','Asia/Karachi'=>'(UTC+05:00) Asia/Karachi','Asia/Aqtobe'=>'(UTC+05:00) Asia/Aqtobe','Asia/Ashgabat'=>'(UTC+05:00) Asia/Ashgabat','Asia/Kolkata'=>'(UTC+05:30) Asia/Kolkata','Asia/Colombo'=>'(UTC+05:30) Asia/Colombo','Asia/Kathmandu'=>'(UTC+05:45) Asia/Kathmandu','Asia/Qyzylorda'=>'(UTC+06:00) Asia/Qyzylorda','Asia/Bishkek'=>'(UTC+06:00) Asia/Bishkek','Asia/Yekaterinburg'=>'(UTC+06:00) Asia/Yekaterinburg','Asia/Thimphu'=>'(UTC+06:00) Asia/Thimphu','Indian/Chagos'=>'(UTC+06:00) Indian/Chagos','Asia/Almaty'=>'(UTC+06:00) Asia/Almaty','Asia/Dhaka'=>'(UTC+06:00) Asia/Dhaka','Antarctica/Vostok'=>'(UTC+06:00) Antarctica/Vostok','Asia/Rangoon'=>'(UTC+06:30) Asia/Rangoon','Indian/Cocos'=>'(UTC+06:30) Indian/Cocos','Asia/Novokuznetsk'=>'(UTC+07:00) Asia/Novokuznetsk','Asia/Bangkok'=>'(UTC+07:00) Asia/Bangkok','Asia/Novosibirsk'=>'(UTC+07:00) Asia/Novosibirsk','Antarctica/Davis'=>'(UTC+07:00) Antarctica/Davis','Asia/Vientiane'=>'(UTC+07:00) Asia/Vientiane','Indian/Christmas'=>'(UTC+07:00) Indian/Christmas','Asia/Pontianak'=>'(UTC+07:00) Asia/Pontianak','Asia/Omsk'=>'(UTC+07:00) Asia/Omsk','Asia/Ho_Chi_Minh'=>'(UTC+07:00) Asia/Ho_Chi_Minh','Asia/Hovd'=>'(UTC+07:00) Asia/Hovd','Asia/Jakarta'=>'(UTC+07:00) Asia/Jakarta','Asia/Phnom_Penh'=>'(UTC+07:00) Asia/Phnom_Penh','Asia/Kuching'=>'(UTC+08:00) Asia/Kuching','Asia/Harbin'=>'(UTC+08:00) Asia/Harbin','Australia/Perth'=>'(UTC+08:00) Australia/Perth','Asia/Singapore'=>'(UTC+08:00) Asia/Singapore','Asia/Taipei'=>'(UTC+08:00) Asia/Taipei','Asia/Ulaanbaatar'=>'(UTC+08:00) Asia/Ulaanbaatar','Antarctica/Casey'=>'(UTC+08:00) Antarctica/Casey','Asia/Macau'=>'(UTC+08:00) Asia/Macau','Asia/Urumqi'=>'(UTC+08:00) Asia/Urumqi','Asia/Manila'=>'(UTC+08:00) Asia/Manila','Asia/Hong_Kong'=>'(UTC+08:00) Asia/Hong_Kong','Asia/Kuala_Lumpur'=>'(UTC+08:00) Asia/Kuala_Lumpur','Asia/Choibalsan'=>'(UTC+08:00) Asia/Choibalsan','Asia/Kashgar'=>'(UTC+08:00) Asia/Kashgar','Asia/Shanghai'=>'(UTC+08:00) Asia/Shanghai','Asia/Makassar'=>'(UTC+08:00) Asia/Makassar','Asia/Brunei'=>'(UTC+08:00) Asia/Brunei','Asia/Chongqing'=>'(UTC+08:00) Asia/Chongqing','Asia/Krasnoyarsk'=>'(UTC+08:00) Asia/Krasnoyarsk','Australia/Eucla'=>'(UTC+08:45) Australia/Eucla','Asia/Seoul'=>'(UTC+09:00) Asia/Seoul','Asia/Irkutsk'=>'(UTC+09:00) Asia/Irkutsk','Asia/Jayapura'=>'(UTC+09:00) Asia/Jayapura','Pacific/Palau'=>'(UTC+09:00) Pacific/Palau','Asia/Pyongyang'=>'(UTC+09:00) Asia/Pyongyang','Asia/Dili'=>'(UTC+09:00) Asia/Dili','Asia/Tokyo'=>'(UTC+09:00) Asia/Tokyo','Australia/Darwin'=>'(UTC+09:30) Australia/Darwin','Australia/Broken_Hill'=>'(UTC+09:30) Australia/Broken_Hill','Australia/Adelaide'=>'(UTC+09:30) Australia/Adelaide','Australia/Hobart'=>'(UTC+10:00) Australia/Hobart','Pacific/Chuuk'=>'(UTC+10:00) Pacific/Chuuk','Pacific/Guam'=>'(UTC+10:00) Pacific/Guam','Antarctica/DumontDUrville'=>'(UTC+10:00) Antarctica/DumontDUrville','Australia/Brisbane'=>'(UTC+10:00) Australia/Brisbane','Pacific/Saipan'=>'(UTC+10:00) Pacific/Saipan','Australia/Sydney'=>'(UTC+10:00) Australia/Sydney','Australia/Currie'=>'(UTC+10:00) Australia/Currie','Asia/Yakutsk'=>'(UTC+10:00) Asia/Yakutsk','Asia/Khandyga'=>'(UTC+10:00) Asia/Khandyga','Australia/Melbourne'=>'(UTC+10:00) Australia/Melbourne','Pacific/Port_Moresby'=>'(UTC+10:00) Pacific/Port_Moresby','Australia/Lindeman'=>'(UTC+10:00) Australia/Lindeman','Australia/Lord_Howe'=>'(UTC+10:30) Australia/Lord_Howe','Asia/Ust-Nera'=>'(UTC+11:00) Asia/Ust-Nera','Pacific/Noumea'=>'(UTC+11:00) Pacific/Noumea','Pacific/Pohnpei'=>'(UTC+11:00) Pacific/Pohnpei','Asia/Vladivostok'=>'(UTC+11:00) Asia/Vladivostok','Asia/Sakhalin'=>'(UTC+11:00) Asia/Sakhalin','Pacific/Kosrae'=>'(UTC+11:00) Pacific/Kosrae','Antarctica/Macquarie'=>'(UTC+11:00) Antarctica/Macquarie','Pacific/Guadalcanal'=>'(UTC+11:00) Pacific/Guadalcanal','Pacific/Efate'=>'(UTC+11:00) Pacific/Efate','Pacific/Norfolk'=>'(UTC+11:30) Pacific/Norfolk','Antarctica/McMurdo'=>'(UTC+12:00) Antarctica/McMurdo','Asia/Kamchatka'=>'(UTC+12:00) Asia/Kamchatka','Asia/Magadan'=>'(UTC+12:00) Asia/Magadan','Asia/Anadyr'=>'(UTC+12:00) Asia/Anadyr','Pacific/Fiji'=>'(UTC+12:00) Pacific/Fiji','Pacific/Majuro'=>'(UTC+12:00) Pacific/Majuro','Pacific/Wake'=>'(UTC+12:00) Pacific/Wake','Pacific/Nauru'=>'(UTC+12:00) Pacific/Nauru','Pacific/Auckland'=>'(UTC+12:00) Pacific/Auckland','Pacific/Kwajalein'=>'(UTC+12:00) Pacific/Kwajalein','Pacific/Funafuti'=>'(UTC+12:00) Pacific/Funafuti','Pacific/Tarawa'=>'(UTC+12:00) Pacific/Tarawa','Pacific/Wallis'=>'(UTC+12:00) Pacific/Wallis','Pacific/Chatham'=>'(UTC+12:45) Pacific/Chatham','Pacific/Tongatapu'=>'(UTC+13:00) Pacific/Tongatapu','Pacific/Enderbury'=>'(UTC+13:00) Pacific/Enderbury','Pacific/Fakaofo'=>'(UTC+13:00) Pacific/Fakaofo','Pacific/Apia'=>'(UTC+13:00) Pacific/Apia','Pacific/Kiritimati'=>'(UTC+14:00) Pacific/Kiritimati');
	    return $timezone_list;
	}

	

	public static function getTimeZones(){
		$timezones = panelRequestManager::generate_timezone_list();

		return array('timeZones'=>$timezones);
	}


	function getReaddedSite(){
		$actionID = Reg::get('currentRequest.actionID');
		$where = array(
		      		'query' =>  "actionID = ':actionID'",
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
		$data = DB::getRow("?:history", "siteID,historyID", $where);
		$siteID = $data['siteID'];
		$historyID = $data['historyID'];
		$where = array(
		      		'query' =>  "historyID = ':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
	   				)
				);
		$status = DB::getField("?:history_additional_data", "status", $where);
		if($status == 'success'){
			return array('siteID'=>$siteID);
		}else{
			return false;
		}
	}

	function iwpMaintenance(){
		$actionID = Reg::get('currentRequest.actionID');
		$where = array(
		      		'query' =>  "actionID = ':actionID'",
		      		'params' => array(
		               ':actionID'=>$actionID
	   				)
				);
		$data = DB::getRow("?:history", "siteID,historyID", $where);
		$siteID = $data['siteID'];
		$historyID = $data['historyID'];
		$where = array(
		      		'query' =>  "historyID = ':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
	   				)
				);
		$data = DB::getRow("?:history_additional_data", "uniqueName,status", $where);
		$action = $data['uniqueName'];
		$status = $data['status'];
		if($status == 'success'){
			if($action == 'maintenance0'){$cStatus=1;}elseif($action == 'maintenance1'){$cStatus=2;}
			$where = array(
		      		'query' =>  "siteID = ':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
	   				)
				);
			if(DB::update("?:sites",array('connectionStatus'=>$cStatus),$where)){
				return array('siteID'=>$siteID,'action'=>$action);
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}

	function iwpUpdateNotes($params){
		$where = array(
		      		'query' =>  "siteID = ':siteID'",
		      		'params' => array(
		               ':siteID'=>$params['siteID']
	   				)
				);
		if(DB::update("?:sites", array('notes'=>$params['notes']), $where) ){
			return $params;
		}else{
			return false;
		}
	}

	function iwpUpdateLinks($params){
		$where = array(
		      		'query' =>  "siteID = ':siteID'",
		      		'params' => array(
		               ':siteID'=>$params['siteID']
	   				)
				);
		if(DB::update("?:sites", array('links'=>$params['links']), $where) ){
			$params['links'] = explode(",",$params['links']);
			return $params;
		}else{
			return false;
		}
	}
        
        public static function iwpLoadServerInfo($siteID){
        		$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
	   				)
				);
                $historyData = DB::getRow("?:sites", "siteTechinicalInfo", $where);
                $HTML = TPL::get('/templates/site/serverInformation.tpl.php', array('historyData' => unserialize($historyData['siteTechinicalInfo']), 'siteID' => $siteID));
		return $HTML;
	}
        
        public static function getlastMaintenanceModeHTML($siteID){
        	$where = array(
		      		'query' =>  "siteID = ':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
	   				)
				);
            $lastHTML = DB::getRow("?:sites", "lastMaintenanceModeHTML,siteID", $where);
            return $lastHTML;
        }

		public static function iwpUpdateNotifCount($ID){
			updateOption("notifCount", 0);				//setting that all the notify contents are read.
			updateOption("notifyCenterDeliveredCount", 0);
			//updating notif count below
			/* $notifyCenterCount = 0;
			$notifyCenterCount = getOption("notifCount") - 1;
			if(!($notifyCenterCount < 0)){
				updateOption("notifCount", $notifyCenterCount);
			} */
			
			if($ID != 'clear_offer'){
			//marking read for every messages below
				$notifyCenter = unserialize(getOption("markNotifyCenter"));
			if(!getOption("notifyCenterRead")){
				$notifyCenterRead = array();
			}
			else{
					$notifyCenterRead = unserialize(getOption("notifyCenterRead"));
			}
			//foreach($notifyCenter as $ID => $notifyDetail){
				$notifyCenterRead[$ID] = 1;
			//}
				updateOption("notifyCenterRead", serialize($notifyCenterRead));
			
			return array('notif_count' => $notifyCenterCount);
		}
		}
		
		public static function updateNotificationContent(){
			//get notification center content and notification offer content which is updated in options table
			$notifyCenter = unserialize(getOption("markNotifyCenter"));
			$notifyCenterCount = getOption("notif_count");
			$notifyCenterDeliveredCount = 0;
			$notifyOffer = unserialize(getOption("markNotifyOffer"));
			$notifyCountStyle = '';
			//$notifyCenterRead = array();
			if(!getOption("notifyCenterRead")){
				$notifyCenterRead = array();
			}
			else{
				$notifyCenterRead = unserialize(getOption("notifyCenterRead"));
			}
			if(!getOption("notifyCenterDelivered")){
				$notifyCenterDelivered = array();
			}
			else{
				$notifyCenterDelivered = unserialize(getOption("notifyCenterDelivered"));
			}
			if(!getOption("notifyOfferDelivered")){
				$notifyOfferDelivered = array();
			}
			else{
				$notifyOfferDelivered = unserialize(getOption("notifyOfferDelivered"));
			}
			if(empty($notifyCenterCount)){
				$notifyCountStyle = 'style="display:none"';
			}
			
			//prepare the html
			$notificationTypeBasedIcon = array('announcement' => 'bullhorn', 'occasionalOffer' => 'gift', 'blog' => 'file-text-o');
			//$notifyCenter = json_decode(getOption("markNotifyCenter"), true);
			$notif_center_html = '';
			if(!empty($notifyCenter)){
				foreach($notifyCenter as $ID => $notifyDetail){
					if(!array_key_exists($ID, $notifyCenterRead)){
						$notifyCenterRead[$ID] = 1;					//marking read in array's key
						$this_class = 'unread';
					}
					else{
						$this_class = 'read';
					}
					if(!array_key_exists($ID, $notifyCenterDelivered)){
						$notifyCenterDelivered[$ID] = 1;					//marking delivered in array's key
						$notifyCenterDeliveredCount++;
					}
					$notif_center_html .= '<div class="notif_each_wrap"><li notif_id = "'.$ID.'" class="'.$this_class.'"><div class="notif_content">'.$notifyDetail['htmlContent'].'<div class="timestamp">'.date('M j ', $notifyDetail['publishedTime']).' at '.date('H:ia', $notifyDetail['publishedTime']).'</div></div>	<i class="fa fa-'.$notificationTypeBasedIcon[$notifyDetail['type']].'"></i></li></div>'; 
					
				}
			}
			updateOption("notifyCenterDelivered", serialize($notifyCenterDelivered));
			
			$offer_hmtl = '';
			if(!empty($notifyOffer)){
				//for displaying the discount
				$offer_hmtl = $notifyOffer["htmlContent"];
				
				//for including offers into delivered count
				if(!array_key_exists($notifyOffer['ID'], $notifyOfferDelivered)){
					$notifyOfferDelivered[$notifyOffer["ID"]] = 1;					//marking delivered in array's key
					$notifyCenterDeliveredCount++;
				}
			}
			updateOption("notifyOfferDelivered", serialize($notifyOfferDelivered));
			updateOption("notifyCenterDeliveredCount", $notifyCenterDeliveredCount);
			
			return array('center_html' => $notif_center_html, 'offer_hmtl' => $offer_hmtl, 'notif_count' => $notifyCenterDeliveredCount);
		}
		
		public static function updateIwpTweetStatus($type){
			if(!empty($type)){
				updateOption('tweet_status_'.$type, "done");
			}
			return $type;
		}

	public static function clearHistoryByIDs($historyIDs = array() ){
		if(!empty($historyIDs)){
			$historyIDs = implode("','", DB::esc($historyIDs));
			DB::delete("?:history",  "historyID IN ('".$historyIDs."')");
			DB::delete("?:history_additional_data", "historyID IN ('".$historyIDs."')");
			DB::delete("?:history_raw_details", "historyID IN ('".$historyIDs."')");

			DB::doQuery("OPTIMIZE TABLE `?:history`");
			DB::doQuery("OPTIMIZE TABLE `?:history_additional_data`");
			DB::doQuery("OPTIMIZE TABLE `?:history_raw_details`");
		}
	}

	public static function clearHistoryTasks($params){
    	if(isset($params) && isset($params['clearWhat'])){
    		if($params['clearWhat'] == 'uncomplete'){
				$accessUsers = " ";
				setHook('historyHTML', $accessUsers);
				$where = array(
		      		'query' =>  "status NOT IN ('completed','error','netError') AND :accessUsers showUser = ':showUser' ",
		      		'params' => array(
		               ':accessUsers'=>$accessUsers,
		               ':showUser'=>'Y'
	   				)
				);
				$historyData = DB::getArray("?:history", "historyID", $where); 
				$error = 'task_cleared';
				$errorMsg = 'Task cleared by user';

				if(!empty($historyData) && is_array($historyData)){
					foreach($historyData as $key => $history){
						updateHistory(array("status" => "error", "error" => $error,'userIDCleared'=>$GLOBALS['userID']), $history['historyID'], array("status" => "error", "errorMsg" => $errorMsg));
					}
				}
    		}elseif($params['clearWhat'] == 'searchList'){
    			$where = "showUser='Y'";
    			if(!empty($params['dates'])){
					$dates 		= explode('-', $params['dates']);
					$fromDate 	= strtotime(trim($dates[0]));
					$toDate		= strtotime(trim($dates[1]));
					if(!empty($fromDate) && !empty($toDate) && $fromDate != -1 && $toDate != -1){
						$toDate += 86399;
						$where .= " AND microtimeAdded >= ".DB::esc($fromDate)." AND  microtimeAdded <= ".DB::esc($toDate)." ";
					}
				}

				$getKeyword = "";
				if(!empty($params['getKeyword'])){
					$keyword = "'".implode("','", explode(',', DB::esc($params['getKeyword']) ) )."'" ;
					$getKeyword = " AND type IN (".$keyword.") ";
				}
				
				if(!empty($params['userID'])){
					$where .= " AND userID = '".DB::esc($params['userID'])."' ";
				}
				$where2 = " ";
				if(empty($params['searchByUser']) ){
					setHook('historyHTML', $where2);
				}
				if( trim($where2.$where.$getKeyword) != "showUser='Y'" ){
					$historyIDs = DB::getFields("?:history", "historyID", $where2.$where.$getKeyword );
					self::clearHistoryByIDs($historyIDs);
					// $historyIDs = implode("','", $historyIDs);
					// DB::delete("?:history", "historyID IN ('".$historyIDs."')");
					// DB::delete("?:history_additional_data", "historyID IN ('".$historyIDs."')");
					// DB::delete("?:history_raw_details", "historyID IN ('".$historyIDs."')");
				}else{
					DB::delete("?:history","1");
					DB::delete("?:history_additional_data","1");
					DB::delete("?:history_raw_details","1");
					
					DB::doQuery("OPTIMIZE TABLE `?:history`");
					DB::doQuery("OPTIMIZE TABLE `?:history_additional_data`");
					DB::doQuery("OPTIMIZE TABLE `?:history_raw_details`");
				}
    		}elseif($params['clearWhat'] == 'autoDeleteLog' ){
				if(!empty($params['time'])){
					$where = " microtimeAdded <= '".DB::esc($params['time'])."' ";
	    			$accessUsers = " ";
					setHook('historyHTML', $accessUsers);
					if($accessUsers != " ") $accessUsers = " AND ".DB::esc($accessUsers);
					$historyIDs = DB::getFields("?:history", "historyID", $where.$accessUsers );
					self::clearHistoryByIDs($historyIDs);
					// if(!empty($historyIDs)){
					// 	$historyIDs = implode("','", $historyIDs);
					// 	DB::delete("?:history", "historyID IN ('".$historyIDs."')");
					// 	DB::delete("?:history_additional_data", "historyID IN ('".$historyIDs."')");
					// 	DB::delete("?:history_raw_details", "historyID IN ('".$historyIDs."')");
		
					// 	DB::doQuery("OPTIMIZE TABLE `?:history`");
					// 	DB::doQuery("OPTIMIZE TABLE `?:history_additional_data`");
					// 	DB::doQuery("OPTIMIZE TABLE `?:history_raw_details`");
					// }
					if(!empty($params['LastAutoDeleteLogTime'])){
						$now = $params['LastAutoDeleteLogTime'];
					}else{
						$now = time();
					}
					updateOption('LastAutoDeleteLogTime', $now);
				}
    		}elseif($params['clearWhat'] == 'singleAct' ){
    			if(!empty($params['actionID'])){
    				$where = array(
			      		'query' =>  "actionID = ':actionID'",
			      		'params' => array(
			               ':actionID'=>$params['actionID']
		   				)
					);
    				$historyIDs = DB::getFields("?:history", "historyID", $where );
    				self::clearHistoryByIDs($historyIDs);
    			}
    		}
    	}
	}

	public static function autoCheckAndDeleteLog($params){
		$settings = Reg::get('settings');
		if(isset($settings['autoDeleteLog']) && !empty($settings['autoDeleteLog'])){
			$last = getOption('LastAutoDeleteLogTime');
			if(!empty($last)){
				$now = time();
				if(date("DDMMYY",$last) != date("DDMMYY",$now) && $last < $now){
					$days = (int)$settings['autoDeleteLog'];
					$till = $days*24*60*60;
					$toDelete = $now - $till;
					$delParams = array();
					$delParams['clearWhat'] = 'autoDeleteLog';
					$delParams['time'] = $toDelete;
					$delParams['LastAutoDeleteLogTime'] = $now;
					self::clearHistoryTasks($delParams);
				}
				
			}else{
				updateOption('LastAutoDeleteLogTime', 1);
			}
		}
	}

	public static function getAccessibleUsers($params){
    	$accessUsers = " ";
    	setHook('historyHTML', $accessUsers);
    	$where = array(
      		'query' =>  ":accessUsers 1",
      		'params' => array(
               ':accessUsers'=>$accessUsers
				)
		);
    	$usersTmp = DB::getArray("?:users", "userID as id,name,email", $where); 
    	$users = array();
    	foreach ($usersTmp as $user) {
                if(!empty($user['name']))
                    $users[$user['id']] = $user['name'];
                else
                    $users[$user['id']] = $user['email'];
    	}
    	return $users;
    	
	}
	public static function getActivityCategories($params){
		$activityCats = array(
								'Backups' => array('backup'),
								'Reload_Data' => 'stats',
								'Plugins' => 'plugins',
								'Themes' => 'themes',
								'Site_Actions' => 'site',
								'Update_Actions' => 'PTC',
								'Update_Client' => 'clientPlugin'
							);
		//addons exclusive
		$activityCatsAddon = array(
								'Backup_to_Repository' => 'backupRepository',
								'Schedule_Backup' => 'scheduleBackup',
								'Broken_Links' => 'brokenLinks',
								'Bulk_Publish' => array('post','page','links','posts','pages'),
								'Client_Plugin_Branding' => 'clientPluginBranding',
								'Code_Snippets' => 'codeSnippets',
								'File_Editor' => 'fileEditor',
								'Google_Webmasters' => 'googleWebMasters',
								'Install_Clone_WP' => 'installClone',
								'iThemes_Security' => 'ithemesSecurity',
								'Malware_Scanning_Sucuri' => 'malwareScanningSucuri',
								'Manage_Comments' => 'comments',
								'Manage_Users' => 'manageUsers',
								'Wordfence' => 'wordFence',
								'WP_Optimize' => 'wp',
								'Staging' => 'staging'
							);
		//nameConversions
		$activityName = array(
								'backupRepository' => 'Backup_to_Repository',
								'scheduleBackup' => 'Schedule_Backup',
								'brokenLinks' => 'Broken_Links',
								'bulkPublish' => 'Bulk_Publish',
								'clientPluginBranding' => 'Client_Plugin_Branding',
								'codeSnippets' => 'Code_Snippets',
								'fileEditor' => 'File_Editor',
								'googleWebMasters' => 'Google_Webmasters',
								'installClone' => 'Install_Clone_WP',
								'ithemesSecurity' => 'iThemes_Security',
								'malwareScanningSucuri' => 'Malware_Scanning_Sucuri',
								'manageComments' => 'Manage_Comments',
								'manageUsers' => 'Manage_Users',
								'wordFence' => 'Wordfence',
								'wpOptimize' => 'WP_Optimize',
								'staging' => 'Staging'
							);

		$addonDetails = Reg::get('addonDetails');
		foreach ($addonDetails as $name => $addon) {
			if(isset($activityCatsAddon[ $activityName[$addon['slug'] ] ] ) ){
				if($addon['slug'] == 'backupRepository' || $addon['slug'] == 'scheduleBackup'){
					array_push($activityCats['Backups'], $activityCatsAddon[ $activityName[$addon['slug'] ] ] );
				}else{
					$activityCats[ $activityName[$addon['slug'] ] ] = $activityCatsAddon[ $activityName[$addon['slug'] ] ];
				}
			}
		}

		return $activityCats;
	}

		
	public static function smtpSaveSettings($parmas){
		if(!empty($parmas) && is_array($parmas)){
			return updateOption("smtpSettings", serialize($parmas));
		}
	}

	public static function smtpGetSettings(){
		$datas = getOption("smtpSettings");
		return @unserialize($datas);
	}
	
	public static function saveTestSmtpSettings($params){
		if(!empty($params) && is_array($params)){
			return updateOption("emailTempSettings", serialize($params));
		}
		return true;
	}
	
	public static function testSendMail(){
		$isSent = sendAppMail(array('userID' => $GLOBALS['userID']), '/templates/email/testEmail.tpl.php', array('isTest' => 1));
		if(!empty($isSent)){
			addNotification($type='N', $title='Test Mail', $message='E-Mail Sent Successfully.', $state='U', $callbackOnClose='', $callbackReference='');
		}
	}
	public static function appDirPermission(){ 
		$directoryPermission['updates'] = 0;
		$directoryPermission['uploads'] = 0;
		$getTempDirPermission = getTempDir(false);
		if (is_writable($getTempDirPermission)) {
			$directoryPermission['updates'] = 1;  
		}
		if (is_writable(APP_ROOT.'/uploads')) {
			$directoryPermission['uploads'] = 1;
		}
		return $directoryPermission;
	}
	public static function isConfigWritable(){
		return isConfigWritable();
	}
	public static function closeBetaWelcome(){
		updateOption('showBetaWelcome', 0);
	}
	public static function close2_7AddonUpdatePopup(){
		updateOption('show2_7AddonUpdatePopup', 0);
	}
	public static function autoCheckAndDeleteLoginLog(){
		$last = getOption('LastAutoDeleteLoginLogTime');
		if(!empty($last)){
			$now = time();
			if(date("DDMMYY",$last) != date("DDMMYY",$now) && $last < $now){
				$days = 90;
				$till = $days*24*60*60;
				$toDelete = $now - $till;
				self::clearLoginHistory($toDelete);
			}
			
		}else{
			updateOption('LastAutoDeleteLoginLogTime', 1);
		}
	}
	public static function clearLoginHistory($toDelete){
		if(!empty($toDelete)){
			DB::delete("?:login_logs", "time <='".$toDelete."'");
			DB::doQuery("OPTIMIZE TABLE `?:login_logs`");
			updateOption('LastAutoDeleteLoginLogTime', time());
		}
	}

	public static function insertFireQueue($requiredData){
		$tempArray['requiredData'] = $requiredData;
		DB::insert("?:temp_storage", array('type' => 'fireQueue', 'paramID' => NULL, 'time' => time(), 'data' =>  serialize($tempArray)));
	}

	public static function getFireQueue(){
		$compactVars = DB::getField("?:temp_storage", "data", "type = 'fireQueue' ORDER BY ID ASC LIMIT 1 ");
		DB::delete("?:temp_storage", "type = 'fireQueue' ORDER BY ID ASC LIMIT 1 ");
		return unserialize($compactVars);
	}

	public static function processFireQueue(){
		$tempData = self::getFireQueue();
		if (empty($tempData) || !is_array($tempData)) {
			return '';
		}
		foreach ($tempData["requiredData"] as $key => $value) {
			$functionName = $key;
			$requiredData = $value;
		}
		if (empty($requiredData) || !is_array($requiredData)) {
			return '';
		}
		$data[$functionName] = self::requiredData($requiredData);
		return $data;
	}
	public static function autoFillInstallCloneCommonCpanel($params){
		return autoFillInstallCloneCommonCpanel($params);
	}
	public static function decodeRequestParameters($params){
		if(!empty($params) && is_array($params)){
			foreach ($params as $param=>$value){
				if(is_array($value)){
					$value = self::decodeRequestParameters($value);
					$params[$param] = $value; 
				} 
				else {
					if(strpos($param, '_b64encoded') !== false){
						$value = @base64_decode($value);
						$decodedParamName = str_replace('_b64encoded', '', $param);
						unset($params[$param]);
						$params[$decodedParamName] = $value;
					}
				}
			}
			return $params;
		}
		return $params;
	}

	public static function getSitesByGroupID($params){
		$groupDetails = self::getGroupsSites();
		if (userStatus() != "admin") {
			$where = array(
	      		'query' =>  "userID = :userID",
	      		'params' => array(
	               ':userID'=>$GLOBALS['userID']
					)
			);
			$speSiteIDs = array();
			$siteIDs = DB::getFields('?:user_access', 'siteID', $where);
			$group = $groupDetails[$params['groupID']];
			$groupSiteIDs = $group['siteIDs'];
			foreach ($siteIDs as $sKey => $siteID) {
				foreach ($groupSiteIDs as $gKey => $gSiteID) {
					if ($siteID == $gSiteID) {
						array_push($speSiteIDs, $siteID);
					}
				}
			}
			if (!empty($speSiteIDs)) {
				return array("siteIDs" =>$speSiteIDs);
			}
		} else{
			return $groupDetails[$params['groupID']];
		}
	}

	public static function printGroupsForReloaData(){
		return printGroupsForReloaData();
	}

	public static function getConversionNeededTableNames(){
		return unserialize(getOption('showConversionNeededTableNames'));
	}

	private function checkDataIsValid($action){
		//Restricted function access
		$functions = array('addFunctions');
		if(!in_array($action, $functions)){
			return true;
		}
		return false;

	}

}