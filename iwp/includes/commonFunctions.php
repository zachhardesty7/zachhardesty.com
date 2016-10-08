<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

function paginate($page, $total, $itemsPerPage, $paginationName='pagination'){
	
	if(empty($page) || !is_numeric($page)) $page = 1;
	
	$totalPage = ceil($total / $itemsPerPage);
	
	$prevPage = $page > 1 ? ($page - 1) : '';
	$nextPage = $page < $totalPage ? ($page + 1) : '';
	
	$pagination = array('page'		=> $page,
						'prevPage'	=> $prevPage,
						'nextPage'	=> $nextPage,
						'total'		=> $total,
						'itemPerPage'	=> $itemsPerPage,
						'totalPage'	=> $totalPage,						
						);
					
	Reg::tplSet($paginationName, $pagination);
						
	return 'LIMIT '.(($page - 1)  * $itemsPerPage).', '.$itemsPerPage;
}

function repoDoCall($URL, $data){
	
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_URL, $URL);
	//curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/plain')); 
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	$return=curl_exec($ch);
	
	return $return;
}


function doCall($URL, $data, $timeout=DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT, $options=array()) //Needs a timeout handler
{	
	$SSLVerify = false;
	$URL = trim($URL);
	//if(stripos($URL, 'https://') !== false){ $SSLVerify = true; }
	
	$HTTPCustomHeaders = array();
	
	$userAgentAppend = '';	
	if(!defined('IWP_HEADERS') || (defined('IWP_HEADERS') && IWP_HEADERS) ){
		$userAgentAppend = ' InfiniteWP';
		$HTTPCustomHeaders[] = 'X-Requested-From: InfiniteWP';
	}
	
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_URL, $URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36'.$userAgentAppend);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($SSLVerify === true) ? 2 : false );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSLVerify);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, true);

	if(!defined('REFERER_OPT') || (defined('REFERER_OPT') && REFERER_OPT === TRUE) ){
		curl_setopt($ch, CURLOPT_REFERER, $URL);
	}
        
	if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	}
	
	$contentType = 'application/x-www-form-urlencoded';
	if(!empty($options['contentType'])){
		$contentType = $options['contentType'];
	}
	$HTTPCustomHeaders[] = 'Content-Type: '.trim($contentType);//before array('Content-Type: text/plain') //multipart/form-data
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPCustomHeaders);
	
	if(!empty($options['httpAuth'])){
		curl_setopt($ch, CURLOPT_USERPWD, $options['httpAuth']['username'].':'.$options['httpAuth']['password']);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	}	
	
	if(!empty($options['useCookie'])){
		if(!empty($options['cookie'])){
			curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
		}
	}
	
	if (!ini_get('safe_mode') && !ini_get('open_basedir')){
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	}
	
	if($options['file'] == 'download' && !empty($options['filePath'])){
		$fp = fopen($options['filePath'], "w");
    	curl_setopt($ch, CURLOPT_FILE, $fp);	
	}
	
	if(!empty($data)){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, base64_encode(serialize($data)));
	}
	
	$microtimeStarted 	= microtime(true);
	$rawResponse 			= curl_exec($ch);
	$microtimeEnded 	= microtime(true);
	
	$curlInfo = array();
	$curlInfo['info'] = curl_getinfo($ch);
	if(curl_errno($ch)){
		$curlInfo['errorNo'] = curl_errno($ch);
		$curlInfo['error'] = curl_error($ch);
	}
        	
	curl_close($ch);
	
	if($options['file'] == 'download' && !empty($options['filePath'])){
		fclose($fp);
	}

	list($responseHeader, $responseBody) = bifurcateResponse($rawResponse, $curlInfo);
	
	return array($responseBody, $microtimeStarted, $microtimeEnded, $curlInfo, $responseHeader);
}

function bifurcateResponse($rawResponse, $curlInfo){  
	$header;
	$body = $rawResponse;//safety
    if(isset($curlInfo["info"]["header_size"])) { 
        $header_size = $curlInfo["info"]["header_size"];  
        $header = substr($rawResponse, 0, $header_size);   
        $body = substr($rawResponse, $header_size);
    }
    return array($header, $body); 
}

