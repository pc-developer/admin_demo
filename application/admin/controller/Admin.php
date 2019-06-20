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
        $where['id'] = ['>',0];

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
        $data['mobile'] = trim(input('phone'));
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
        if (($act == 'del') && $admin_id) {
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

    # 分组列表
    public function group_list()
    {
        $name = input('name');

        $where['id'] = ['>',0];
        if ($name) {
            $where['name'] = ['like',"%$name%"];
        }
        
        $list = Db::name('admin_group')->where($where)->select();
        $count = Db::name('admin_group')->where($where)->count();

        $this->assign('count',$count);
        $this->assign('name',$name);
        $this->assign('list',$list);

        return $this->fetch();
    }

    # 添加分组
    public function group_add()
    {
        // $group_id = input('id/d');
        $act = input('act/s');

        // if (($act == 'edit') && !$group_id) {
        //     return '<p style="text-align: center; margin-top: 3em; color: red; font-size: 2.5rem;">找不到该分组信息</p>';
        // }

        $menu = Db::name('menu')->order('sort asc,id asc')->field('id,name,parent_id')->select();

        $menu_list = array();
        
        foreach ($menu as $k1 => $v1) {
            if ($v1['parent_id'] == 0) {
                $menu_list[$v1['id']] = $v1;
                unset($menu[$k1]);

                if (is_array($menu)) {
                    foreach ($menu as $k2 => $v2) {
                        if ($v2['parent_id'] == $v1['id']) {
                            $menu_list[$v1['id']]['next'][$v2['id']] = $v2;
                            unset($menu[$k2]);
                        }
                    }
                }
            }
        }
        
        $this->assign('act',$act);
        $this->assign('menu_list',$menu_list);

        return $this->fetch();
    }

    # 分组增删改
    public function group_handle()
    {
        $data = $_POST;
        $result = array('status'=>0,'msg'=>"参数错误！");
        
        if ($data) {
            $name = trim($data['name']);
            $note = trim($data['note']);
            $act = $data['act'];
            unset($data['name']);
            unset($data['note']);
            unset($data['act']);
            $data_keys = array_keys($data);
            $data_keys = json_encode($data_keys);

            if (!$name) {
                $result['msg'] = "分组名称必填！";
                return json($result);
            }
            $time = time();

            if ($act == 'add') {
                $bool = Db::name('admin_group')->insert(['name'=>$name,'jurisdiction'=>$data_keys,'create_time'=>$time,'update_time'=>$time,'note'=>$note]);

                if ($bool) {
                    $result['status'] = 1;
                    $result['msg'] = "操作成功！";
                } else {
                    $result['msg'] = "操作失败！";
                }
            }
            // $permissions = array();
            
            // foreach ($data_keys as $k1 => $v1) {
            //     $temp = explode('_',$v1);
                
            //     if (is_array($temp)) {
                    
            //         foreach ($temp as $k2 => $v2) {
            //             $permissions[$v2] = '';
            //         }
            //     }
            // }
        }
        
        return json($result);
    }
}