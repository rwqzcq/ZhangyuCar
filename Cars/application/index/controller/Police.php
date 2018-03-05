<?php
namespace app\index\controller;
use app\index\Controller\Base;
use think\Request;
use think\Db;
use app\index\validate\Detainedcars as CarValidate;
use app\index\model\Detainedcars as CarModel;
use app\index\Model\CarChange;

class Police extends Base
{
    protected $actionStatus = [
        'scrap' => 4, // 已报废
        'release' => 0, // 已放行
    ];
    protected $squadron_id = null;
    /**
     * 交警中队初始化
     */
    protected function _initialize()
    {
        parent::_initialize();  //继承父类中的初始化操作
        // 权限管理
        $limit = [
            0=>'管理员',
            2=> '交管中队'
        ];
        if(!in_array($this->user->role, $limit)) {
            return $this->error('只有交警中队的人才有权限查看此模块');
        }
        // 找到当前交警所属的中队
        $this->squadron_id = $this->user->squadronUser->squadron_id;
    }
  //添加操作的界面
    public function  carAdd()
    {
        $this->view->assign('title','录入车辆');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','PHP中文网ThinkPHP5开发实战课程');
        // 暂扣单位
        $squadron = Db::name('squadron')->select();
        $this->assign('squadron', $squadron);
        // 停车场
        $parking = Db::name('parking')->select();
        $this->assign('parking', $parking);      

        return $this->view->fetch('car_add');
    }
  // 处理添加逻辑
    public function create(Request $request) 
    {
        // 判断提交类型
        if($request->isPost()) {
            $car = [
                'detained_id' => intval($request->param('detained_id', '', 'trim')),
                'detained_date' => $request->param('detained_date', '', 'trim'),
                'detained_type' => intval($request->param('detained_type', '', 'trim')),
                'detained_number' => $request->param('detained_number', '', 'trim'),
                'detained_reason' => intval($request->param('detained_reason', '', 'trim')),
                'detained_police' => intval($request->param('detained_police', '', 'trim')),
                'detained_parking' => intval($request->param('detained_parking', '', 'trim')),
                'detained_man' => $request->param('detained_man', '', 'trim'),
                'detained_status' => 1,
            ];
            // 数据验证
            $validate = new CarValidate();
            if(!$validate->check($car)) {
               return $this->error($validate->getError());
            } else {
                // 数据写入
                if(CarModel::create($car)) {
                    return $this->success('添加成功', 'policeList');
                } else {
                    return $this->error('数据插入失败');
                }
            }
        }
        return $this->error('提交方式为post');
    }
    /**
     * 车辆列表
     */
    public function policeList()
    {
        $this -> view -> assign('title', '车辆列表');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');
        // 模板赋值
        $cars = CarModel::all();
        
        $this->assign('cars', $cars);
        return $this -> view -> fetch('police_list');
    }
    /**
     * 报废审核
     */
    public function discardedAudit()
    {
        $this -> view -> assign('title', '报废审核');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');
        $map = [];
        $map['detained_police'] = $this->squadron_id;
        $map['detained_status'] = 3; // 待报废状态
        // 查找
        $number = Request::instance()->get('detained_number');
        if($number != '') {
           $map['detained_number'] = ['like', '%'.$number.'%']; 
        }        
        // 获取该警局之下的所有车辆
        $cars = CarModel::all($map);
        $car_lists = [];
        foreach($cars as $car) {            
            $detained_id = $car->detained_id;
            $change = CarChange::get(['detained_id' => $detained_id, 'apply_type' => 1]); //获取关于该车的详细信息
            if(is_null($change)) { // 如果为空
                continue;
            }
            $item = [];
            $item['detained_id'] = $detained_id;
            $item['detained_number'] = $car->detained_number; // 车牌号
            $item['detained_type'] = $car->detained_type; // 车辆类型
            $item['detained_parking'] = $car->parking->parking_name; // 停放车场
            $apply = [];
            $apply['apply_time'] = $change->apply_time; // 申请时间 
            $apply['apply_reason'] = $change->apply_reason; // 报废原因
            $apply['apply_user_name'] = $change->applyUser->name; // 申请人
            $item['apply'] = $apply;
            $car_lists[] = $item;
        } 
        $this->assign('cars', $car_lists); 
        return $this -> view -> fetch('discarded_audit');
    }
    /**
     * 放车审核
     */
    public function releaseAudit()
    {
        $this -> view -> assign('title', '放车审核');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');

        $map = [];
        $map['detained_police'] = $this->squadron_id;
        $map['detained_status'] = 2; // 待放车状态
        // 查找
        $number = Request::instance()->get('detained_number');
        if($number != '') {
           $map['detained_number'] = ['like', '%'.$number.'%']; 
        }        
        // 获取该警局之下的所有车辆
        $cars = CarModel::all($map);
        $car_lists = [];
        foreach($cars as $car) {            
            $detained_id = $car->detained_id;
            $change = CarChange::get(['detained_id' => $detained_id, 'apply_type' => 0]); //获取关于该车的详细信息
            if(is_null($change)) { // 如果为空
                continue;
            }
            $item = [];
            $item['detained_id'] = $detained_id;
            $item['detained_number'] = $car->detained_number; // 车牌号
            $item['detained_type'] = $car->detained_type; // 车辆类型
            $item['detained_parking'] = $car->parking->parking_name; // 停放车场
            $apply = [];
            $apply['apply_time'] = $change->apply_time; // 申请时间 
            $apply['apply_reason'] = $change->apply_reason; // 报废原因
            $apply['apply_user_name'] = $change->applyUser->name; // 申请人
            $item['apply'] = $apply;
            $car_lists[] = $item;
        } 
        $this->assign('cars', $car_lists); 
        return $this -> view -> fetch('release_audit');
    }
    /**
     * 处理报废和放车
     */
    public function save(Request $request) {   
        // 只有交管人员可以执行该操作！
         if($this->user->role != '交管中队') {
             return $this->error('只有交管人员可以执行该操作！');
        }
        // 判断操作类型
        $action = $request->param('action', '', 'trim');
        if(!$action || !in_array($action, $this->mainAction)) {
            return $this->error('非法请求!');
        }
        // 获取id
        $detained_id = $request->param('detained_id', '', 'trim');
        if(is_numeric($detained_id)) {
            $detained_id = intval($detained_id);
            $map = [];
            $map['detained_id'] = $detained_id;
            // 查找该车辆是否存在
            $car = CarModel::get($detained_id);
            $car_change = CarChange::get($map);
            if(is_null($car) || is_null($car_change)) {
                return $this->error('车辆不存在');
            }
            // 更新车辆申请表
            $car_change->approval_time = time();
            $car_change->approval_user_id = $this->user->id;
            $car_change->save();
            // 车辆信息表更新
            $car_change->car->detained_status = $this->actionStatus[$action];
            $car_change->car->save();
            return $this->success('审核通过');
        } else {
            $this->error('非法传值');
        }
    }
    /**
     * 综合查询
     */
    public function queryList()
    {
        $this -> view -> assign('title', '综合查询');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');
        // 停车场赋值
        $squadron = Db::name('parking')->select();
        $this->assign('parking', $squadron);

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
            // 当前警局
            $map['detained_police'] = $this->squadron_id;
            $cars = CarModel::all(function($query)use($map){
                $query->where($map);
            });
            $count = count($cars);
        }
        $this->assign('count', $count);
        $this->assign('cars', $cars);

