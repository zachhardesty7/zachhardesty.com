<?php
class cacheCommon{
	private $cachePath = '';
	private $currentHashKey = '';
	private $cacheFilePath = '';
	private $cacheFileURL = '';
	private $head;
	function __construct(){
		$this->cacheFolderPath = APP_ROOT.'/uploads/cache/';
		$this->cacheFolderURL = 'uploads/cache/';
	}

	function checkAccessibility($type = NULL){
		if($this->checkFolderAccessibility() === true && $this->checkFileAccessibility($type) === true){
			return true;
		}
		return false;
	}

	function checkFolderAccessibility(){
		if($this->checkCacheFolderExist() === false){
			return false;
		}
		return true;
	}

	function checkFileAccessibility($type = NULL){
		if($this->checkCacheFilesExist($type) === false){
			return false;
		}
		return true;
	}

	function checkFilesPermission($file){
		clearstatcache();
		$data = @substr(sprintf('%o', fileperms($file)), -3);
		if( $data >= 644){
			return true;
		} else {
			chmod($file, 0644);
			return false;
		}
	}

	function checkCacheFilesExist($type = NULL){
		$cssFile = $this->cacheFilePath.'.css';
		$jsFile  = $this->cacheFilePath.'.js';
		if ($type === 'panel') {
			if($this->checkCacheFileExist($cssFile) === true){ // checking only css file
				return true;
			}
			return false;
		}
		if($this->checkCacheFileExist($cssFile) === true && $this->checkCacheFileExist($jsFile) === true){
			return true;
		}
		return false;
	}

	function checkCacheFileExist($file){
		if (!file_exists($file)) {
			if($this->createCacheFile($file) === false){
				return false;
			}
			if($this->checkFilesPermission($file) !== false){
				return true;
			}
			if($this->checkFilesPermission($file) === false){
				return false;
			}
		}
		return true;
	}

	function createCacheFile($filePath){
		$CommentLine 	= "\n\n/*________________File created________________*/\n\n";
		return @file_put_contents($filePath, $CommentLine, FILE_APPEND);
	}

	function checkCacheFolderExist(){
		if (!file_exists($this->cacheFolderPath)) {
			$status = $this->createCachefolder();
			if(!$status){
				return false;
			}
		}
		return true;
	}

	function createCachefolder(){
		return @mkdir($this->cacheFolderPath);
	}

	function checkEligibilityForCache(){
		$lastfailedTime = getOption('cacheProcessPushDataFailed');
		$retryTime = 60 * 60 * 24 * 7; // try cache every 1 week
		$lastfailedTime += $retryTime;
		if ($lastfailedTime <= time()) {
			return true;
		}
		return false;
	}

	function createCacheFilePath(){
		$this->cacheFilePath =  $this->cacheFolderPath.$this->currentHashKey;
	}

	function createCacheFileURL(){
		$this->cacheFileURL =  $this->cacheFolderURL.$this->currentHashKey;
	}

	function validateHashKey($newHashKey, $type){
		if ($type == 'addons') {
			$unserializedHashKeys = getOption('cacheProcessAddonHashKeys');
		} else if($type == 'panel') {
			$unserializedHashKeys = getOption('cacheProcessPanelHashKeys');
		}
		if (!empty($unserializedHashKeys)) {
			$hashKeys = unserialize($unserializedHashKeys);
			if (!empty($hashKeys) && is_array($hashKeys) && array_key_exists($newHashKey, $hashKeys)) {
				return true;
			}
		}
		return false;
	}

	function setHashKey($newHashKey){
		$this->currentHashKey = $newHashKey;
		$this->createCacheFilePath();
		$this->createCacheFileURL();
	}

	function pushDataIntoCSSFile($filePath, $slug){
		$file = APP_ROOT.'/'.$filePath;
		if (!@is_readable($file)) {
			return false;
		}
		$fileData = @file_get_contents($file).PHP_EOL;
		$replaceURL = APP_URL.dirname($filePath).'/../';
		$fileData = str_replace(array('(../', "('../", '("../'), array('('.$replaceURL, "('".$replaceURL, '("'.$replaceURL), $fileData);
		
		$startComment	= "\n\n/*________________Starts - ".$filePath."________________*/\n\n";
		$endComment 	= "\n\n/*__________________Ends - ".$filePath."________________*/\n\n";
		$data =  @file_put_contents($this->cacheFilePath.'.css', $startComment.''.$fileData.''.$endComment, FILE_APPEND);
		return $data;
	}

	function pushIntoJSFile($filePath, $slug = NULL){
		$file = APP_ROOT.'/'.$filePath;
		if (!@is_readable($file)) {
			return false;
		}
		$fileData = @file_get_contents($file).PHP_EOL;
		$startComment	= "\n\n/*________________Starts - ".$filePath."________________*/\n\n";
		$endComment 	= "\n\n/*__________________Ends - ".$filePath."________________*/\n\n";
		return @file_put_contents($this->cacheFilePath.'.js', $startComment.''.$fileData.''.$endComment, FILE_APPEND);
	}

