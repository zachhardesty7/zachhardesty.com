<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of time utilities
 *
 * @author sridharrajs
 */
class timeUtils {

    
    public static function isLessThanNowInMinutes($minutes, $time) {
        if (empty($minutes) || empty($time)) {
            return false;
        }
        $computedTime = time() + $minutes * 60;
        return $time < $computedTime;
    }

}
