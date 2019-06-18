<?php

namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Db;
use think\Request;

class Login extends Controller
{
    private $ip = '';

    public function _initialize()
    {
        $request = Request::instance();
        $this->ip = $request->ip();
    }

    public function index()
    {
        if (session('?admin')) {
            $this->redirect('admin/index/index',301);
            exit();
        }
        
        $this->redirect('admin/login/login');
        exit();
    }

    public function login()
    {
        return $this->fetch();
    }

    //登陆验证
    public function do_login()
    {
        $name = trim(input('name/s'));
        $password = input('password');
        $captcha = trim(input('captcha/s'));
        $result = array('status'=>0,'msg'=>"参数错误！");
        
        if (empty($name)) {
            $result['msg'] = "请输入用户名！";
            return json($result);
        }
        if (empty($password)) {
            $result['msg'] = "请输入密码！";
            return json($result);
        }
        if (empty(trim($password))) {
            $result['msg'] = "密码不能为空字符串！";
            return json($result);
        }
        $password = trim($password);
        if (empty($captcha)) {
            $result['msg'] = "请输入验证码！";
            return json($result);
        }
        
        if (!captcha_check($captcha)) {
            $result['msg'] = "验证码错误！";
            return json($result);
        }

        $admin_info = Db::name('admin')->where('name',$name)->find();

        if ($admin_info) {
            $password = get_encrypt($password);
            
            if ($password == $admin_info['password']) {
                if ($admin_info['is_lock'] == 1) {
                    $result['msg'] = "该用户已被禁用！";
                    return json($result);
                }
                
                Db::name('admin')->where('id',$admin_info['id'])->update(['last_login_ip'=>$this->ip]);
                $admin = array('id'=>$admin_info['id'],'name'=>$admin_info['name'],'is_super'=>$admin_info['is_super']);
                Session::set('admin',$admin);

                $result['status'] = 1;
                $result['msg'] = "登陆成功！";
            } else {
                $result['msg'] = "用户名或密码错误！";
            }
        } else {
            $result['msg'] = "用户名或密码错误！";
        }

        return json($result);
    }

    //退出
    public function logout()
    {
        session(null);
        return redirect('admin/login/login');
    }
}