	function pushDataIntoCacheFile($type, $filePath, $minify, $slug = NULL){
		if(empty($filePath)){
			return false;
		}
		if(!defined("DISABLE_MINIFY") && !strstr($filePath,'.min.') && $minify == true ){
			$fileDetails = pathinfo($filePath);
			$filePath = $fileDetails['dirname'].'/'.$fileDetails['filename'].'.min.'.$fileDetails['extension'];
		}
		if ($type === 'css') {

			if($this->pushDataIntoCSSFile($filePath, $slug) === false){
				return false;
			}
			return true;
		} else if($type === 'js'){
			if($this->pushIntoJSFile($filePath, $slug) === false){
				return false;
			}
			return true;
		}
	}

	function pushDataFailed(){
		return updateOption('cacheProcessPushDataFailed',time());
	}

	function getCacheFile($type){
		if ($type === 'css' && file_exists($this->cacheFilePath.'.css')) {
			return $this->prepareCSSRef($this->cacheFileURL.".css");
		}
		if ($type === 'js' && file_exists($this->cacheFilePath.'.js')) {
			return $this->prepareJSRef($this->cacheFileURL.'.js');
		}
		return false;
	}

	function processFilesCommon($filesDetail, $slug = NULL){
		foreach ($filesDetail as $fileDetails) {
			if (empty($fileDetails) || !is_array($fileDetails)) {
				continue;
			}
			foreach ($fileDetails as $fileType => $fileMeta) {
				if ($fileType == 'tp') {
					continue;
				}
				if(isset($fileMeta['dontMinify']) && $fileMeta['dontMinify'] == true ){
					$minify = false;
				} else {
					$minify = true;
				}
				$lastPushStatus = $this->pushDataIntoCacheFile($fileType, $fileMeta['href'], $minify, $slug);
				if ($lastPushStatus === false) {
					$this->pushDataFailed();
					return 'dataPushFailed';
				}
			}
		}
	}

	function prepareRef($fileType, $filePath, $minify){
		if(!defined("DISABLE_MINIFY") && !strstr($filePath,'.min.') && $minify == true){
			$fileDetails = pathinfo($filePath);
			$filePath = $fileDetails['dirname'].'/'.$fileDetails['filename'].'.min.'.$fileDetails['extension'];
		}
		if ($fileType === 'css') {
			return $this->prepareCSSRef($filePath);
		} else if ($fileType === 'js') {
			return $this->prepareJSRef($filePath);
		} else if ($fileType === 'tp') {
			return $filePath.PHP_EOL;
		}
	}

	function prepareCSSRef($file){
		return "<link rel='stylesheet' href='".$file.'?'.$this->currentHashKey."' type='text/css' media='all' />".PHP_EOL;
	}

	function prepareJSRef($file){
		return PHP_EOL."<script src='".$file.'?'.$this->currentHashKey."' type='text/javascript' charset='utf-8'></script>".PHP_EOL;
	}

	function fallbackCommon($filesDetail){
		$this->head = '';
		if (empty($filesDetail) || !is_array($filesDetail)) {
			return ;
		}
		foreach ($filesDetail as $fileDetails) {
			if (empty($fileDetails) || !is_array($fileDetails)) {
				continue;
			}
			foreach ($fileDetails as $fileType => $fileMeta) {
				if ($fileType == 'tp') {
					$this->head .= $this->prepareRef($fileType, $fileMeta['HTML'], false);
				} else {
					if(isset($fileMeta['dontMinify']) && $fileMeta['dontMinify'] == true){
						$minify = false;
					} else {
						$minify = true;
					}
					$this->head .= $this->prepareRef($fileType, $fileMeta['href'], $minify);
				}
			 }
		}
		return $this->head;
	}

	function clearFailedCacheFiles($key = NULL){
		if (!empty($key)) {
			$deleteFilePath = $this->cacheFolderPath.$key;
		} else {
			$deleteFilePath = $this->cacheFolderPath.$this->currentHashKey;
		}
		@unlink($deleteFilePath.'.css');
		@unlink($deleteFilePath.'.js');
	}

	function clearExpiredHash(&$hashKeys, $expiredHashKey, $type){
		unset($hashKeys[$expiredHashKey]);
		if (empty($hashKeys)) {
			$rawHashKeys = serialize('');
		} else {
			$rawHashKeys = serialize($hashKeys);
		}
		if ($type == 'addons') {
			updateOption('cacheProcessAddonHashKeys', $rawHashKeys);
		} else {
			updateOption('cacheProcessPanelHashKeys', $rawHashKeys);
		}
	}