function unserializeArray($strBetArray){
	if(empty($strBetArray) || !is_array($strBetArray)){ return false; }
	$newArray = array();
	foreach($strBetArray as $key => $value){
		$newArray[$key] = unserialize($value);
	}
	return $newArray;
}

function getStrBetAll($string, $startString, $endString)
{
	$betArray = array();
	while($string){
		list($strBet, $string) = getStrBet($string, $startString, $endString);
		if(!$strBet) break;
		$betArray[] = $strBet;
	}
	return $betArray;
}

function getStrBet($string, $startString, $endString)//note endstring must be after the start string
{
	if(!$startString) { $startPos = 0; }
	else{
		$startPos = strpos($string, $startString);
		if($startPos === false) { return false; }
		$startPos = $startPos + strlen($startString);
	}
	
	if(!$endString)
	{
		$strBet = substr($string, $startPos);
		return array($strBet, substr($string, strpos($string, $strBet)));
	}
	
	$endPos = strpos($string, $endString, $startPos);
	if(!$endPos) return false;
	
	$strBet = substr($string, $startPos, ($endPos - $startPos));
	return array($strBet, substr($string, $endPos+strlen($endString)));
}


function fixObject (&$object){
  if (!is_object ($object) && gettype ($object) == 'object')
	return ($object = unserialize (serialize ($object)));
  return $object;
}

function objectToArray($o) {
	if (is_object($o)) {
			$o = get_object_vars($o);
	}
	if (is_array($o)) {
		return array_map(__FUNCTION__, $o);
	}
	else {
		// Return array
		return $o;
	}
}


function callURLAsync($url, $params=array()){

    $post_params = array();
	foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts = parse_url($url);
	$host = $parts['host'];

	if (($parts['scheme'] == 'ssl' || $parts['scheme'] == 'https') && extension_loaded('openssl')){
		$parts['host'] = "ssl://".$parts['host'];
		$parts['port'] = 443;
		error_reporting(0);
	}
	elseif($parts['port']==''){
		$parts['port'] = 80;
	}	
	  
    $fp = @fsockopen($parts['host'], $parts['port'], $errno, $errstr, 30);
	if(!$fp) return array('status' => false, 'resource' => !empty($fp) ? true : false, 'errorNo' => 'unable_to_intiate_fsock', 'error' => 'Unable to initiate FsockOpen');
	if($errno > 0) return array('status' => false, 'errorNo' => $errno, 'error' => $errno. ':' .$errstr);

	$settings = Reg::get('settings');

    $out = "POST ".$parts['path']." HTTP/1.0\r\n";
    $out.= "Host: ".$host."\r\n";
	if(!empty($settings['httpAuth']['username'])){
		$out.= "Authorization: Basic ".base64_encode($settings['httpAuth']['username'].':'.$settings['httpAuth']['password'])."\r\n";
	}
	$out.= "User-agent: " . "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0". "\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n"; 
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    $is_written = fwrite($fp, $out);
	if(!$is_written){
		return array('status' => false, 'writable' => false);
	}
	
	/*if($settings['enableFsockFget'] == 1){
		fgets($fp, 128);
	}*/
	
    fclose($fp);
	return array('status' => true);
}

