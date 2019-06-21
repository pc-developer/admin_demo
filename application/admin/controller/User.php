<?php

namespace app\admin\controller;

use think\Db;
use app\common\model\Users as UsersModel;
use think\Loader;

class User extends Base
{
    # 会员列表
    public function index()
    {
        $user_id = input('user_id');
        $user_name = input('user_name/s');
        $datemin = input('datemin/s');
        $datemax = input('datemax/s');
        $sel_time = input('sel_time/d');
        
        $where['id'] = ['>',0];

        if ($user_id != '') {
            $where['id'] = $user_id;
        }
        if ($user_name) {
            $where['nickname|mobile|email'] = ['like',"%$user_name%"];
        }
        if ($datemin || $datemax) {
            if ($datemin && $datemax) {
                $date = ['between time',[strtotime($datemin),strtotime('+1 day',strtotime($datemax))]];
            } elseif ($datemin) {
                $date = ['> time',strtotime($datemin)];
            } else {
                $date = [['< time',strtotime('+1 day',strtotime($datemax))],['>',0],'and'];
            }

            switch ($sel_time) {
                case 1:
                    $where['register_time'] = $date;
                    break;
                case 2:
                    $where['last_login_time'] = $date;
                    break;
                default:
                    break;
            }
        }

        $UsersModel = new UsersModel;
        $list = $UsersModel->where($where)->field('id,nickname,sex,mobile,email,status,register_time')->paginate(10);
        
        $this->assign('user_id',$user_id);
        $this->assign('user_name',$user_name);
        $this->assign('datemin',$datemin);
        $this->assign('datemax',$datemax);
        $this->assign('sel_time',$sel_time);
        $this->assign('list',$list);

        return $this->fetch();
    }

    # 添加会员
    public function user_add()
    {
        $act = input('act/s');

        $this->assign('act',$act);

        return $this->fetch();
    }

    # 会员信息
    public function user_show()
    {
        $user_id = input('user_id/d',0);
        
        if ($user_id <= 0) {
            return return_msg("找不到该会员信息");
        }
        $UsersModel = new UsersModel;
        $user = $UsersModel->field('password',true)->find($user_id);

        $this->assign('user',$user);

        return $this->fetch();
    }

    # 编辑会员
    public function user_edit()
    {
        $act = input('act/s');
        $user_id = input('id/d',0);

        if ($user_id <= 0) {
            return return_msg("找不到该会员信息");
        }

        $user_info = Db::name('users')->where('id',$user_id)->find();

        $this->assign('act',$act);
        $this->assign('user_info',$user_info);

        return $this->fetch();
    }

    # 会员增删改
    public function user_handle()
    {
        $act = input('act/s');
        $data['nickname'] = trim(input('nickname/s'));
        $data['password'] = trim(input('password/s'));
        $data['password_confirm'] = trim(input('password2'));
        $data['sex'] = input('sex/d',0);
        $data['mobile'] = trim(input('mobile'));
        $data['email'] = trim(input('email/s'));
        $data['status'] = input('status/d',1);
        $user_id = input('id');

        $result = array('status'=>0,'msg'=>"参数错误！");

        $UsersModel = new UsersModel;
        $users_validate = Loader::Validate('Users');

        if ($act == 'add') {
            if (!$users_validate->check($data)) {
                $result['msg'] = $users_validate->getError();
            } else {
                $data['password'] = get_password($data['password']);
                $data['register_time'] = time();
                $bool = $UsersModel->allowField(true)->save($data);
                if (!$bool) {
                    $result['msg'] = "操作失败！";
                }

                $result['status'] = 1;
                $result['msg'] = "操作成功！";
            }
        }
        if ($act == 'edit') {
            if (!$users_validate->scene('edit')->check($data)) {
                $result['msg'] = $users_validate->getError();
            } else {
                $data['id'] = intval($user_id);
                if ($data['password']) {
                    $data['password'] = get_password($data['password']);
                } else {
                    unset($data['password']);
                }
                
                $bool = $UsersModel->allowField(true)->isUpdate(true)->save($data);

                if ($bool) {
                    $result['msg'] = "操作成功！";
                    $result['status'] = 1;
                } else {
                    $result['msg'] = "操作失败！";
                }
            }
        }
        if (($act == 'del') && $user_id) {
            $bool = $UsersModel->destroy($user_id);
            if ($bool) {
                $result['status'] = 1;
                $result['msg'] = "删除成功！";
            } else {
                $result['msg'] = "删除失败！";
            }
        }
        if (($act == 'pwd') && $data['password']) {
            if (!$users_validate->scene('pwd')->check($data)) {
                $result['msg'] = $users_validate->getError();
            } else {
                $password = get_password($data['password']);
                $bool = $UsersModel->where('id',$user_id)->update(['password'=>$password]);
                if ($bool) {
                    $result['status'] = 1;
                    $result['msg'] = "修改成功！";
                } else {
                    $result['msg'] = "修改失败！";
                }
            }
        }
        
        return json($result);
    }

    # 更改会员状态
    public function user_status()
    {
        $user_id = input('user_id/d',0);
        $status = input('status/d',1);
        $result = array('status'=>0);

        if ($user_id > 0) {
            $bool = Db::name('users')->where('id',$user_id)->update(['status'=>$status]);

            if ($bool) {
                $result['status'] = 1;
            }
        }

        return json($result);
    }

    # 修改密码
    public function change_password()
    {
        $user_id = input('user_id/d',0);
        $act = input('act/s');

        if ($user_id <= 0) {
            return return_msg("找不到该会员");
        }

        $user = Db::name('users')->field('id,nickname,mobile,email')->find($user_id);

        $this->assign('act',$act);
        $this->assign('user',$user);

        return $this->fetch();
    }
}