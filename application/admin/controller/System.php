<?php

namespace app\admin\controller;

use think\Db;
use app\admin\model\Menu;

/**
 * 系统设置类
 */

class System extends Base
{
    # 菜单列表
    public function menu()
    {
        $menu = Db::name('menu')->select();
        $count = Db::name('menu')->count();

        $this->assign('count',$count);
        $this->assign('menu',$menu);
        return $this->fetch();
    }

    # 添加菜单
    public function add_menu()
    {
        $menu_id = input('id/d',0);
        $type = input('type/s','add');

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

        $this->assign('type',$type);
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
        $id = input('id/d',0);
        $type = input('type/s');
        
        $result = array('status'=>0,'msg'=>"没有内容！");

        if (!$data['name'] && ($type != 'del')) {
            $result['msg'] = "请填写菜单名称！";
            return json($result);
        }
        if (($data['parent_id'] > 0) && !$data['url'] && ($type != 'del')) {
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
        if ($type == 'add') {
            $bool = $menu->save($data);
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "操作成功！";
            } else {
                $result['msg'] = "操作失败！";
            }
        }
        //更改菜单
        if ($type == 'edit') {
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
        if (($type == 'del') && ($id > 0)) {
            // $bool = $menu->delete($id);
            $bool = true;
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "删除成功！";
            } else {
                $result['msg'] = "删除失败！";
            }
        }
        
        return json($result);
    }
}