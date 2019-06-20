<?php

namespace app\common\model;

use think\Model;

class Users extends Model
{
    public function getSexAttr($value)
    {
        $sex = [0=>"保密",1=>"男",2=>"女"];
        return $sex[$value];
    }

    public function getAvatarAttr($value)
    {
        $avatar = $value ?: "__ADMIN__/static/h-ui/images/ucnter/avatar-default.jpg";
        return $avatar;
    }

    public function getRegisterTimeAttr($value)
    {
        $time = $value ? date('Y-m-d H:i:s',$value) : '';
        return $time;
    }

    public function getLoginTimeAttr($value)
    {
        $time = $value ? date('Y-m-d H:i:s',$value) : '';
        return $time;
    }

    public function getLastLoginTimeAttr($value)
    {
        $time = $value ? date('Y-m-d H:i:s',$value) : '';
        return $time;
    }
}