	function clearExpiredCacheFiles($rawHashKeys, $type){
		$expireDays = 60 * 60 * 24 * 30;
		if(!empty($rawHashKeys)){
			$hashKeys = unserialize($rawHashKeys);
			$tempHashKeys = $hashKeys;
			if (!empty($hashKeys) && is_array($hashKeys)) {
				foreach ($hashKeys as $key => $time) {
					if ( ($time + $expireDays) <= time()) {
						$this->clearFailedCacheFiles($key);
						$this->clearExpiredHash($tempHashKeys, $key, $type);
					}
				}
			}
		}
	}

	function processExpiredCacheFiles(){
		$rawPanelHashKeys = getOption('cacheProcessPanelHashKeys');
		$rawAddonsHashKeys 	= getOption('cacheProcessAddonHashKeys');
		$this->clearExpiredCacheFiles($rawAddonsHashKeys, 'addons');
		$this->clearExpiredCacheFiles($rawPanelHashKeys, 'panel');
	}
}

class createAddonCache{

	private $newHashKey;
	private $currentHashKey;
	private $noHashChange;
	private $cacheCommonObj;
	private $addonsDetail;
	private $head;
	private $cacheFilesCreated;
	private $allAddons;
	private $oldAddons;
	private $addonsSlug;
	function __construct(){
		setHook('headHTMLFiles', $this->addonsDetail);
		$this->allAddons = Reg::get('addonDetails');
		$this->cacheCommonObj = new cacheCommon();

		if(is_array($this->allAddons) && !is_array($this->addonsDetail)){
			$this->oldAddons = array_keys(Reg::get('addonDetails'));
		} else if(is_array($this->allAddons) && is_array($this->addonsDetail)){
			$this->addonsSlug = array_keys($this->allAddons);
			$this->oldAddons = array_diff(array_keys(Reg::get('addonDetails')), array_keys($this->addonsDetail));
			$this->newHashKey = $this->getAddonCacheHashKey($this->addonsDetail);
			$this->cacheCommonObj->setHashKey($this->newHashKey);
			if ($this->getCacheFiles() === true) {
				$this->cacheFilesCreated = 1;
			}
		}
	}

	function initiateCacheProcess(){
		if(!is_array($this->allAddons) && !is_array($this->addonsDetail)){
			return false;
		}
		if ($this->cacheFilesCreated === 1 && !defined('DISABLE_FILE_CACHE')) {
			$this->head .= $this->processOldAddons();
			$this->updateCurrentHashKey($this->newHashKey);
			return  $this->head;
		}

		if(defined('DISABLE_FILE_CACHE') || $this->cacheCommonObj->checkEligibilityForCache() === false ||  $this->cacheCommonObj->checkAccessibility() === false){
			$this->head  =  $this->fallback($this->addonsDetail);
			$this->head .= $this->processOldAddons();
			return $this->head;
		}

		$dataPushResult = $this->processEachAddonFiles($this->addonsDetail);
		if($dataPushResult != 'AllCompleted'){
			$this->head .= $this->processOldAddons();
			$this->head .= $this->fallback($this->addonsDetail);
			return $this->head;
		}
		$this->getCacheFiles();
		$this->head .= $this->processOldAddons();
		$this->updateCurrentHashKey($this->newHashKey);
		return $this->head;
	}

	function fallback($addonsDetail){
		$this->cacheCommonObj->clearFailedCacheFiles();
		if (empty($addonsDetail) || !is_array($addonsDetail)) {
			return false;
		}
		foreach ($addonsDetail as $addonDetails) {
			if (empty($addonDetails) || !is_array($addonDetails)) {
				continue;
			}
			$this->head .= $this->cacheCommonObj->fallbackCommon($addonDetails['files']);
		}
		return $this->head;
	}

	function getCacheFiles(){
		$this->addTPFiles($this->addonsDetail);
		$cssFile = $this->cacheCommonObj->getCacheFile('css');
		if ($cssFile === false) {
			return false;
		}
		$this->head .= $cssFile;
		$jsFile = $this->cacheCommonObj->getCacheFile('js');
		if ($jsFile === false) {
			return false;
		}
		$this->head .= $jsFile;
		return true;
	}

	function getCurrentHashKey(){
		return getOption('cacheProcessAddonHashKeys');
	}

	function updateCurrentHashKey($key){
		$unserializedHashKeys = $this->getCurrentHashKey();
		if (!empty($unserializedHashKeys)) {
			$hashKeys = unserialize($unserializedHashKeys);
			$hashKeys[$key] = time();
		} else {
			$hashKeys[$key] = time();
		}
		return updateOption('cacheProcessAddonHashKeys', serialize($hashKeys));
	}

