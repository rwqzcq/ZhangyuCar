<?php
namespace app\index\controller;

use think\Request;
use app\index\model\User as UserModel;
use think\Session;
use think\Db;
class User extends Base
{
    //登陆界面
    public function login()
    {
        $this -> alreadyLogin();
        $this -> view -> assign('title', '用户登录');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');
        $this -> view -> assign('copyRight', '本案例仅供PHP中文网(www.php.cn)学员学习交流,请勿商用,责任自负');
        return $this -> view -> fetch();
    }

    //登录验证
    public function checkLogin(Request $request)
    {
        $status = 0; //验证失败标志
        $result = '验证失败'; //失败提示信息
        $data = $request -> param();

        //验证规则
        $rule = [
            'name|姓名' => 'require',
            'password|密码'=>'require',
            'captcha|验证码' => 'require|captcha'
        ];

        //验证数据 $this->validate($data, $rule, $msg)
        $result = $this -> validate($data, $rule);

        //通过验证后,进行数据表查询
        //此处必须全等===才可以,因为验证不通过,$result保存错误信息字符串,返回非零
        if (true === $result) {

            //查询条件
            $map = [
                'name' => $data['name'],
                'password' => md5($data['password'])
            ];

            //数据表查询,返回模型对象
            $user = UserModel::get($map);
            if (null === $user) {
                $result = '没有该用户,请检查';
            } else {
                $status = 1;
                $result = '验证通过,点击[确定]后进入后台';

                //创建2个session,用来检测用户登陆状态和防止重复登陆
                Session::set('user_id', $user -> id);
                Session::set('user_info', $user -> getData());

                //更新用户登录次数:自增1
                $user -> setInc('login_count');
            }
        }
        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    //退出登陆
    public function logout()
    {
        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        UserModel::update(['login_time'=>time()],['id'=> Session::get('user_id')]);
        Session::delete('user_id');
        Session::delete('user_info');

        $this -> success('注销登陆,正在返回',url('user/login'));
    }

    //管理员列表
    public function  adminList()
    {
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> assign('keywords', 'PHP中文网教学系统');
        $this -> view -> assign('desc', '教学案例');

        $this -> view -> count = UserModel::count();

        //判断当前是不是admin用户
        //先通过session获取到用户登陆名
        $userName = Session::get('user_info.name');
        if ($userName == 'admin') {
            $list = UserModel::all();  //admin用户可以查看所有记录,数据要经过模型获取器处理
        } else {
            //为了共用列表模板,使用了all(),其实这里用get()符合逻辑,但有时也要变通
            //非admin只能看自己信息,数据要经过模型获取器处理
            $list = UserModel::all(['name'=>$userName]);
        }


        $this -> view -> assign('list', $list);
        //渲染管理员列表模板
        return $this -> view -> fetch('admin_list');
    }

    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        if($result->getData('status') == 1) {
            UserModel::update(['status'=>0],['id'=>$user_id]);
        } else {
            UserModel::update(['status'=>1],['id'=>$user_id]);
        }
    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        $this->view->assign('title','编辑管理员信息');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        $this->view->assign('user_info',$result->getData());
        return $this->view->fetch('admin_edit');
    }

    //更新数据操作
    public function editUser(Request $request)
    {
        //获取表单返回的数据
        $data = $request -> param();
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }

        $condition = ['id'=>$data['id']] ;
        $result = UserModel::update($data, $condition);

        //如果是admin用户,更新当前session中用户信息user_info中的角色role,供页面调用
        if (Session::get('user_info.name') == 'admin') {
            Session::set('user_info.role', $data['role']);
        }

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }

    //删除操作
    public function deleteUser(Request $request)
    {
        $user_id = $request -> param('id');
        UserModel::update(['is_delete'=>1],['id'=> $user_id]);
        UserModel::destroy($user_id);
    }

    //添加操作的界面
    public function  adminAdd()
    {
        $this->view->assign('title','添加管理员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        return $this->view->fetch('admin_add');
    }

    //检测用户名是否可用
    public function checkUserName(Request $request)
    {
        $userName = trim($request -> param('name'));
        $status = 1;
        $message = '用户名可用';
        if (UserModel::get(['name'=> $userName])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '用户名重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //检测用户邮箱是否可用
    public function checkUserEmail(Request $request)
    {
        $userEmail = trim($request -> param('email'));
        $status = 1;
        $message = '邮箱可用';
        if (UserModel::get(['email'=> $userEmail])) {
            //查询表中找到了该邮箱,修改返回值
            $status = 0;
            $message = '邮箱重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';

        $rule = [
            'name|用户名' => "require|min:3|max:10",
            'password|密码' => "require|min:3|max:10",
            'email|邮箱' => 'require|email'
        ];

        $result = $this -> validate($data, $rule);
        if ($result === true) {
            // 添加相关逻辑 isset不可用
            if(isset($data['parking'])) {
                $table_name = 'parking_user';
                $value = $data['parking'];
                unset($data['parking']);              
            } else if(isset($data['squadron'])) {
                $table_name = 'squadron_user';
                $value = $data['squadron'];
                unset($data['squadron']);                
            }
            $user= UserModel::create($data);
            if($table_name == 'parking_user') {
                Db::table($table_name)->insert(['user_id' => $user->id, 'parking_id' => intval($value)]);
            } else if($table_name == 'squadron_user') {
                Db::table($table_name)->insert(['user_id' => $user->id, 'suqadron_id' => intval($value)]);
            }
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        }
        return ['status'=>$status, 'message'=>$message];
    }
    /**
     * 返回交警中队或者停车场的数据
     */
    public function getList(Request $request) {
        $type_id = intval($request->param('type', '', 'trim'));
        return $type_id == 1 ? Db::name('squadron')->field(['id', 'squadron_name as name'])->select() : Db::name('parking')->field(['id', 'parking_name as name'])->select();
    }
}