function fsockSameURLConnectCheck($url, $fget=true){
	
	if($fget){
		$params=array('check' =>  'sameURL');	
	}
	else{
		$fsockSameURLCheckUsingDBValue =  uniqid('fsock_', true);
		$params=array('check' =>  'sameURLUsingDB', 'fsockSameURLCheckUsingDBValue' => $fsockSameURLCheckUsingDBValue);
	}
	
	$post_params = array();
	foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);
	
	$parts = parse_url($url);
	$host = $parts['host'];

	if (($parts['scheme'] == 'ssl' || $parts['scheme'] == 'https') && extension_loaded('openssl')){
		$parts['host'] = "ssl://".$parts['host'];
		$parts['port'] = 443;
		error_reporting(0);
	}
	elseif($parts['port']==''){
		$parts['port'] = 80;
	}
	  
    $fp = @fsockopen($parts['host'], $parts['port'], $errno, $errstr, 30);
	if(!$fp) return array('status' => false, 'resource' => !empty($fp) ? true : false, 'errorNo' => 'unable_to_intiate_fsock', 'error' => 'Unable to initiate FsockOpen');
	if($errno > 0) return array('status' => false, 'errorNo' => $errno, 'error' => $errno. ':' .$errstr);

	$settings = Reg::get('settings');
	
    $out = "POST ".$parts['path']." HTTP/1.0\r\n";
    $out.= "Host: ".$host."\r\n";
	if(!empty($settings['httpAuth']['username'])){
		$out.= "Authorization: Basic ".base64_encode($settings['httpAuth']['username'].':'.$settings['httpAuth']['password'])."\r\n";
	}
	$out.= "User-agent: " . "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0". "\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
	
    if (isset($post_string)) $out.= $post_string;
	
    $is_written = fwrite($fp, $out);
	if(!$is_written){
		return array('status' => false, 'writable' => false, 'errorNo' => 'unable_to_write_request', 'error' => 'Unable to write request');
	}
	
	$temp = '';
	if($fget){		
		 while (!feof($fp)) {
			$temp .= fgets($fp, 128);
		}
	}
	
	fclose($fp);
	
	if($fget){
		if(strpos($temp, 'WWW-Authenticate:') !== false){
			return array('status' => false, 'errorNo' => 'authentication_required', 'error' => 'Your IWP Admin Panel has folder protection.<br><a onclick="$(\'#settings_btn\').click();$(\'#authUsername\').focus();">Set the credentials</a> in settings -> Folder protection.');
		}
		else{			
			return fsockSameURLConnectCheck($url, false);
		}
	}
	else{
		sleep(7);//due to fsock non-blocking mode we have to wait
		if(!empty($fsockSameURLCheckUsingDBValue) && $fsockSameURLCheckUsingDBValue == getOption('fsockSameURLCheckUsingDBValue')){
			return array('status' => true);
		}
		else{
			return array('status' => false, 'errorNo' => 'unable_to_verify', 'error' => 'Unable to verify content(method using DB)');
		}
	}
   
}

function filterParameters($array, $DBEscapeString=true){
  
    if(is_array($array)){
        foreach($array as $key => $value){
            $array[$key] = filterParameters($array[$key]);
        }
    }elseif(is_string($array)){
        if(get_magic_quotes_gpc()){
            $array = stripslashes($array);
        }
        if($DBEscapeString){
            $array = DB::realEscapeString($array);
        }
    }
    return $array;
    
}

function IPInRange($IP, $range) {

	if (strpos($range, '*') !==false) { // a.b.*.* format
	  // Just convert to A-B format by setting * to 0 for A and 255 for B
	  $lower = str_replace('*', '0', $range);
	  $upper = str_replace('*', '255', $range);
	  $range = "$lower-$upper";
	}
	
	if (strpos($range, '-')!==false) { // A-B format
	  list($lower, $upper) = explode('-', $range, 2);
	  $lowerDec = (float)sprintf("%u", ip2long($lower));
	  $upperDec = (float)sprintf("%u", ip2long($upper));
	  $IPDec = (float)sprintf("%u", ip2long($IP));
	  return ( ($IPDec>=$lowerDec) && ($IPDec<=$upperDec) );
	}
	if($IP == $range) return true;
	return false;
}

function ksortTree( &$array, $sortMaxLevel=-1, $currentLevel=0 )
{
  if((int)$sortMaxLevel > -1 && $sortMaxLevel <= $currentLevel){ return false;}
  
  if (!is_array($array)) {
    return false;
  }
 
  ksort($array);
  foreach ($array as $k=>$v) {
	$currentLevel++;
    ksortTree($array[$k], $sortMaxLevel, $currentLevel);
  }
  return true;
}

function trimValue(&$v){
	$v = trim($v);
}

function arrayMergeRecursiveNumericKeyHackFix(&$array){
	if(!is_array($array)){ return; }

	foreach($array as $key => $value){
		$finalKey = $key;
		$numKey = preg_replace("/[^0-9]/", '', $key);
		if($key == '_'.$numKey){
			unset($array[$key]);
			$array[$numKey] = $value;
			$finalKey = $numKey;
		}
		arrayMergeRecursiveNumericKeyHackFix($array[$finalKey]);
	}
	return;

}