        return $this -> view -> fetch('query_list');
    }
    /**
     * 总和统计
     */
    public function statisticsList()
    {
        // 停车场赋值
        $squadron = Db::name('parking')->select();
        $this->assign('parking', $squadron);
        $this -> view -> assign('title', '综合统计');
        $this -> view -> assign('keywords', 'php中文网');
        $this -> view -> assign('desc', 'php中文网ThinkPHP5实战开发教学案例');
        // 查询操作
        $map = [];
        $cars = null;
        $count = 0;
        $request = Request::instance()->get();
        $where = [];
        if(count($request) > 0) {
            // 停车场
            if(isset($request['detained_parking']) && $request['detained_parking'] != -1) {
                $where['detained_parking'] = intval($request['detained_parking']);
            }      
            // 时间 某个时间段内进场与出厂的数量
            if(isset($request['start_date']) && $request['start_date'] != '') {
               // echo $request['start_date'];die;
                $start_date = strtotime($request['start_date']);
                // 入场时间要大于开始的时间
                $map['detained_date'] = ['gt', $start_date];
            }    
        }
        // 查询所有车场
        $car_model = new CarModel();
        $parkings = $car_model->staticCount($where);
        $parking_count = [];
        foreach($parkings as $parking) {
            $parking_id = $parking->detained_parking;
            $parking_name = $parking->parking->parking_name;
            $count = [];
            $map['detained_parking'] = $parking_id;
            // 进场车辆总数            
            $in_total = CarModel::where($map)->count();
            $count['in_total'] = $in_total;
            // 出厂数量总数 = 已报废+已释放
            $map['detained_status'] = ['in', [0, 4]];
            $outs = CarModel::where($map)->select(); // 计算总的出厂
            $out_total = count($outs);
            // 出厂车辆停放总停留天数与筛选
            $total_days = 0;
            $final_total = 0;
            // 需要减掉的车的数量
            $cut_days = 0;
            if($out_total > 0) {                
                if(isset($request['end_date']) && $request['end_date'] != '') {
                    $end_date = strtotime($request['end_date']);
                }
                foreach($outs as $out) { 
                    // 判断时间
                    if(isset($end_date) && is_numeric($end_date)) {
                        // 批准时间大于结束时间的时候这次循环终止
                        if($out->carChange->approval_time > $end_date) {
                            continue;
                        } 
                    } 
                    // 最终的数量+1 也就是满足条件的数量
                    $final_total++;  
                    // 计算天数             
                    $diff = $out->carChange->approval_time - strtotime($out->detained_date);
                    $diff_days = round($diff/(86400));
                    echo $diff_days.'<br>';
                    $total_days += $diff_days;
                }
            }
            $count['out_total'] = $final_total; // 总出厂数量             
            $count['still_total'] = $in_total - $final_total; // 停留车辆总数
            $count['out_total_days'] =  $total_days; // 出厂停放总天数
            $parking_count[$parking_name] = $count;            
        }
        // 车场名
        // 进场车辆总数 = 全部
        // 出厂车辆总数 =  已报废 +  已释放
        // 停留车辆总数  = 已入场
        // 车辆总停留数 = 
        // 出厂车辆停放总天数
        $this->view->assign('parking_count', $parking_count);
        $this->view->assign('count', count($parking_count));
        return $this -> view -> fetch('statistics_list');
    }
}
