<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Request;
use think\Cache;
use think\Cookie;

/**
 * 后台公共类
 */
class Base extends Controller
{
    private $ip = '';
    protected $admin = '';
    protected $id = '';
    protected $module = '';
    protected $controller = '';
    protected $action = '';

    public function _initialize()
    {
        $this->get_request();
        if (!session('?admin')) {
            Session::clear();
            Cache::clear();
            $this->redirect('admin/login/index');
        }
        $this->id = session('admin.id');
        $last_login_ip = Db::name('admin')->where('id',$this->id)->value('last_login_ip');
        if ($this->ip != $last_login_ip) {
            Session::clear();
            Cache::clear();
            $this->redirect('admin/login/index');
        }

        $this->admin = session('admin');
        session('admin_id',$this->id);
        
        if (!Cookie::has('web_info')) {
            $this->get_web_info();
        }
        $this->get_skin();
        $global_menu_list = $this->get_menu();
        
        $this->assign('admin_identity',$this->admin);
        $this->assign('global_menu',$global_menu_list);
    }

    # 皮肤
    public function get_skin()
    {
        $skin = Db::name('config')->where(['name'=>'admin_skin','inc_type'=>'base'])->field('name,value,inc_type')->find();
        $this->assign('skin',$skin);
    }

    # 获取请求信息
    public function get_request()
    {
        $request = Request::instance();
        $this->ip = $request->ip();
        $this->module = $request->module();
        $this->controller = $request->controller();
        $this->action = $request->action();
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

    # 网站信息
    public function get_web_info()
    {
        $info = Db::name('config')->where(['inc_type'=>'web_info'])->column('name,value');
        
        Cookie::set('web_info',$info,3600);
    }
}