function appErrorHandler($errno, $errstr,  $errfile, $errline, $errcontext )
{
   	if(!isset($GLOBALS['appErrorHandlerErrors'])){
		$GLOBALS['appErrorHandlerErrors'] = '';	
	}
    $GLOBALS['appErrorHandlerErrors'] .= @date('Y-m-d H:i:s')." ERR: errno:".$errno." (".$errstr.") file:".$errfile.", line:".$errline.".\r\n";
	return false;
}

function appErrorHandlerWriteFile(){

	if(!empty($GLOBALS['appErrorHandlerErrors'])){
		@file_put_contents(APP_ROOT.'/appErrorLogs.txt', $GLOBALS['appErrorHandlerErrors'], FILE_APPEND);
		unset($GLOBALS['appErrorHandlerErrors']);
	}
}

set_error_handler('appErrorHandler', E_ERROR|E_WARNING|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_COMPILE_WARNING);
@register_shutdown_function('appErrorHandlerWriteFile');

if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = jsonEncoder($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = jsonEncoder($k).':'.jsonEncoder($v);
      return '{' . join(',', $result) . '}';
    }
  }
}


function downloadURL($URL, $filePath){
	
	return (curlDownloadURL($URL, $filePath) || fopenDownloadURL($URL, $filePath));

}

function curlDownloadURL($URL, $filePath){
	
	//$options = array('file' => 'download', 'filePath' => $filePath);
	//$callResponse = doCall($URL, '', 60, $options);
	
	$fp = fopen ($filePath, 'w');
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 180);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	
	if (!ini_get('safe_mode') && !ini_get('open_basedir')){
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	}	
	$callResponse = curl_exec($ch);	
	curl_close($ch);
	fclose($fp);

	if($callResponse == 1){
		return true;
	}
	return false;
	
}

function fopenDownloadURL($URL, $filePath){
	
	 if (function_exists('ini_get') && ini_get('allow_url_fopen') == 1) {
		 $src = @fopen($URL, "r");
		 $dest = @fopen($filePath, 'wb');
		 if($src && $dest){
			 while ($content = @fread($src, 1024 * 1024)) {
				@fwrite($dest, $content);
			 }
    
			@fclose($src);
			@fclose($dest);
			return true;
		 }		
	 }
	 return false;
}


