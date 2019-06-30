<?php

namespace app\admin\validate;

use think\Validate;

class System extends Validate
{
    protected $rule = [
        'web_name'      => 'require',
        'copyright'     => 'require',
        'web_title'     => 'require',
        'web_key_words' => 'require',
        'web_desc'      => 'require',
        'linkman'       => 'require',
        'phone'         => 'require',
        'mobile'        => 'mobile',
        'service_tel'   => 'require',
    ];
    protected $message = [
        'web_name.require'      =>  "请输入网站名称！",
        'copyright.require'     =>  "请输入底部版权信息！",
        'web_title.require'     =>  "请输入网站标题！",
        'web_key_words.require' =>  "请输入关键字！",
        'web_desc.require'      =>  "请输入描述！",
        'linkman.require'       =>  "请输入联系人！",
        'phone.require'         =>  "请输入联系电话！",
        'mobile.mobile'         =>  "联系手机格式不正确！",
        'service_tel.require'   =>  "请输入客服电话！",
    ];
    //预定义正则表达式
    protected $regex = [
        'mobile' => '/^1[34578]\d{9}$/',    //手机正则
    ];
    protected $scene = [
        'web_info' => ['web_name','copyright','web_title','web_key_words','web_desc','linkman','phone','mobile'],
    ];
}