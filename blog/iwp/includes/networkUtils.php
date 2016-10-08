<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

 class cURLErrors{
	
	private $cURLDetails = array();

	function __construct($cURLInfo){
		$this->parseCURLInfo($cURLInfo);		
	}
	private function parseCURLInfo($cURLInfo){
		if($cURLInfo['info']['http_code'] != 200 || !empty($cURLInfo['errorNo'])){
			$this->cURLDetails['status'] = 'netError';
			
			if($cURLInfo['info']['http_code'] != 0 && $cURLInfo['info']['http_code'] != 200){
				$this->cURLDetails['error'] = $cURLInfo['info']['http_code'];
				$this->cURLDetails['errorMsg'] = 'HTTP Error '.$cURLInfo['info']['http_code'].': '.$GLOBALS['httpErrorCodes'][ $cURLInfo['info']['http_code'] ].'.';
			}
			elseif($cURLInfo['errorNo']){
				$this->cURLDetails['error'] = $cURLInfo['errorNo'];
				$this->cURLDetails['errorMsg'] = 'cURL Error(' . $cURLInfo['errorNo'] . '): ' . htmlspecialchars($cURLInfo['error']) . '.';
				
				if($cURLInfo['errorNo'] == 6 || $cURLInfo['errorNo'] == 7){
					$this->cURLDetails['errorMsg'] .= "<br>Please contact your host. They should be able to help you out with this.";
				}
	 		}
		}
	}
	public function isOk(){
		if($this->cURLDetails['status'] === 'netError') {
			return false;
		}
		return true;
	}
	public function getStatus(){
		return $this->cURLDetails['status'];
	}
	public function getError(){
		return $this->cURLDetails['error'];		
	}
	public function getErrorMsg(){
		return $this->cURLDetails['errorMsg'];
	}
	public function getErrorDetails(){
		return $this->cURLDetails;
	}
}