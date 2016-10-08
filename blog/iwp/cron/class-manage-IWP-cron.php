<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IWP_Cron
 *
 * @author sridharrajs
 */

require_once(APP_ROOT . '/lib/class-IWP-cron-API.php');
require_once(APP_ROOT . '/includes/utils/time-utils.php');

class Manage_IWP_Cron {

    private static $cronAPI;
    private static $OPTION_NAME = 'IWP_Cron';
    
    public static function hideCronInviteNotification(){
        updateOption('iwpCrontInvitedNotificationReq', false);
    }
    
    public static function isActive(){
        $iwpCron = getOption(self::$OPTION_NAME);
        return !empty($iwpCron);
    }
    
    public static function test(){
        self::loadAPIDetails();
        $responseCode = self::$cronAPI->ping();
        $action = 'test';
        self::informUser($responseCode, $action);
    }
    
    private static function isSuccess($responseCode){
        return $responseCode === 200 || $responseCode === 201;
    }

    public static function handleCronReq($mode) {
        switch ($mode) {
            case 'handshake':
                self::evaluateHandShake();
                break;
            case 'pulse':
                self::evaluatePulse();
                break;
            case 'test':
                $response = array(
                  "status" => 'OK'
                ); 
                self::sendResponse($response);
                break;
            default:
                header("HTTP/1.0 400");
                break;
        }
    }
    
    private static function evaluateHandShake(){
        $nextScheduledTime = getNextTaskScheduleTime();
        $minutes = 30;
        if (timeUtils::isLessThanNowInMinutes($minutes, $nextScheduledTime)) {
            self::requestPulseCron();
        } else {
            self::offPulseCron();
        }
    }
    
    private static function evaluatePulse(){
        define('CRON_MODE', 'IWP_Cron');
        define('CRON_TIMEOUT', 30);
    }

    private static function sendResponse($nonJSONEncodedResponse){
        header('Content-Type: application/json');
        echo json_encode($nonJSONEncodedResponse);        
    }
    
    private static function informUser($responseCode, $action){
        $title = '';
        $message = 'Something happened!';
        $type = 'E';
        switch($action){
            case 'test':
                $title = 'Cron testing';
                $message= 'Hey! We werent able talk to your server!';
                if(self::isSuccess($responseCode)){
                    $type = 'N';
                    $message= 'Hey! All Good!';
                }
                break;
            case 'register':
                $title = 'Activating cron';
                $message= 'Hey! We couldnt Activate cron for you, try again later';
                if(self::isSuccess($responseCode)){
                    $type = 'N';
                    $message= 'Hey! Activated!';
                }
                break;
            case 'deactivate':
                $title = 'Deactivating cron';
                $message= 'Hey! We couldnt deactivate cron for you, try again later';
                if(self::isSuccess($responseCode)){
                    $type = 'N';
                    $message= 'Your cron was deactivated successfully!';
                }
                break;
        }   
        addNotification($type, $title, $message);
    }
    
    private static function updateOptions($responseCode, $action){
        if(!self::isSuccess($responseCode)){
            return;
        }
        switch($action){
            case 'register':
                addOption(self::$OPTION_NAME, 'activated');
                break;
            case 'deactivate':
                deleteOption(self::$OPTION_NAME);
                break;
        }
    }

    public static function register() {
        self::loadAPIDetails();
        $userName = self::getOption('appRegisteredUser');
        $responseCode = self::$cronAPI->register($userName);
        $action = 'register';
        self::informUser($responseCode, $action);
        self::updateOptions($responseCode, $action);
    }
    
    public static function update(){
        $status = 'N';
        self::loadAPIDetails();
        $responseCode = self::$cronAPI->update($status);
        $action = 'deactivate';
        self::informUser($responseCode, $action);
        self::updateOptions($responseCode, $action);
    }

    public static function requestPulseCron() {
        self::loadAPIDetails();
        self::$cronAPI->requestPulseCron();
    }

    public static function offPulseCron() {
        self::loadAPIDetails();
        self::$cronAPI->offPulseCron();
    }

    public static function getOption($optionName) {
        $where = array(
                    'query' =>  "optionName = ':optionName'",
                    'params' => array(
                       ':optionName'=>$optionName
                    )
                );
        return DB::getField("?:options", "optionValue", $where);
    }

    private static function loadAPIDetails() {
        self::$cronAPI = new IWP_Cron_API();
    }

}

panelRequestManager::addFunctions('Manage_IWP_Cron::isActive','Manage_IWP_Cron::test','Manage_IWP_Cron::register','Manage_IWP_Cron::update','Manage_IWP_Cron::hideCronInviteNotification');