function protocolRedirect(){

	if(APP_HTTPS == 1 && ($_SERVER['HTTPS'] != 'on' && $_SERVER['SERVER_PORT']!='443')){
		header('Location: '.APP_URL);	
	}
	elseif(APP_HTTPS == 0 && ($_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT']=='443')){
		header('Location: '.APP_URL);
	}
}

function checkOpenSSL(){
	if(!function_exists('openssl_verify')){
		return false;
	}
	else{
		$key = @openssl_pkey_new();
		@openssl_pkey_export($key, $privateKey);
		$privateKey	= base64_encode($privateKey);
		$publicKey = @openssl_pkey_get_details($key);
		$publicKey 	= $publicKey["key"];
		
		if(empty($publicKey) || empty($privateKey)){
			return false;
		}
	}
	return true;
}

function httpBuildURLCustom($parts){
	
	if(is_array($parts['query'])){
		$parts['query'] = http_build_query($parts['query'], NULL, '&');
	}
	$URL = $parts['scheme'].'://'
		.($parts['user'] ? $parts['user'].':'.$parts['pass'].'@' : '')
		.$parts['host']
		.((!empty($parts['port']) && $parts['port'] != 80) ? ':'.$parts['port'] : '')
		.($parts['path'] ? $parts['path'] : '')
		.($parts['query'] ? '?'.$parts['query'] : '')
		.($parts['fragment'] ? '#'.$parts['fragment'] : '');
	return $URL;
}

function sendMail($from, $fromName, $to, $toName, $subject, $message, $options=array()){
	
	require_once(APP_ROOT.'/lib/phpmailer.php');
	require_once(APP_ROOT.'/lib/class.smtp.php'); //smtp mail
	
	$mail = new PHPMailer(); // defaults to using php "mail()"
	
	if(!empty($options['emailSettings'])){
		$emailSettings = $options['emailSettings'];
	}
	if(!empty($emailSettings['smtpSettings'])){
		$smtpSettings = $emailSettings['smtpSettings'];
	}
	//if(true){
	
	if(!empty($smtpSettings) && !empty($smtpSettings['useSmtp'])){
		$mail->IsSMTP();
		$mail->Host       = $smtpSettings['smtpHost']; // sets the SMTP server
		$mail->Port       = $smtpSettings['smtpPort'];
		if($smtpSettings['smtpAuth'] == 1 && !empty($smtpSettings['smtpAuthUsername']) && !empty($smtpSettings['smtpAuthPassword'])){
			$mail->SMTPAuth  = true; //enable SMTP authentication
			$mail->Username  = $smtpSettings['smtpAuthUsername']; // SMTP account username
			$mail->Password  = $smtpSettings['smtpAuthPassword']; 
		}
		
		if(!empty($options['CharSet'])){
			$mail->CharSet = 'utf-8';			
		}
		$mail->SMTPSecure = $smtpSettings['smtpEncryption'];
		$mail->From = $from;
		$mail->FromName = $fromName;
		$mail->AddAddress($to);
		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->MsgHTML($message);
		if(!empty($options['attachment'])){
			$mail->AddAttachment($options['attachment']);
		}
		//$mail->Debugoutput = function($str, $level) { /* place any code for debugging here. */ };
	}
	else{
		$body = $message;
		$mail->SetFrom($from, $fromName);
		$mail->AddAddress($to, $toName);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		if(!empty($options['attachment'])){
			$mail->AddAttachment($options['attachment']);
		}
	
	}
		
	if(!$mail->Send()) {
	  addNotification($type='E', $title='Mail Error', $message=$mail->ErrorInfo, $state='U');	  
	  return false;
	} else {
	  //echo "Message sent!";
	  return true;
	}
}

if ( !function_exists('mb_detect_encoding') ) { 
	function mb_detect_encoding ($string, $enc=null, $ret=null) { 

		static $enclist = array( 
		'UTF-8',
		// 'ASCII', 
		// 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 
		// 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 
		// 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16', 
		// 'Windows-1251', 'Windows-1252', 'Windows-1254', 
		);

		$result = false; 

		foreach ($enclist as $item) { 
			$sample = $string;
			if(function_exists('iconv'))
				$sample = iconv($item, $item, $string); 
			if (md5($sample) == md5($string)) { 
				if ($ret === NULL) { $result = $item; } else { $result = true; } 
				break; 
			}
		}

		return $result; 
	}
}

function convertToMinSec($time) {
    $min = intval(($time / 60) % 60);
    $minSec = $min.' minutes';
    if($min==0) {
        $sec = intval($time % 60);
        $sec = str_pad($sec, 2, "0", STR_PAD_LEFT);
        $minSec = $sec.' seconds';
    }
    return $minSec;
}

function autoPrintToKeepAlive($uniqueTask){
	$printEveryXSecs = 5;
	$currentTime = microtime(1);

	if(!$GLOBALS['IWP_PROFILING']['TASKS'][$uniqueTask]['START']){
		$GLOBALS['IWP_PROFILING']['TASKS'][$uniqueTask]['START'] = $currentTime;	
	}
	
	if(!$GLOBALS['IWP_PROFILING']['LAST_PRINT'] || ($currentTime - $GLOBALS['IWP_PROFILING']['LAST_PRINT']) > $printEveryXSecs){
		$printString = $uniqueTask." TT:".($currentTime - $GLOBALS['IWP_PROFILING']['TASKS'][$uniqueTask]['START']);
		printFlush($printString);
		$GLOBALS['IWP_PROFILING']['LAST_PRINT'] = $currentTime;		
	}
}

function printFlush($printString){
	echo $printString;
	ob_flush();
	flush();
}

function jsonEncoder( $data, $options = 0, $depth = 512 ) {
	if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
		$args = array( $data, $options, $depth );
	} elseif ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
		$args = array( $data, $options );
	} else {
		$args = array( $data );
	}
	$json = @call_user_func_array( 'json_encode', $args );
	
	if ( false !== $json && ( version_compare( PHP_VERSION, '5.5', '>=' ) || false === strpos( $json, 'null' ) ) )  {
		return $json;
	}

	$args[0] = jsonCompatibleCheck( $data, $depth );
	return @call_user_func_array( 'json_encode', $args );
}

