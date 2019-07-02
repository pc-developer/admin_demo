<?php

namespace app\admin\controller;

use think\Db;

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
}