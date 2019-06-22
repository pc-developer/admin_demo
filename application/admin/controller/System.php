<?php

namespace app\admin\controller;

use think\Db;
use app\admin\model\Menu;

/**
 * 系统设置类
 */

class System extends Base
{
    private $setting = [];

    public function _initialize()
    {
        parent::_initialize();

        $this->setting = array(
            'web_info'  => ['order'=>0,'url'=>'/admin/system/index','name'=>"网站信息"],
            'smtp'      => ['order'=>1,'url'=>'/admin/system/smtp','name'=>"邮箱设置"],
        );

        array_multisort(array_column($this->setting,'order'),SORT_ASC,$this->setting); //order列升序排列
        
        $this->assign('list',$this->setting);
    }

    # 网站设置
    public function index()
    {
        $index = input('index/d',0);

        $this->assign('index',$index);
        $this->assign('inc_type','web_info');

        return $this->fetch();
    }

    # 邮箱设置
    public function smtp()
    {
        $index = input('index/d',0);

        $this->assign('index',$index);
        $this->assign('inc_type','smtp');

        return $this->fetch();
    }
    
    # 菜单列表
    public function menu()
    {
        $name = trim(input('name'));
        $where['id'] = ['>',0];
        
        if ($name) {
            $where['name'] = ['like',"%$name%"];
        }
        
        $menu = Db::name('menu')->where($where)->whereOr('id','in',function($query) use ($where){
            $query->name('menu')->where($where)->where('parent_id','>',0)->field('parent_id');
        })->order('sort desc,id asc')->select();
        
        $count = Db::name('menu')->where($where)->count();
        
        if ($menu) {
            $list = array();
            foreach ($menu as $k1 => $v1) {
                if ($v1['parent_id'] == 0) {
                    array_push($list,$menu[$k1]);
                    unset($menu[$k1]);
                    if(is_array($menu)){
                        foreach($menu as $k2 => $v2){
                            if($v2['parent_id'] == $v1['id']){
                                array_push($list,$menu[$k2]);
                                unset($menu[$k2]);
                            }
                        }
                    }
                }
            }
        }

        $this->assign('name',$name);
        $this->assign('count',$count);
        $this->assign('list',$list);
        return $this->fetch();
    }

    # 添加菜单
    public function add_menu()
    {
        $menu_id = input('id/d',0);
        $act = input('act/s','add');

        if ($menu_id > 0) {
            $info = Db::name('menu')->where('id',$menu_id)->find();
            $this->assign('info',$info);
        }
        
        $menu[0] = ['id'=>0, 'name' => '顶级菜单'];
        $res = Db::query("select `id`,`name` from `ppc_menu` where `level` = 1 and `id` != '$menu_id' order by `id` asc");
        if($res){
            foreach($res as $v){
                $menu[$v['id']] = $v;
            }
        }

        $this->assign('act',$act);
        $this->assign('menu',$menu);
        
        return $this->fetch();
    }

    # 菜单增删改
    public function menu_handle()
    {
        $data['name'] = input('name/s');
        $data['parent_id'] = input('parent_id/d',0);
        $data['url'] = input('url/s');
        $data['icon'] = input('icon/s');
        $data['sort'] = input('sort/d',0);
        $data['is_show'] = input('is_show/d',1);
        $id = input('id');
        $act = input('act/s');
        
        $result = array('status'=>0,'msg'=>"没有内容！");

        if (!$data['name'] && ($act != 'del')) {
            $result['msg'] = "请填写菜单名称！";
            return json($result);
        }
        if (($data['parent_id'] > 0) && !$data['url'] && ($act != 'del')) {
            $result['msg'] = "请填写菜单地址！";
            return json($result);
        }
        
        $menu = new Menu;

        $data['level'] = 1;

        if ($data['parent_id'] > 0) {
            $info = $menu->where('id',$data['parent_id'])->find();
            if ($info['parent_id'] != $data['parent_id']) {
                $data['level'] = $info['level'] + 1;
            }
        }
        
        //新增菜单
        if ($act == 'add') {
            $bool = $menu->save($data);
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "操作成功！";
            } else {
                $result['msg'] = "操作失败！";
            }
        }
        //更改菜单
        if ($act == 'edit') {
            $data['id'] = $id;
            $bool = $menu->isUpdate(true)->save($data);
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "操作成功！";
            } else {
                $result['msg'] = "操作失败！";
            }
        }
        //删除菜单
        if (($act == 'del') && $id) {
            $id = array_filter(explode(',',$id));
            $bool = $menu->where('id','in',$id)->whereOr('parent_id','in',$id)->delete();
            
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "删除成功！";
            } else {
                $result['msg'] = "删除失败！";
            }
        }
        
        return json($result);
    }

    # 菜单状态修改
    public function menu_is_show(){
        $menu_id = input('menu_id/d',0);
        $is_show = input('is_show/d',1);
        $result = array('status'=>0);
        if ($menu_id > 0) {
            //隐藏后，子菜单也会隐藏，显示只显示选中菜单
            $where = array('id'=>$menu_id);
            if ($is_show == 0) {
                $where = ['id|parent_id'=>$menu_id];
            }
            $bool = Db::name('menu')->where($where)->update(['is_show'=>$is_show]);
            if ($bool) {
                $result['status'] = 1;
            }
        }
        
        return json($result);
    }
}