<?php

namespace app\admin\controller;

use think\Db;
use think\Loader;
use app\admin\model\Admin as AdminModel;

/**
 * 管理员
 */
class Admin extends Base
{
    # 管理员列表
    public function index()
    {
        $name = input('admin_name/s');
        $where = array();

        if ($name) {
            $where['name'] = ['like',"%$name%"];
        }

        $list = Db::name('admin')->where($where)->select();
        $count = Db::name('admin')->where($where)->count();

        $this->assign('admin_name',$name);
        $this->assign('count',$count);
        $this->assign('list',$list);

        return $this->fetch('admin_list');
    }

    # 添加管理员
    public function admin_add()
    {
        $act = input('act/s');

        $group = Db::name('admin_group')->select();

        $this->assign('act',$act);
        $this->assign('group',$group);

        return $this->fetch();
    }

    # 编辑管理员
    public function admin_edit()
    {
        $admin_id = input('id/d',0);
        $act = input('act/s');
        
        if ($admin_id <= 0) {
            return '<p style="text-align: center; margin-top: 3em; color: red; font-size: 2.5rem;">找不到该管理员的信息</p>';
        }

        $admin_info = Db::name('admin')->where('id',$admin_id)->find();

        $group = Db::name('admin_group')->select();

        $this->assign('act',$act);
        $this->assign('group',$group);
        $this->assign('info',$admin_info);

        return $this->fetch();
    }

    # 管理员增删改
    public function admin_handle()
    {
        $data['name'] = trim(input('adminName'));
        $data['password'] = trim(input('password'));
        $data['password_confirm'] = trim(input('password2'));
        $data['mobile'] = intval(trim(input('phone'))) ?: '';
        $data['email'] = trim(input('email'));
        $data['group_id'] = input('group/d');
        $data['is_lock'] = input('is_lock/d',0);
        $data['is_super'] = input('is_super/d',0);
        $data['note'] = trim(input('note/s'));
        $act = input('act/s');
        $admin_id = input('admin_id');
        
        $admin = new AdminModel;
        $admin_validate = Loader::Validate('Admin');
        $result = array('status'=>0,'msg'=>"参数错误！");
        
        //添加
        if ($act == 'add') {
            if (!$admin_validate->check($data)) {
                $result['msg'] = $admin_validate->getError();
            } else {
                $data['password'] = get_encrypt($data['password']);
                $bool = $admin->allowField(true)->save($data);
                
                if ($bool) {
                    $result['status'] = 1;
                    $result['msg'] = "操作成功！";
                } else {
                    $result['msg'] = "操作失败！";
                }
            }
        }
        //修改
        if ($act == 'edit') {
            $data['id'] = intval($admin_id);
            if (!$admin_validate->scene('edit')->check($data)) {
                $result['msg'] = $admin_validate->getError();
            } else {
                if ($data['password']) {
                    $data['password'] = get_encrypt($data['password']);
                } else {
                    unset($data['password']);
                }

                $bool = $admin->allowField(true)->isUpdate(true)->save($data);

                if ($bool) {
                    $result['status'] = 1;
                    $result['msg'] = "操作成功！";
                } else {
                    $result['msg'] = "操作失败！";
                }
            }
        }
        //删除
        if ($act == 'del' && $admin_id) {
            $bool = $admin->destroy($admin_id);
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "删除成功！";
            } else {
                $result['msg'] = "删除失败！";
            }
        }
        
        return json($result);
    }

    # 管理员状态修改
    public function admin_is_lock()
    {
        $admin_id = input('admin_id/d',0);
        $is_lock = input('is_lock/d',0);

        $result = array('status'=>0);
        
        if ($admin_id > 0) {
            $bool = Db::name('admin')->where('id',$admin_id)->update(['is_lock'=>$is_lock]);

            if ($bool) {
                $result['status'] = 1;
            }
        }

        return json($result);
    }

}