<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2015 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

#class providing functions for getting favicon

class Favicon{

	public static $localPath 		= 'uploads/favicon/';
	public static $defaultTimeout 	= 5; //sec
	public static function getFaviconsCronJob($cronStartTime){
		if(self::checkFolderAccessibility() === false){
			return false;
		}
		$sitesData = DB::getArray("?:sites", "siteID, favicon, URL, lastFaviconUpdate", "type = 'normal' ORDER BY siteID ASC");
		foreach ($sitesData as $siteData) {
			if(($cronStartTime + CRON_TIMEOUT) > time()){ 
				self::validateFavicon($siteData);
			}
		}		
	}
	public static function refreshFaviconSite($siteID){
		if(self::checkFolderAccessibility() === false){
			return false;
		}
		
		$where = array(
		      		'query' =>  "siteID=':siteID' AND type = 'normal'",
			      	'params' => array(
		               ':siteID'=>$siteID
					)
				);
		$siteData = DB::getArray("?:sites", "siteID, favicon, URL, lastFaviconUpdate", $where);
		self::getFavicon($siteData[0]);
	}
	public static function getFaviconsPanelIdle(){
		if(self::checkFolderAccessibility() === false){
			return false;
		}
		$limit = 10;	
		$limitChecker=0;
		$sitesData = DB::getArray("?:sites", "siteID, favicon, URL, lastFaviconUpdate", "type = 'normal' ORDER BY siteID ASC");
		foreach ($sitesData as $siteData) {
			if ($limitChecker < $limit) {
				if(self::validateFavicon($siteData) != false){
					$limitChecker++;
				}
			}else {
				break;
			}
		}
	}

	public static function checkFolderAccessibility(){
		if(self::checkFaviconFolderExist() === false){
			return false;
		}
		return true;
	}

	public static function checkLastFaviconAppNotificationTime(){
		$failedTime = self::getLastFaviconAppNotificationTime();
		$retryTime = 60 * 60 * 24 * 14; // send notification again after two weeks
		$failedTime += $retryTime;
		if ($failedTime <= time()) {
			return true;
		}
		return false;
	}

	public static function checkFaviconFolderExist(){
		if (!file_exists(APP_ROOT."/".self::$localPath)) {
			$status = self::createFolder();
			if(!$status){
				return false;
			}
		}
		if (is_writable(APP_ROOT."/".self::$localPath)) {
			return true;
		}
		self::makeNotification(2);
		return false;
	}

	public static function validateFavicon($siteData){
		$checkUpdateFavicon	= 7*24*60*60; // 7 days
		$checkAddNewFavicon = 2*24*60*60; // 2 days    
		$lastFaviconUpdate 	= $siteData['lastFaviconUpdate']; 
		$faviconPath 		= $siteData['favicon'];
		$timeDifference 	= time() - $lastFaviconUpdate ;
		
		if (($faviconPath == 'default' && $timeDifference >= $checkAddNewFavicon )|| $timeDifference >= $checkUpdateFavicon || ($lastFaviconUpdate == 0 || $faviconPath == NULL)) {
			self::getFavicon($siteData);
		} else {
			return false;
		}
	}

	public static function getFavicon($siteData){
		if(self::getFaviconFromPageContent($siteData) !== false){
			return '';
		}
		if(self::getFaviconFromRootPath($siteData) !== false ){
			return '';
		}
		self::defaultFavicon($siteData['siteID']);
	}

	public static function getFaviconFromRootPath($siteData) {
		$URL 	= $siteData['URL']."favicon.ico";
		$siteID = $siteData['siteID'];
		$lastFaviconUpdate = $siteData['lastFaviconUpdate']; 
		list( , , , $curlInfo,) = doCall($URL, $data = '', self::$defaultTimeout);
	   	$curlInfo = $curlInfo['info'];
	    if ($curlInfo['size_download'] > 1 && $curlInfo['size_download'] > $curlInfo['download_content_length']) {
	        $downloadSize = $curlInfo['size_download'];
	    } elseif ($curlInfo['download_content_length'] > 1) {
	        $downloadSize = $curlInfo['download_content_length'];
	    } 
		if (self::isImageFound($curlInfo['content_type'])) {
		    if ($curlInfo['http_code'] == 200 && $downloadSize > 1) {
		       	return self::storeFaviconAndDBUpdate($URL, $siteID, $lastFaviconUpdate);	
		     } else {
				return false;
		     }
	 	} else {
			return false;
	 	}
	}

	public static function isImageFound($contentType){
		if (strstr($contentType, "image") !== false || strstr($contentType, "text/plain") !== false) {
	        return true;
	    }else {
	    	return false;
	    }
	}

