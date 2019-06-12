<?php

namespace app\admin\controller;

class Login extends Base
{
    public function index()
    {
        return $this->redirect('admin/login/login');
    }

    public function login()
    {
        return $this->fetch();
    }
}