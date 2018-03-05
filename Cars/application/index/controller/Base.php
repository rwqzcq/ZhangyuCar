<?php
namespace app\index\controller;
use think\Controller;
use think\Session;
use app\index\model\User;
class Base extends Controller
{
  protected $mainAction = ['scrap', 'release']; //  主要进行的操作
  protected $user = null; // 用户
  protected function _initialize()
  {
    parent::_initialize();  //继承父类中的初始化操作
    define('USER_ID', Session::get('user_id'));
    $user_id = Session::get('user_id');
    $role = '';
    if($user_id != null) {
      $this->user = User::get($user_id);
      $role = $this->user->role;          
    }
    $this->assign('role', $role);
    header('Content-type:text/html; charset=utf-8');
  }

  //判断用户是否登录，放在后台的入口：index/index
  protected function isLogin()
  {
    if (is_null(USER_ID)) {
      $this -> error('用户未登录，无权访问',url('user/login'));
    }
  }

  //防止用户重复登录 user/login
  protected function alreadyLogin()
  {
    if (!is_null(USER_ID)) {
      $this -> error('用户已经登录，请勿重复登录',url('index/index'));
    }
  }
}
