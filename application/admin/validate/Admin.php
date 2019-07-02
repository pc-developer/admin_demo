<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'name'          => 'require|alphaDash|length:4,16|unique:admin',
        'password'      => 'require|length:6,20|confirm',
        'mobile'        => ['regex'=>'/^1[34578]\d{9}$/'],
        'email'         => 'require|email',
        'note'          => 'max:100',
    ];
    
    protected $message = [
        'name.require'      => "管理员必填！",
        'name.alphaDash'    => "用户名只能是字母和数字！",
        'name.length'       => "用户名长度4-16！",
        'name.unique'       => "已存在该用户名！",
        'password.require'  => "密码必填！",
        'password.length'   => "密码长度6-20！",
        'password.confirm'  => "密码不一致！",
        'mobile.regex'      => "手机号码格式不正确！",
        'email.require'     => "邮箱不能为空！",
        'email.email'       => "邮箱格式不正确！",
        'note.max'          => "备注不能大于100字！",
    ];

    protected $scene = [
        'edit'      => [
            'name'      => 'require|alphaDash|length:4,16|unique:admin,name^id',
            'password'  => 'length:6,20|confirm',
            'mobile',
            'email',
            'note',
        ],
        'password'  => [
            'password'  => 'require|length:6,20|confirm',
        ],
    ];
}