<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Request;

/**
 * 后台公共类
 */
class Base extends Controller
{
    private $ip = '';
    public $admin = '';
    public $id = '';

    public function _initialize()
    {
        $request = Request::instance();
        $this->ip = $request->ip();
        if (!session('?admin')) {
            Session::clear();
            $this->redirect('admin/login/index');
        }
        $this->id = session('admin.id');
        $last_login_ip = Db::name('admin')->where('id',$this->id)->value('last_login_ip');
        if ($this->ip != $last_login_ip) {
            Session::clear();
            $this->redirect('admin/login/index');
        }

        $this->admin = session('admin');
        session('admin_id',$this->id);

        $global_menu_list = $this->get_menu();

        $this->assign('global_menu',$global_menu_list);
    }

    # 获取菜单
    public function get_menu()
    {
        if ($this->admin['is_super'] == 1) {
            $sql = "select `id`,`name`,`icon` from `ppc_menu` where `is_show` = 1 and `parent_id` = 0 order by `sort` desc,`id` asc";
        }

        $global_menu_list = Db::query($sql);
        if ($global_menu_list) {
            foreach ($global_menu_list as $key => $value) {
                $next = Db::query("select `id`,`name`,`url` from `ppc_menu` where `is_show` = 1 and `parent_id` = '$value[id]'  order by `sort` desc,`id` asc");
                $global_menu_list[$key]['next'] = $next;
            }
        }
        return $global_menu_list;
    }
}