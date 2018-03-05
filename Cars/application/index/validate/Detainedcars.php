<?php
/*
 * 车辆信息验证器
 */
namespace app\index\validate;
use think\Validate;

class Detainedcars extends Validate 
{
    // 验证器
    protected $rule = [
        'detained_id' => 'require|number', // 暂扣单号 不为空 必须为数字 不能重复
        'detained_date' => 'require|checkDate', // 暂扣时间 不为空 时间戳
        'detained_type' => 'require|number', // 车辆类型
        'detained_number' => 'require', // 车牌号
        'detained_reason' => 'require|number', // 暂扣原因
        'detained_police' => 'require|number', // 经办民警
        'detained_parking' => 'require|number', // 停放停车场
        'detained_man' => 'require', // 经办民警
        'detained_status' => 'require|number', // 车辆状态
    ];
    protected $message = [
        'detained_date.checkDate' => '日期不能超过今天'
    ];
    // 验证场景
    // protected $scene = [
    //     'create' => ['detained_status'],
    // ];
    // 自定义验证规则
    protected function checkDate($date) {
        return strtotime($date) > time('+1 day') ? false : true;
    }
}