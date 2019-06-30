<?php

namespace app\admin\controller;

use think\Db;
use app\admin\model\Menu;
use think\Loader;

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
        $inc_type = 'web_info';

        $temp = $this->get_setting($inc_type);
        $config = $this->handle_config($temp);
        
        $this->assign('index',$index);
        $this->assign('inc_type',$inc_type);
        $this->assign('config',$config);

        return $this->fetch();
    }

    public function get_setting($inc_type)
    {
        $config = Db::name("config")->where('inc_type',$inc_type)->select();
        return $config;
    }

    # 设置处理函数
    public function handle_config($data)
    {
        $result = array();
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $result[$value['name']] = $value['value'];
            }
        }
        return $result;
    }

    # 设置增删改
    public function setting_handle()
    {
        $data = input('post.');

        $result = array('status'=>0,'msg'=>"参数错误！");

        if (!$data) {
            return json($result);
        }

        $system_validate = Loader::Validate('System');
        
        if (!$system_validate->scene($data['inc_type'])->check($data)) {
            $result['msg'] = $system_validate->getError();
        }

        $inc_type = $data['inc_type'];
        unset($data['inc_type']);
        $temp = $this->get_setting($inc_type);  //获取设置
        $config = $this->handle_config($temp);  //设置处理

        $file_dir = ROOT_PATH . 'public';
        $img = array();

        foreach ($data as $k1 => $v1) {
            if (is_file($file_dir.$v1)) {
                if (!isset($config[$k1]) || ($v1 != $config[$k1])) {
                    $img[] = [$k1=>$v1];
                }
            }
        }

        //移动新上传图片
        if ($img) {
            $path = '/images/setting/';
            $img_path = $this->move_img($img,$path,$file_dir);    //移动图片
            
            foreach ($img_path['img'] as $k2 => $v2) {
                $data[$k2] = $v2;
            }
        }

        $add_data = array();

        foreach ($temp as $key => $value) {
            $config_ids[$value['name']] = $value['id'];
        }

        Db::startTrans();   //开启事务
        $bool = false;

        foreach ($data as $k3 => $v3) {
            $is_up = false;
            foreach ($config as $k4 => $v4) {
                if ($k3 == $k4) {
                    $is_up = true;
                    if ($v3 != $v4) {
                        $bool = Db::name('config')->update(['id'=>$config_ids[$k4],'value'=>trim($v3)]);
                        if (!$bool) {
                            $add_data = array();
                            break 2;
                        }
                    }
                    
                    unset($config[$k4]);
                    break;
                }
            }
            
            if (!$is_up && $v3) {
                $add_data[] = ['name'=>$k3,'value'=>trim($v3),'inc_type'=>$inc_type];
            }
        }
        
        if ($add_data) {
            $bool = Db::name('config')->insertAll($add_data);
        }
        
        if ($bool) {
            if (isset($img_path['del'])) {
                foreach ($img_path['del'] as $k5 => $v5) {
                    @unlink($file_dir.$v5);
                }
            }
            Db::commit();   //提交事务
            $result['status'] = 1;
            $result['msg'] = "操作成功！";
        } else {
            Db::rollback(); //事务回滚
            $result['msg'] = "操作失败！";
        }

        return json($result);
    }

    /**
     * 移动图片
     * @param array $data 图片（含相对路径）
     * @param string $path  移动图片路径
     * @param string $file_dir 绝对路径
     * @return array $result 图片（包含相对路径）
     */
    public function move_img($data,$path = '',$file_dir = '')
    {
        $file_dir = $file_dir ?: ROOT_PATH . 'public';
        $path = $path ?: '/images/';
        $result = array();

        if (is_array($data)) {
            foreach ($data as $k1 => $v1) {
                if (is_array($v1)) {
                    $img_name = md5(time().mt_rand(0,10000)).'.jpg';
                    foreach ($v1 as $k2 => $v2) {
                        if (is_file($file_dir.$v2)) {
                            $image = \think\Image::open($file_dir.$v2);
                            $bool = $image->save($file_dir.$path.$img_name);
                            if ($bool) {
                                $result['img'][$k2] = $path.$img_name;
                                $result['del'][] = $v2;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    # 上传图片
    public function upload_images()
    {
        if(isset($_FILES['image'])){
            $module = isset($_POST['module']) ? trim($_POST['module']) : '';
            if(!$module){
                echo "<script>parent.iframe_images_callback(0,'')</script>";
                exit;
            }
            $file = request()->file('image');
            $files_dir = ROOT_PATH . 'public/images/'.$module.'/temp';
            
            $info = $file->move($files_dir);
            if($info){
                $src_dir = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/images/'.$module.'/temp/';
                $savename = str_replace('\\','/',$info->getSaveName());
                $src_dir .= $savename;
                echo "<script>parent.iframe_images_callback(1,' $src_dir','/images/$module/temp/$savename')</script>";
            }else{
                echo "<script>parent.iframe_images_callback(0,'')</script>";
            }
        }
        exit;
    }

    # 删除图片
    public function del_img()
    {
        $inc_type = input('inc_type/s');
        $name = input('name/s');
        $img = input('img/s');
        
        $result = array('status'=>0);
        $file_dir = ROOT_PATH . 'public/';
        $is_del = false;
        $is_update = true;

        Db::startTrans();
        
        if ($name) {
            $where = array('name'=>$name,'inc_type'=>$inc_type);
            $config = Db::name('config')->where($where)->find();
            if ($config) {
                $is_update = Db::name('config')->where($where)->update(['value'=>'']);
            }
        }
        
        if ($is_update && is_file($file_dir.$img)) {
            $is_del = @unlink($file_dir.$img);
        }
        
        if ($is_del) {
            Db::commit();
            $result['status'] = 1;
        } else {
            Db::rollback();
        }

        return json($result);
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