	public static function getFaviconFromPageContent($siteData) {
		$siteID = $siteData['siteID'];
		$lastFaviconUpdate = $siteData['lastFaviconUpdate'];
		$faviconNotFound = 0;
		$URL 	= $siteData['URL'];

		list($HTMLContent, , , $curlInfo,) = doCall($URL, $data = '', self::$defaultTimeout);
	   	$curlInfo = $curlInfo['info'];
		if (self::isFaviconFound($curlInfo['http_code'],$HTMLContent)) {
        	$faviconURL = self::parseHTMLContent($HTMLContent, $URL);
        	if($faviconURL){
        		return self::storeFaviconAndDBUpdate($faviconURL, $siteID, $lastFaviconUpdate); 
        	} else {
        		$faviconNotFound = 1 ;
        	}       
		} else {
			$faviconNotFound = 1 ;
		}
		if ($faviconNotFound == 1) {
			return false;
		}
	}
	public static function isFaviconFound($httpStatus,$HTMLContent){
		if ($httpStatus == 200 && (stripos($HTMLContent, "icon") !== false || stripos($HTMLContent, "shortcut icon") !== false)) {
			return true;
		} else{
			return false;
		} 
	}

	public static function parseHTMLContent($HTMLContent, $URL) {
		include_once(APP_ROOT."/lib/htmlParser/simple_html_dom.php");
		$DOM = str_get_html($HTMLContent);
		$relTypesofFavicon = array('link[rel*=shortcut icon]', 'link[rel*=icon]');
		$faviconFound = 0;
		foreach ($relTypesofFavicon as $relTypeofFavicon) {
			if (!$faviconFound) {
				foreach($DOM->find($relTypeofFavicon) as $element) {
	   				$faviconURL = $element->href;
	   				if ($faviconURL) {
	   					$faviconFound = 1;
	   					break;
	   				}
	  			}
  			} else {
  				break;
  			}
		}
		if (isset($faviconURL) && !empty($faviconURL)) {
		    return  self::findFaviconURLType($faviconURL, $URL);
  		}else {
	        return false;
	    }	
	}

	public static function storeFaviconAndDBUpdate($URL,$siteID, $lastFaviconUpdate){
		$currentTime = time();
		$fileName  = $siteID."_".$currentTime;
	    $localPath = self::$localPath.''.$fileName . '.ico';
            $localSavePath = APP_ROOT.'/'.self::$localPath.''.$fileName . '.ico';
	    $result = self::storeFavicontoLocalstorage($URL, $localSavePath, $siteID, $lastFaviconUpdate); //to store icon into local
	  	if ($result) {
	 		return self::updateFavicon($siteID, $localPath, $lastFaviconUpdate, $currentTime, true); 	
	  	} else {
	 		return self::updateFavicon($siteID, $localPath, $lastFaviconUpdate, $currentTime, false); 	
	  	}
	}
	public static function createFolder(){
		$localPath = self::$localPath;
		if(!file_exists($localPath)){
			 if(!mkdir(self::$localPath, 0755, true)){
			 	self::makeNotification(1);
				return false;
			 }
		}
		return true;
	}
	public static function makeNotification($type){
		if (self::checkLastFaviconAppNotificationTime()) {
			if ($type == 1) {
				addNotification($type='E', $title='Cannot create folder to save favicons', $message='As of v2.6, we are storing your website\'s favicons locally to avoid multiple calls. But we do not have enough permission to create a new folder to save them. <br><br>Please change the default folder permission for the <strong>[IWP Admin Panel]/uploads</strong> folder to 755 or 777.', $state='U', $callbackOnClose='', $callbackReference='');
			} else if($type == 2){
				addNotification($type='E', $title='Cannot store favicons', $message='As of v2.6, we are storing your website\'s favicons locally to avoid multiple calls. But we do not have enough permission to save them. <br><br>Please change the default folder permission for the <strong>[IWP Admin Panel]/uploads</strong> folder to 755 or 777.', $state='U', $callbackOnClose='', $callbackReference='');
			}
			self::saveLastFaviconAppNotificationTime();
		}
	}

