<?php
namespace app\index\model;
use think\Model;

class User extends Model
{
  // 保存自动完成列表
  protected $auto = [
      'delete_time' => NULL,
      'is_delete' => 1, //1:允许删除 0:禁止删除
  ];
  // 新增自动完成列表
  protected $insert = [
      'login_time'=> '0', //新增时登录时间应该为NULL,因为刚创建
      'login_count' => 0, //原因同上,刚创建肯定没有登录过
      'delete_time' => '0',
  ];
  // 更新自动完成列表
  protected $update = [];
  // 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
  protected $autoWriteTimestamp = true; //自动写入
  // 创建时间字段
  protected $createTime = 'create_time';
  // 更新时间字段
  protected $updateTime = 'update_time';
  // 时间字段取出后的默认时间格式
  protected $dateFormat = 'Y年m月d日';

  //数据表中角色字段:role返回值处理
  public function getRoleAttr($value)
  {
      $role = [
         0=>'管理员',
         1=> '交管中队',
         2=> '停车场人员'
      ];
      return $role[$value];
  }

  //状态字段:status返回值处理
  public function getStatusAttr($value)
  {
      $status = [
          0=>'已停用',
          1=>'已启用'
      ];
      return $status[$value];
  }

  //密码修改器
  public function setPasswordAttr($value)
  {
      return md5($value);
  }

  //登录时间获取器
  public function getLoginTimeAttr($value)
  {
      return date('Y/m/d H:i', $value);
  }
  /**
   * 查看当前用户所在的停车场
   */
  public function parkingUser()
  {
      return $this->belongsTo('ParkingUser', 'id', 'user_id')->field('parking_id');
  }
  /**
   * 查看当前用户所在的交警中队
   */
  public function squadronUser()
  {
      return $this->belongsTo('SquadronUser', 'id', 'user_id')->field('squadron_id');
  }
}
