<?php

namespace app\admin\controller;

use think\Db;
use think\Loader;

class Index extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }
    
    public function index()
    {
        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }

    public function user()
    {
        $admin_id = $this->id;
        if ($admin_id <= 0) {
            return return_msg("暂无信息");
        }
        $admin = Db::name('admin')->find($admin_id);
        $group = Db::name('admin_group')->column('id,name');
        
        $this->assign('group',$group);
        $this->assign('admin_info',$admin);

        return $this->fetch();
    }

    public function change_password()
    {
        return $this->fetch();
    }

    # 修改密码
    public function password()
    {
        $admin_id = $this->id;
        $data['password'] = trim(input('password'));
        $data['password_confirm'] = trim(input('password2'));
        $old_password = trim(input('old_password'));
        $result = array('status'=>0,'msg'=>"参数错误！");

        $old_password = get_encrypt($old_password);
        $admin_password = Db::name('admin')->where('id',$admin_id)->value('password');

        if ($old_password == $admin_password) {
            $admin_validate = Loader::Validate('Admin');
            
            if (!$admin_validate->scene('password')->check($data)) {
                $result['msg'] = $admin_validate->getError();
            } else {
                $password = get_encrypt($data['password']);
                $bool = Db::name('admin')->where('id',$admin_id)->update(['password'=>$password]);
                if ($bool) {
                    $result['status'] = 1;
                    $result['msg'] = "成功修改密码！";
                } else {
                    $result['msg'] = "修改密码失败！";
                }
            }
        } else {
            $result['msg'] = "原密码不正确！";
        }

        return json($result);
    }
}