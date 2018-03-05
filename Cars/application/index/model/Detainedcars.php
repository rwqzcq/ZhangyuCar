<?php

namespace app\index\model;

use think\Model;
use app\index\model\CarChange;

class Detainedcars extends Model
{
    // 汽车类型
    protected $carType = [
        1 => '小型汽车',
        2 => '大型汽车',
        3 => '两轮摩托车',
        4 => '轻便摩托车'
    ];
    // 暂扣原因
    protected $detainedReason = [
        1 => '涉案',
        2 => '违章',
        3 => '事故',
        4 => '其他'
    ];
    // 车辆状态
    protected $detainedStatus = [
        '1' => '已入库',
        '0' => '已释放',
        '2' => '待释放',
        '3' => '待报废',
        '4' => '已报废',
    ];
    /**
     * 汽车类型拦截器
     */
    public function getDetainedTypeAttr($value) {
        return $this->carType[$value];
    }
    /**
     * 暂扣原因拦截器
     */
    public function getDetainedReasonAttr($value) {
        return $this->detainedReason[$value];
    }
    /**
     * 车辆状态拦截器
     */
    public function getDetainedStatusAttr($value) {
        return $this->detainedStatus[$value];
    }
    /**
     * 暂扣日期拦截器
     */
    public function setDetainedDateAttr($value) {
        return strtotime($value);
    }
    /**
     * 日期转化获取器 
     */
    public function getDetainedDateAttr($value) {
        return date('Y-m-d', $value);
    }

    /**
     * 建立停车场与车的关联模型
     */
    public function parking()
    {
        // 模型名 本模型的外键 关联模型的主键
        return $this->belongsTo('parking', 'detained_parking', 'id')->field('parking_name');
    }
    /**
     * 建立中队与车的关联模型
     */
    public function squadron()
    {
        return $this->belongsTo('squadron', 'detained_police', 'id')->field('squadron_name');
    }
    /**
     * 建立车与车事务申请的一对一关联
     */
    public function carChange()
    {
        return $this->hasOne('CarChange', 'detained_id', 'detained_id');
    }
    /**
     * 根据交警中队和申请的类型来返回相应的结果
     * @param $squadron_id int 交警大队id
     * @param $type 事务的类型 0表示放行 1表示报废
     */
    public function getDealBySquadron($squadron_id, $type = 'scrap')
    {
        if(!is_numeric($squadron_id)) {
            return [];
        }
        // 获取该警局之下的所有车辆
        $cars = $this->where(['detained_police' => $squadron_id])->select();
        $car_lists = [];
        foreach($cars as $car) {            
            $detained_id = $car->detained_id;
            $change = CarChange::get(['detained_id' => $detained_id, 'apply_type' => $type]); //获取关于该车的详细信息
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
            $apply['apply_user_name'] = $change->applyUser->getAttr('name'); // 申请人
            $item['apply'] = $apply;
            $car_lists[] = $item;
        } 
        return $car_lists; 
    }
    /**
     * 车场统计
     */
    public function staticCount($where = []) 
    {
       // $parking_group = $this->group('detained_parking')->select();
        $parking_group = $this->where($where)->distinct(true)->field('detained_parking')->select();
        if(is_null($parking_group)) {
            return [];
        }
        return $parking_group;
    }
}