function jsonCompatibleCheck( $data, $depth ) {
	if ( $depth < 0 ) {
		return false;
	}

	if ( is_array( $data ) ) {
		$output = array();
		foreach ( $data as $key => $value ) {
			if ( is_string( $key ) ) {
				$id = jsonConvertString( $key );
			} else {
				$id = $key;
			}
			if ( is_array( $value ) || is_object( $value ) ) {
				$output[ $id ] = jsonCompatibleCheck( $value, $depth - 1 );
			} elseif ( is_string( $value ) ) {
				$output[ $id ] = jsonConvertString( $value );
			} else {
				$output[ $id ] = $value;
			}
		}
	} elseif ( is_object( $data ) ) {
		$output = new stdClass;
		foreach ( $data as $key => $value ) {
			if ( is_string( $key ) ) {
				$id = jsonConvertString( $key );
			} else {
				$id = $key;
			}

			if ( is_array( $value ) || is_object( $value ) ) {
				$output->$id = jsonCompatibleCheck( $value, $depth - 1 );
			} elseif ( is_string( $value ) ) {
				$output->$id = jsonConvertString( $value );
			} else {
				$output->$id = $value;
			}
		}
	} elseif ( is_string( $data ) ) {
		return jsonConvertString( $data );
	} else {
		return $data;
	}

	return $output;
}

function jsonConvertString( $string ) {
	if ( function_exists( 'mb_convert_encoding' ) ) {
		$encoding = mb_detect_encoding( $string, mb_detect_order(), true );
		if ( $encoding ) {
			return mb_convert_encoding( $string, 'UTF-8', $encoding );
		} else {
			return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
		}
	} else {
		return checkInvalidUTF8( $string, $true);
	}
}

function checkInvalidUTF8( $string, $strip = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Check for support for utf8 in the installed PCRE library once and store the result in a static
	static $utf8_pcre = null;
	if ( ! isset( $utf8_pcre ) ) {
		$utf8_pcre = @preg_match( '/^./u', 'a' );
	}
	// We can't demand utf8 in the PCRE installation, so just return the string in those cases
	if ( !$utf8_pcre ) {
		return $string;
	}

	// preg_match fails when it encounters invalid UTF8 in $string
	if ( 1 === @preg_match( '/^./us', $string ) ) {
		return $string;
	}

	// Attempt to strip the bad chars if requested (not recommended)
	if ( $strip && function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8', $string );
	}

	return '';
}

function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Edge/i',$u_agent)) 
    { 
        $bname = 'Edge'; 
        $ub = "Edge"; 
    } 
    elseif(preg_match('/OPR/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "OPR"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    }else{
    	$bname = 'Unknown'; 
    	$ub = "Unknown";
    }
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}
function doSetCharsetToUTF8() {
	$query = "
		SET 
			character_set_results = 'utf8', 
			character_set_client = 'utf8', 
			character_set_connection = 'utf8', 
			character_set_database = 'utf8', 
			character_set_server = 'utf8'
	";
	$result = DB::doQuery($query);		
}

function isUTF8Collation($tableName) {
	$return = showTableLike($tableName);
	if(preg_match('/^utf8/', $return['Collation'])) {
		return true;
	}
	return false;
}

function showTableLike($tableName) {
	$result = DB::getRow("show table status like '".$tableName."'");
	return $result;
}

if(!function_exists('unserializeBase64DecodeArray')){
	function unserializeBase64DecodeArray($strBetArray){
		if(empty($strBetArray) || !is_array($strBetArray)){ return false; }
		$newArray = array();
		foreach($strBetArray as $key => $value){
			$newArray[$key] = unserialize(base64_decode($value));
		}
		return $newArray;
	}

}

if(!function_exists('addProtocolCommon')){
	function addProtocolCommon($URL) {
		$URL = trim($URL);
		return (substr($URL, 0, 7) == 'http://' || substr($URL, 0, 8) == 'https://')
			? $URL
			: 'http://'.$URL;
	}
}

