<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-IWP-cron-API
 *
 * @author sridharrajs
 */

class IWP_Cron_API {

    public $apiURL = 'http://app.iwpcron.com/v1/users';
    public $cronURL;

    function __construct() {
        $this->cronURL = APP_FULL_URL.'cron.php';
    }

    public function register($userName) {
        $fields = array(
            'username' => $userName,
            'iwpURL' => $this->cronURL,
            'appInstallHash' => APP_INSTALL_HASH,
            'installedHash'=>getInstalledHash()
        );
        $methodURL = $this->apiURL;
        $responseCode = $this->doCall($methodURL, $fields);
        return $responseCode;
    }
    
    public function ping(){
        $fields = array('iwpURL' => $this->cronURL);
        $methodURL = $this->apiURL . '/ping';
        $responseCode = $this->doCall($methodURL, $fields);
        return $responseCode;
    }

    public function requestPulseCron() {
        $response = array("pingBack" => true,
            "url" => $this->cronURL
        );
        $this->sendResponse($response);
    }

    public function offPulseCron() {
        $response = array(
            "pingBack" => false,
            "url" => $this->cronURL
        );
        $this->sendResponse($response);
    }
    
    public function update($status){
        $fields = array(
            'status'=>$status
        );
        $methodURL = $this->apiURL . '/status';
        $responseCode = $this->doCall($methodURL, $fields);
        return $responseCode;
    }

    private function doCall($methodURL, $fields) {
        $ch = curl_init($methodURL);
        curl_setopt($ch, CURLOPT_URL, $methodURL);
        $this->buildHeaders($ch);
        curl_setopt($ch, CURLOPT_POST, 1);
        $payload = json_encode($fields);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $httpcode;        
    }
    
    private function buildHeaders(&$ch){
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-App-Install-Hash:'. APP_INSTALL_HASH,
            'X-Installed-Hash:'.getInstalledHash()            
        ));            
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0');
    }

    private function sendResponse($nonJSONEncodedReponse) {
        header('Content-Type: application/json');
        echo json_encode($nonJSONEncodedReponse);
    }

}