	function getAddonCacheHashKey($addonsDetail){
		$addonHash = '';
		if (empty($addonsDetail)) {
			return false;
		}
		foreach ($addonsDetail as $slug =>$addonDetails) {
			$addonHash .= $slug.'_'.$addonDetails['version'];
		}
		return md5($addonHash);
	}


	function processEachAddonFiles($addonsDetail){
		if (empty($addonsDetail) || !is_array($addonsDetail)) {
			return false;
		}
		foreach ($addonsDetail as $addonSlug => $addonDetails) {
			if (empty($addonDetails) || !is_array($addonDetails)) {
				continue;
			}
			$status = $this->cacheCommonObj->processFilesCommon($addonDetails['files'], $addonSlug);
			if ($status === 'dataPushFailed') {
				$this->fallback($this->addonsDetail);
				break;
			}
		}

		return 'AllCompleted';
	}

	function addTPFiles($addonsDetail){
		if (empty($addonsDetail) || !is_array($addonsDetail)) {
			return false;
		}
		foreach ($addonsDetail as $addonDetails) {
			if (empty($addonDetails) || !is_array($addonDetails)) {
				continue;
			}
			foreach ($addonDetails['files'] as $fileDetails) {
				if (empty($fileDetails) || !is_array($fileDetails)) {
					continue;
				}
				foreach ($fileDetails as $fileType => $fileMeta) {
					if($fileType === 'tp'){
						$this->head .= $fileMeta['HTML'].PHP_EOL;
					}
				}
			}
		}
	}

	function processOldAddons(){
		$head = '';
		if(empty($this->oldAddons) || !is_array($this->oldAddons)){
			return false;
		}
		foreach ($this->oldAddons as $addonSlug) {
			if($this->allAddons[$addonSlug]['isLoaded'] == true){
				$head .= $this->fallBackOldAddons($addonSlug);
			}
		}
		return $head;
	}

	function fallBackOldAddons($addonSlug){
		if(!file_exists(APP_ROOT.'/addons/'.$addonSlug.'/initHTMLHead.php')){
			return false;
		}
		ob_start();
		echo "\n";
		include(APP_ROOT.'/addons/'.$addonSlug.'/initHTMLHead.php');
		$head .= ob_get_clean();
		return $head;
	}
}

class createPanelCache{
	private $newHashKey;
	private $currentHashKey;
	private $noHashChange;
	private $cacheCommonObj;
	private $filesDetail;
	private $head;
	private $cacheFilesCreated;

	function __construct($filesDetail){
		$this->filesDetail = $filesDetail;
		$this->newHashKey = $this->getPanelCacheHashKey();
		$this->cacheCommonObj = new cacheCommon();
		$this->cacheCommonObj->setHashKey($this->newHashKey);
		if ($this->getCacheFiles() === true) {
			$this->cacheFilesCreated = 1;
		}
	}


	function initiateCacheProcess(){

		if ($this->cacheFilesCreated === 1 && !defined('DISABLE_FILE_CACHE')) {
			$this->updateCurrentHashKey($this->newHashKey);
			return  $this->head;
		}

		if(defined('DISABLE_FILE_CACHE') || $this->cacheCommonObj->checkEligibilityForCache() === false ||  $this->cacheCommonObj->checkAccessibility('panel') === false){
			return $this->fallback($this->filesDetail);
		}
		$this->pushIntoCacheFile();
		$this->getCacheFiles();
		$this->updateCurrentHashKey($this->newHashKey);
		return $this->head;
	}

	function pushIntoCacheFile(){
		$status = $this->cacheCommonObj->processFilesCommon($this->filesDetail, 'panel');
		if ($status === 'dataPushFailed') {
			$this->fallback($this->filesDetail);
		}
	}

	function fallback($filesDetail){
		$this->cacheCommonObj->clearFailedCacheFiles();
		return $this->cacheCommonObj->fallbackCommon($filesDetail);
	}

	function getCurrentHashKey(){
		return getOption('cacheProcessPanelHashKeys');
	}

	function updateCurrentHashKey($key){
		$unserializedHashKeys = $this->getCurrentHashKey();
		if (!empty($unserializedHashKeys)) {
			$hashKeys = unserialize($unserializedHashKeys);
			$hashKeys[$key] = time();
		} else {
			$hashKeys[$key] = time();
		}
		return updateOption('cacheProcessPanelHashKeys', serialize($hashKeys));
	}

	function loadJSFallBackMethod($filesDetail){
		return $this->cacheCommonObj->fallbackCommon($filesDetail);
	}

	function getCacheFiles(){
		$cssFile = $this->cacheCommonObj->getCacheFile('css');
		if ($cssFile === false) {
			return false;
		}
		$this->head .= $cssFile;
		return true;
	}

	function getPanelCacheHashKey(){
		return md5(APP_VERSION);
	}
}