if(!function_exists('getURLPartsCommon')){
	function getURLPartsCommon($URL) {
		$URL = addProtocolCommon($URL);
		return parse_url($URL);
	}
}

if(!function_exists('autoFillInstallCloneCommonCpanel')){
	function autoFillInstallCloneCommonCpanel($params){
		include_once APP_ROOT."/lib/xmlapi.php";
		global $xmlapi, $ID, $DBNames,$DBUserNames,$rootDir,$dbName,$dbUser,$cpUser,$mainDomain,$subDomainFlg,$newCronKey,$lastError;
		$params['cpHost'] = $params['cpHost'];
		$rootDir = 'public_html/';
		$prefix = "clone_";
		$cpUser = trim($params['cpUser']);
		$cpPass = $params['cpPass'];
		$siteInfo = getURLPartsCommon($params['cpHost']);
		$host =  str_replace(array('http://','https://', 'www.','/cpanel/','/cpanel'), '', trim($siteInfo['host'], '/'));
		$hostName = trim($host);

		if(!defined('USERNAME')) { define('USERNAME', $cpUser); }

		$xmlapi = new xmlapi($hostName);
		$xmlapi->set_port( 2083 );
		$xmlapi->password_auth(USERNAME, $cpPass);
		$xmlapi->set_debug(1);

		$primaryHosts = apiGenCommon("DomainLookup", "getmaindomain"); //DomainLookup::getmaindomain
		$primaryHost = (array)$primaryHosts->data;
		$mainDomain = $primaryHost['main_domain'];
		$host =  str_replace(array('http://','https://', 'www.'), '', trim($mainDomain, '/'));
		$mainDomain = trim($host);

		$appDomainPath = $mainDomain;
		$destURL = "http://".$mainDomain.'/';
		$path = '/'.$rootDir;

		$stats = apiGenCommon("StatsBar", "stat", array('display' => 'sqldatabases'));
		$sqlInfo = (array)$stats->data;
		if($sqlInfo['_max'] == 'unlimited' ||  ($sqlInfo['_max'] - $sqlInfo['_count'] >= 1)){
			for($i=0; $i<=3; $i++){
				//create database
				if(!$DBNames){
					$dbName = $prefix.substr( md5(rand()), 0, 5).$i;
					$addDB = $xmlapi->api1_query(USERNAME, "Mysql", "adddb", array($dbName));
					$addDBArray = (array)$addDB;
					if($addDB->error) {
						$DBNames = false;
						$dbName = '';
						$lastError = $addDBArray['error'];
						if(strpos($addDBArray['error'], 'database name already exists') !== false){ } else {continue;}
					} else {
						$DBNames = true;
						break;
					}
				}
			}
			if($DBNames) {
				for($i=0; $i<=3; $i++){
					//create user
					if(!$DBUserNames){
						$dbUser = substr( md5(rand()), 0, 3).$i;
						$dbPass = substr( md5(rand()), 0, 7);

						//$addUser = apiGenCommon("Mysql", "adduser", array('username' => $dbUser, 'password' => $dbPass));
						$addUser = $xmlapi->api1_query(USERNAME, "Mysql", "adduser", array($dbUser, $dbPass));
						$addUserArray = (array)$addUser;
						if($addUser->error) {
							$DBUserNames = false;//exists in the database
							$dbUser = '';
							$lastError = $addUserArray['error'];
							if(strpos($addUserArray['error'], 'exists in the database') !== false){} else {continue;}
						} else {
							$DBUserNames = true;
							break;
						}
					}
				}
			}
		} else{
			echo json_encode(array("error" => 'cPanel error: MySQL database limit reached'));
			exit;
		}
		if(empty($DBNames)) {
			echo json_encode(array("error" => 'cPanel error: Failed to create the database('.$lastError.')'));
			exit;
		}
		if(empty($DBUserNames)) {
			echo json_encode(array("error" => 'cPanel error: Failed to create the database user('.$lastError.')'));
			exit;
		}
		//add user to database
		$linkUserDB = $xmlapi->api1_query(USERNAME, "Mysql", "adduserdb", array($dbName, $dbUser, 'all'));
		if($linkUserDB->error) {
			echo json_encode(array("error" => 'cPanel error: Failed to link DB and User'));
			exit;
		}
		$URLParts = parse_URL($params['cpHost']);
		if (!isset($URLParts['host'])) {
			$cpHost = $URLParts['path'];
		} else {
			$cpHost = $URLParts['host'];
		}
		$exportArray = array();
		$exportArray['cpUser'] = $params['cpUser'];
		$exportArray['cpPass'] = $params['cpPass'];
		$exportArray['cpHost'] = $cpHost;
		$exportArray['destURL'] = $destURL;
		$exportArray['path'] = $path;
		$exportArray['dbName'] = USERNAME.'_'.$dbName;
		$exportArray['dbUser'] = USERNAME.'_'.$dbUser;
		$exportArray['dbPass'] = $dbPass;
		return $exportArray;
	}
}


