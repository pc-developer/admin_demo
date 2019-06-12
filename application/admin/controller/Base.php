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
        $this->admin = Session::get('admin');
        if (!$this->admin) {
            Session::clear();
            $this->redirect('/admin/login/index');
        }
    }

    # 获取菜单
    public function get_menu()
    {
        if ($this->admin['is_super']) {
            $sql = "select `id`,`name`,`icon` from `ppc_menu` where `is_show` = 1 and `parent_id` = 0 order by `sort` desc,`id` asc";
        }

        $global_menu_list = Db::query($sql);
        if($global_menu_list){
            foreach($global_menu_list as $k => $v){
                if($this->admin['is_super']){
                    $last = Db::query("select `id`,`name`,`url` from `ppc_menu` where `is_show` = 1 and `parent_id` = '$v[id]' order by `sort` desc,`id` asc");
                }else{
                    $last = Db::query("select `id`,`name`,`url` from `ppc_menu` where `is_show` = 1 and `parent_id` = '$v[id]' and `id` in (".$this->jurisdiction_str.") order by `sort` desc,`id` asc");
                }
                $global_menu_list[$k]['last'] = $last;
            }
        }
        return $global_menu_list;
    }
}