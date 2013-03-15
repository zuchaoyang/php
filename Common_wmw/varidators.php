<?php

/** 
 * 判断是否是邮箱
 * @param $account
 */
function isEmail($email) {
    if(empty($email) || stripos($email, '@') === false) {
        return false;
    }
    return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i", $email) ? true : false;
}


/** 
 * 判断是否是手机号
 * @param $phone
 */
function isPhonenum($phone) {
    if(empty($phone) || strlen(strval($phone)) != 11) {
        return false;
    }
    
    return preg_match("/^1[0-9]{10}$/", $phone) ? true : false;
}

/** 
 * 判断是否是账号
 * @param $uid
 */
function isUid($uid) {
    if(empty($uid)) {
        return false;
    }
    
    return preg_match("/^[1-9](\d)+$/", $uid) ? true : false;
} 

/**
 * 判断是否长度限制范围
 */
function isLengthLimit($str, $minlen, $maxlen) {
    
    //如果参数为空则返回失败
    if (empty($str) || empty($minlen) || empty($maxlen)) {
        return false;
    }
    
    //长度范围不是数字，返回失败
    if (!is_int($minlen) || !is_int($maxlen)) {
        return false;
    }
    
    //最小长度大于最大长度，返回失败
    if ($minlen > $maxlen) {
        return false;
    }
    
    if( $maxlen<strlen($str) || $minlen> strlen($str)) {
        return false;
    }
    return true;
}


