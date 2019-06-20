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

 //返回信息
 function return_msg($msg){
    return '<p style="text-align: center; margin-top: 3em; color: red; font-size: 2.5rem;">'.$msg.'</p>';
 }