	public static function updateFavicon($siteID, $localPath, $lastFaviconUpdate, $currentTime,$status){
		$where = array(
		      		'query' =>  "siteID=:siteID",
			      	'params' => array(
		               ':siteID'=>$siteID
					)
				);
	  	if ($status) {
	  		DB::update("?:sites", array( //update local path
	        'favicon' => $localPath,
	        'lastFaviconUpdate' => $currentTime
		    ), $where); 
		    $deleteFavicon = $siteID."_".$lastFaviconUpdate;
		    $deleteFavicon = self::$localPath.''.$deleteFavicon.'.ico';
		    if (file_exists($deleteFavicon)) {
		       		unlink($deleteFavicon);
			}	
	  	} else {
	  		DB::update("?:sites", array( //update local path
	        'lastFaviconUpdate' => time()
		    ), $where); 
		    
	  	} 	  
	  	return true;
	}
	public static function findFaviconURLType($faviconURL, $URL){
			if (!empty($faviconURL)) {
			$HREFInfo = parse_url($faviconURL);
	        if (!empty($HREFInfo['scheme']) && !empty($HREFInfo['host']) && !empty($HREFInfo['path'])) {
	        	return self::verifyFaviconURL($faviconURL);
	        } else if (empty($HREFInfo['scheme']) && empty($HREFInfo['host'])) {
	        	return self::relativeURL($HREFInfo, $URL);
	        } else if (empty($HREFInfo['scheme']) && (!empty($HREFInfo['host']) && !empty($HREFInfo['path']))) {
	    		return self::absoluteURLWithoutScheme($HREFInfo);
	        } else {
	            return false;
	    	} 
	    }
	}

	public static function relativeURL($HREFInfo, $URL){
        $faviconURL = $URL.''.ltrim($HREFInfo['path'], '/');
        return self::verifyFaviconURL($faviconURL);
	}

	public static function absoluteURLWithoutScheme($HREFInfo){
		$faviconURL = $HREFInfo['host'].''.$HREFInfo['path'];
		return self::verifyFaviconURL($faviconURL);
	}

	public static function verifyFaviconURL($faviconURL){
		list(, , , $curlInfo,)  	= doCall($faviconURL, $data = '', self::$defaultTimeout);
	    $curlInfo = $curlInfo['info'];
	    if ($curlInfo['http_code'] == 200 && strstr($curlInfo['content_type'], "image") != null) {
	      return $faviconURL;
        } else {
            return false;
        }
	}
	public static function storeFavicontoLocalstorage($URL, $saveTo)
	{	
		
		list($faviconData, , , $curlInfo, )  = doCall($URL, $data = '', self::$defaultTimeout);
		$curlInfo = $curlInfo['info'];
	    if ($curlInfo['size_download'] > 1 && $curlInfo['size_download'] > $curlInfo['download_content_length']) {
	        $downloadSize = $curlInfo['size_download'];
	    } elseif ($curlInfo['download_content_length'] > 1) {
	        $downloadSize = $curlInfo['download_content_length'];
	    } 

		if (self::isImageFound($curlInfo['content_type'])) {
			if ($curlInfo['http_code'] == 200 && $downloadSize > 1) {
		        $fp = fopen($saveTo, 'x');
				if ($fp === false) {
					return false;
				}
				$writeStatus = fwrite($fp, $faviconData);
				if ($writeStatus === false) {
					return false;
				}
			    fclose($fp);
                            return (file_exists($saveTo))?true:false;
		     } else {
		     	return false;
		     }
		} else {
			return false;
		}
	}

	public static function saveLastFaviconAppNotificationTime(){
		return updateOption('lastFaviconAppNotificationTime', time());
	}

	public static function getLastFaviconAppNotificationTime(){
		return getOption('lastFaviconAppNotificationTime');
	}

	public static function defaultFavicon($siteID){
		$where = array(
		      		'query' =>  "siteID=:siteID",
			      	'params' => array(
		               ':siteID'=>$siteID
					)
				);
		$time = time();
	    DB::update("?:sites", array(//update local path
	            'favicon' => 'default',
	            'lastFaviconUpdate' => $time
	    ), $where);
	}
	public static function removeFavicon($siteID){
		$where = array(
		      		'query' =>  "siteID=':siteID'",
			      	'params' => array(
		               ':siteID'=>$siteID
					)
				);
		$removeSiteData = DB::getArray("?:sites", "favicon, lastFaviconUpdate",$where);
   		$deleteFavicon = $removeSiteData[0]['favicon'];
		 if (file_exists($deleteFavicon)) {
	       		unlink($deleteFavicon);
		}	
	}
	public static function clearAllFavicon(){
		$faviconFolderPath = APP_ROOT.'/uploads/favicon/';
		self::clearFilesFromFolder($faviconFolderPath);
		$isDone = DB::UPDATE("?:sites",array('favicon' => NULL , 'lastFaviconUpdate' => 0 ), "1");
		if(!$isDone){ return false; }
	}
	public static function clearFilesFromFolder($path){
		$files = @glob($path."*.ico");
		if (empty($files)) {
			return false;
		}
		foreach ($files as $file) {
			@unlink($file);
		}
	}

} 