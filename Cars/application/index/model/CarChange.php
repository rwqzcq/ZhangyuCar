<?php

namespace app\index\model;

use think\Model;

class CarChange extends Model
{
    protected $applyType = [
        'release' => 0, // 放行
        'scrap' => 1, // 报废
    ];
    /**
     * 申请类型拦截器
     */
    public function setApplyTypeAttr($value) {
        return $this->applyType[$value];
    }
    /**
     * 申请时间拦截器
     */
    public function getApplyTimeAttr($value) {
        return date('Y-m-d', $value);
    }

     /**
      * 建立车处理事务与车的一对一
      */
    public function car()
    {
        return $this->hasOne('Detainedcars', 'detained_id', 'detained_id');
    }
    /**
     * 建立车处理事务与申请人的一对多关联
     */
    public function applyUser()
    {
        return $this->belongsTo('User', 'apply_user_id', 'id');
    }
    /**
     * 建立车处理事务与处理人的一对多关联
     */
    public function approvalUser()
    {
        return $this->belongsTo('User', 'approval_user_id', 'id');
    }
    /**
     * 根据类型来查看相关的汽车参数
     * @param $type int 表示类型 1位报废 0为释放
     * @param $squadron_id int 表示交警中队的id
     */
    public function getCarInfoByType($type = 1, $squadron_id) {
        $car_lists = [];
        $changes = $this->where(['apply_type' => $type])->select();
        foreach($changes as $change){
            if($change->car->detained_police == $squadron_id) {
                $car = $change->car;
                $item = [];
                $item['detained_id'] = $change->detained_id;
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
        }
        return $car_lists;
    }
}
