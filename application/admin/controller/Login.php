<?php

namespace app\admin\controller;

use think\Controller;
use think\Session;

class Login extends Controller
{
    public function index()
    {
        if (session('?admin')) {
            $this->redirect('admin/index/index');
            exit();
        }
        
        $this->redirect('admin/login/login');
    }

    public function login()
    {
        $this->index();
        return $this->fetch();
    }
}