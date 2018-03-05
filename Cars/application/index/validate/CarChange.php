<?php
namespace app\index\validate;

use think\Validate;

class Carchange extends Validate {
    // 验证规则
    protected $rule = [
        'apply_type' => 'require|checkApplyType',
        'detained_id' => 'number',
        'apply_time' => 'number',
        'apply_reason' => 'require',
        'aply_user_id' => 'number',
        'approval_time' => 'number',
        'approval_user_id' => 'number',

    ];
    // 错误信息
    protected $message = [
        'apply_type.checkApplyType' => '请求非法',
    ];
    // 验证场景
    protected $scene = [
        // 申请
        'apply' => ['apply_type', 'detained_id', 'apply_time', 'apply_reason', 'apply_user_id'],
        // 审核
        'approval' => ['detained_id', 'approval_time', 'approval_user_id']
    ];
    /**
     * 检查申请类型
     */
    protected function checkApplyType($value) {
        $type = ['release', 'scrap'];
        return in_array($value, $type) ? true : false;
    }
}