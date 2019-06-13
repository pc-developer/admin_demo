<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;

/**
 * 后台公共类
 */
class Base extends Controller
{
    public function _initialize()
    {
        // $this->admin = Session::get('admin');
        // if (!$this->admin) {
        //     Session::clear();
        //     $this->redirect('Login/login');
        // }
        $global_menu_list = $this->get_menu();
        $this->assign('global_menu',$global_menu_list);
    }

    # 获取菜单
    public function get_menu()
    {
        // if ($this->admin['is_super']) {
        $sql = "select `id`,`name`,`icon` from `ppc_menu` where `is_show` = 1 and `parent_id` = 0 order by `sort` desc,`id` asc";
        // }

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