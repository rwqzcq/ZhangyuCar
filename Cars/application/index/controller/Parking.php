<?php
namespace app\index\controller;
use app\index\Controller\Base;
use app\index\Model\Detainedcars as CarModel;
use app\index\Model\CarChange;
use think\Request;
use app\index\validate\CarChange as CarValidate;
use think\Db;

class Parking extends Base
{
    protected $parking_id = null;
    protected $actionStatus = [
        'scrap' => 3, // 待报废
        'release' => 2, // 待放行
    ];
    /**
     * 车场初始化
     */
    protected function _initialize()
    {
        parent::_initialize();  //继承父类中的初始化操作
        // 权限管理
        $limit = [
            0=>'管理员',
            2=> '停车场人员'
        ];
        if(!in_array($this->user->role, $limit)) {
            return $this->error('只有停车场的人才有权限查看此模块');
        }
        // 找到目前登录用户所在的停车场的id
        if($this->user->role == $limit[2]) {
            $this->parking_id = $this->user->parkingUser->parking_id;
        }
    }
    /**
     * 当前停车场之下的停车列表
     */
    public function  parkingList()
    {
        $map = [];
        $cars = null;
        if($this->user->role === '管理员') {
            $cars = CarModel::all();
            echo '管理员登录';
        } else {
            $parking_id = $this->user->parkingUser->parking_id;
            $cars = CarModel::all(function($query)use($parking_id){
                $query->where(['detained_parking' => $parking_id]);
            });
        }
        $this->assign('cars', $cars);


        $this->view->assign('title','录入车辆');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        return $this->view->fetch('parking_list');
    }
    /**
     *处理车辆报废和车辆放行
     */
    public function save(Request $request)
    {
        // 只有停车场人员可以执行该操作！
        if($this->user->role != '停车场人员') {
            return $this->error('只有停车场人员可以执行该操作！');
        }
        // 接受参数
        if($request->isPost()) {
            // 车牌号
            $detained_number = $request->param('detained_number', '', 'trim');
            // 根据车牌号查找车辆
            $car = CarModel::get(['detained_number' => $detained_number]);
            if(is_null($car)) {
                return $this->error('改车辆不存在');
            }
            // 停车场判定
            if($car->detained_parking != $this->parking_id) {
                return $this->error('改车辆不属于你所在的停车场');
            }
            $apply_type = $request->param('action', '', 'trim');
            // 查找
            $data = [
                'apply_type' => $apply_type,
                'detained_id' => $car->detained_id,
                'apply_time' => time(),
                'apply_user_id' => $this->user->id,
                'apply_reason' => $request->param('apply_reason', '', 'trim'),
            ];
            // 数据验证
            $validate = new CarValidate();
            if(!$validate->scene('apply')->check($data)) {
                return $this->getError();
            }
            // 数据添加
            if(CarChange::create($data)) {
                // 更新车辆状态至待报废|待放行
                $car->detained_status = $this->actionStatus[$apply_type];
                if($car->save()) {
                    return $this->success('提交成功,待审核', 'parkingList');
                } else {
                    return $this->error('车辆状态更新失败');
                }
            }
        }
        return $this->error('提交方式为POST');
    }

    public function  releaseCar()
    {
        $this->view->assign('title','车场放车');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        return $this->view->fetch('release_car');

    }

    public function  discardedAdd()
    {
        $this->view->assign('title','报废处理');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        return $this->view->fetch('discarded_add');
    }
    /**
     * 综合查询
     */
    public function queryList()
    {
        $this -> view -> assign('title', '综合查询');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');
        // 暂扣单位赋值
        $squadron = Db::name('squadron')->select();
        $this->assign('squadron', $squadron);

        // 查询操作
        $map = [];
        $cars = null;
        $count = 0;
        $request = Request::instance()->get();
        if($this->user->role === '管理员' || count($request) == 0) {
            $cars = CarModel::all();
            $count = CarModel::count();
            echo '管理员登录';
        } else {
            // 构建查询数据
            if(count($request) > 0) {
                // 过滤$request
                // 1. -1的全部不要 没有文字的也不要
                // 2. 其他的加上百分号
                array_walk($request, function($value, $key)use(&$map){
                    // 判断是不是数字
                    if(is_numeric($value) && intval($value) != -1 && $key != 'detained_number') {
                        $map[$key] = intval($value);
                    } else {
                        if(trim($value) != '' && intval($value) != -1) {
                            $map[$key] = ['like', '%'.trim($value).'%'];
                        }
                    }
                });
            }
            $map['detained_parking'] = $this->parking_id;
            $cars = CarModel::all(function($query)use($map){
                $query->where($map);
            });
            $count = count($cars);
        }
        $this->assign('count', $count);
        $this->assign('cars', $cars);


        return $this -> view -> fetch('query_list');
    }
}
