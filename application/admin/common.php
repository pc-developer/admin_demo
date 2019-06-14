<?php

/**
 * 后台公共文件
 */

 //加密
 function get_encrypt($str){
    if (empty($str)) {
       return $str;
    }
    
    $new_str = md5($str.md5($str));
    return $new_str;
 }