if(!function_exists('apiGenCommon')){
	function apiGenCommon($mod, $func, $var = array()){
	    global $xmlapi;

	    try{
	        $apiQuery = $xmlapi->api2_query(USERNAME, $mod, $func, $var);
	        if($apiQuery->error){
	            if($func == 'addsubdomain' || $func == 'search' || $func == 'mkdir'){
	                    return false;
	            }
	            $errorMsg = array('mod' => $mod, 'func' => $func);
	            $apiArray = (array)$apiQuery->error;
	            echo json_encode(array('error' => 'cPanel error: '.$apiQuery->error));
	            exit;
	        } else {
	            if($func == 'listfiles' || $func == 'listdbs' || $func == 'stat'|| $func == 'getmaindomain' || $func == 'search'){ //"DomainLookup", "getmaindomain"
	                return $apiQuery;
	            }
	        }
	    }
	    catch(Exception $e){
	        echo json_encode(array("error" => 'cPanel error: '.$e->getMessage()));
	        exit;
	    }
	    return true;
	}
}

function fileSystemRemoveFiles($filesData){
	if(!is_object($GLOBALS['FileSystemObj'])){
		if(!initFileSystem()){
			appUpdateMsg('Unable to initiate file system.', true);
			return false;
		}
	}

	if (!isset($GLOBALS['updatePanelLocation'])) {
		$GLOBALS['updatePanelLocation'] = $GLOBALS['FileSystemObj']->findFolder(APP_ROOT);
		$GLOBALS['updatePanelLocation'] = removeTrailingSlash($GLOBALS['updatePanelLocation']);
	}
	if ( empty($filesData) || !is_array($filesData)) {
		return false;
	}
	foreach ($filesData as $file => $extra) {
		if($extra['type'] == 'f'){
			$GLOBALS['FileSystemObj']->delete($GLOBALS['updatePanelLocation'].$file, false, 'f');
		} else if($extra['type'] == 'd'){
			$GLOBALS['FileSystemObj']->delete($GLOBALS['updatePanelLocation'].$file, true);
		}
	}
}

if(!function_exists('gzdecode') &&  function_exists('gzinflate')){
   function gzdecode($data) { 
       return @gzinflate(substr($data,10,-8)); 
   } 
}

function checkDBStatus(){
	if(!isDBStatusCheckedToday()){
	//Checking the main three tables 
	$needCheckTables = array('?:history','?:history_raw_details','?:history_additional_data');
	foreach($needCheckTables as $checkingTable){
		$individualResults = DB::getArray('CHECK TABLE '.$checkingTable);
		if(!empty($individualResults) && is_array($individualResults)){
			foreach ($individualResults as $results){
				if(isset($results['Msg_text']) && $results['Msg_text']!='OK' ){
					die('One or more database tables connected to your IWP installation have crashed or been removed. Please repair the crashed tables or get in touch with your hosting provider to get the crashed tables fixed. For missing tables, please get in touch with IWP Support');
				}
			}
		}
	}
	updateOption('isDBStatusCheckedToday', date('Y-m-d'));
	}
}

function isDBStatusCheckedToday(){
	@date_default_timezone_set('GMT'); //gmdate(time());
	$today = date('Y-m-d');
	$lastCheckedDate = getOption('isDBStatusCheckedToday');
	$isCheckedToday  = ($today == $lastCheckedDate)?true:false ;
	return $isCheckedToday;
}
