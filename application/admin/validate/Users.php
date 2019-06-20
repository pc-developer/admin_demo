<?php

namespace app\admin\validate;

use think\Validate;

class Users extends Validate
{
    protected $rule = [
        'password'      => 'require|length:6,20|confirm',
        'mobile'        => ['regex'=>'/^1[34578]\d{9}$/'],
        'email'         => 'require|email',
    ];
    
    protected $message = [
        'password.require'  => "密码必填！",
        'password.length'   => "密码长度6-16！",
        'password.confirm'  => "密码不一致！",
        'mobile.regex'      => "手机号码格式不正确！",
        'email.require'     => "邮箱不能为空！",
        'email.email'       => "邮箱格式不正确！",
    ];

    protected $scene = [
        'edit'  => [
            'password'  => 'length:6,20|confirm',
            'mobile',
            'email',
        ],
        'pwd'   => [
            'password',
        ]
    ];
}