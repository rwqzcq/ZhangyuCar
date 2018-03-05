/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : vehicle_system

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2018-03-01 10:25:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `car_change`
-- ----------------------------
DROP TABLE IF EXISTS `car_change`;
CREATE TABLE `car_change` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '事务id',
  `detained_id` int(11) NOT NULL COMMENT '车辆id',
  `apply_time` int(11) NOT NULL COMMENT '申请时间',
  `apply_reason` varchar(256) NOT NULL COMMENT '申请理由',
  `apply_user_id` int(11) NOT NULL COMMENT '申请人',
  `apply_type` tinyint(4) NOT NULL COMMENT '申请事宜 1代表报废申请 0代表放车申请',
  `approval_time` int(11) DEFAULT NULL COMMENT '批准时间',
  `approval_user_id` int(11) DEFAULT NULL COMMENT '批准人id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of car_change
-- ----------------------------
INSERT INTO `car_change` VALUES ('1', '4544', '1519804523', '时间长了', '20001', '1', '1519822477', '10001');
INSERT INTO `car_change` VALUES ('2', '2451489', '1519808380', '表现良好', '20001', '0', '1519822969', '10001');
INSERT INTO `car_change` VALUES ('3', '5488', '1519867476', '发动机坏了', '20001', '1', '1519867541', '10001');
INSERT INTO `car_change` VALUES ('4', '4321', '1519867728', '交了罚款', '20001', '0', '1519867766', '10001');

-- ----------------------------
-- Table structure for `detainedcars`
-- ----------------------------
DROP TABLE IF EXISTS `detainedcars`;
CREATE TABLE `detainedcars` (
  `detained_id` int(50) NOT NULL COMMENT '暂扣单号',
  `detained_date` int(50) NOT NULL COMMENT '暂扣日期',
  `detained_type` int(50) NOT NULL COMMENT '车辆类型',
  `detained_number` varchar(50) NOT NULL COMMENT '车牌号',
  `detained_reason` int(50) NOT NULL COMMENT '暂扣原因',
  `detained_police` int(50) NOT NULL COMMENT '暂扣单位',
  `detained_parking` int(50) NOT NULL COMMENT '停放车场',
  `detained_man` varchar(50) NOT NULL COMMENT '经办民警',
  `detained_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '车辆状态表 1表示已入库 0表示已经释放 2表示待释放 3代表待报废 4代表已经报废',
  PRIMARY KEY (`detained_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of detainedcars
-- ----------------------------
INSERT INTO `detainedcars` VALUES ('2451489', '1515400101', '1', '闽A535241', '1', '1', '1', '小刘', '0');
INSERT INTO `detainedcars` VALUES ('2147483647', '1544976000', '2', '冀E2L989', '2', '1', '1', '老王', '1');
INSERT INTO `detainedcars` VALUES ('4544', '1519747200', '2', '冀32L989', '2', '1', '1', '小王', '4');
INSERT INTO `detainedcars` VALUES ('5488', '1519747200', '3', '冀EM817', '2', '1', '1', '陈奕迅', '4');
INSERT INTO `detainedcars` VALUES ('4321', '1518710400', '4', '冀E6M817', '3', '1', '1', '周杰伦', '0');

-- ----------------------------
-- Table structure for `parking`
-- ----------------------------
DROP TABLE IF EXISTS `parking`;
CREATE TABLE `parking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parking_name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of parking
-- ----------------------------
INSERT INTO `parking` VALUES ('1', '1号停车场');
INSERT INTO `parking` VALUES ('2', '2号停车场');
INSERT INTO `parking` VALUES ('3', '3号停车场');

-- ----------------------------
-- Table structure for `parking_user`
-- ----------------------------
DROP TABLE IF EXISTS `parking_user`;
CREATE TABLE `parking_user` (
  `parking_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`parking_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of parking_user
-- ----------------------------
INSERT INTO `parking_user` VALUES ('1', '1');
INSERT INTO `parking_user` VALUES ('1', '10001');
INSERT INTO `parking_user` VALUES ('1', '20001');

-- ----------------------------
-- Table structure for `squadron`
-- ----------------------------
DROP TABLE IF EXISTS `squadron`;
CREATE TABLE `squadron` (
  `id` int(11) NOT NULL,
  `squadron_name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of squadron
-- ----------------------------
INSERT INTO `squadron` VALUES ('1', '1号中队');
INSERT INTO `squadron` VALUES ('2', '2号中队');
INSERT INTO `squadron` VALUES ('3', '3号中队');

-- ----------------------------
-- Table structure for `squadron_user`
-- ----------------------------
DROP TABLE IF EXISTS `squadron_user`;
CREATE TABLE `squadron_user` (
  `squadron_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`squadron_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of squadron_user
-- ----------------------------
INSERT INTO `squadron_user` VALUES ('1', '10001');
INSERT INTO `squadron_user` VALUES ('1', '10002');

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `name` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '用户密码',
  `email` varchar(255) NOT NULL COMMENT '用户邮箱',
  `role` tinyint(2) unsigned NOT NULL COMMENT '角色',
  `status` int(2) unsigned NOT NULL COMMENT '状态.1启用0禁用',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) NOT NULL COMMENT '删除时间',
  `login_time` int(11) unsigned NOT NULL COMMENT '登录时间',
  `login_count` int(11) unsigned NOT NULL COMMENT '登录次数',
  `is_delete` int(2) unsigned NOT NULL COMMENT '是否删除.1是0否',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20003 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@qq.com', '0', '1', '1515398142', '1519870931', '0', '1519870931', '26', '1');
INSERT INTO `user` VALUES ('10001', '张三', '01d7f40760960e7bd9443513f22ab9af', 'zhangsan@qq.com', '1', '1', '1514446059', '1519869746', '0', '1519869746', '8', '1');
INSERT INTO `user` VALUES ('20001', '王五', '9f001e4166cf26bfbdd3b4f67d9ef617', 'wangwu@qq.com', '2', '1', '1519624155', '1519867738', '0', '1519867738', '3', '1');
INSERT INTO `user` VALUES ('10002', '李四', 'dc3a8f1670d65bea69b7b65048a0ac40', 'lisi@qq.com', '1', '1', '1515398304', '1519789934', '0', '1519789934', '1', '1');
INSERT INTO `user` VALUES ('20002', 'jiaojing2', 'adbe89762c84edeefdfa3cb3751d541f', '1987@qq.com', '1', '1', '1519870907', '1519870907', '0', '0', '1', '1');
