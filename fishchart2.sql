/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : fishchart2

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-09-18 11:03:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for fish_chart
-- ----------------------------
DROP TABLE IF EXISTS `fish_chart`;
CREATE TABLE `fish_chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '聊天内容表',
  `from_user_id` int(11) DEFAULT NULL COMMENT '发送者',
  `group_id` int(11) DEFAULT NULL COMMENT '群组id,此字段有值即为群组消息',
  `to_user_id` int(11) DEFAULT NULL COMMENT '接收者',
  `send_time` datetime DEFAULT NULL COMMENT '发送时间',
  `content` text COMMENT '内容',
  `state` tinyint(4) DEFAULT '0' COMMENT '状态：0成功，-1失败',
  `is_receive` tinyint(4) DEFAULT '0' COMMENT '是否已接收 0：未读， 1：已读',
  `type` char(10) DEFAULT 'text' COMMENT '信息类型 :text-文本消息（默认）,pic-图片，voice-语音,info-透传信息（不显示的系统消息，如对方删除好友）,sys-系统提示消息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_chart
-- ----------------------------
INSERT INTO `fish_chart` VALUES ('1', '1', null, '2', '2018-09-12 15:54:18', '1a', '0', '1', 'text');
INSERT INTO `fish_chart` VALUES ('2', '1', null, '2', '2018-09-12 15:54:21', '1b', '0', '1', 'text');
INSERT INTO `fish_chart` VALUES ('3', '2', null, '1', '2018-09-12 15:54:31', '2a', '0', '1', 'text');
INSERT INTO `fish_chart` VALUES ('4', '2', null, '1', '2018-09-12 15:54:34', '2b', '0', '1', 'text');
INSERT INTO `fish_chart` VALUES ('5', '1', null, '2', '2018-09-14 10:01:10', null, '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('6', '1', null, '2', '2018-09-14 10:03:10', null, '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('7', '1', null, '2', '2018-09-14 10:51:33', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('8', '1', null, '2', '2018-09-14 10:51:59', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('9', '1', null, '2', '2018-09-14 10:57:47', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('10', '1', null, '2', '2018-09-14 11:01:47', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('11', '1', null, '2', '2018-09-14 11:02:34', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('12', '1', null, '2', '2018-09-14 11:02:40', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('13', '1', null, '2', '2018-09-14 11:02:57', '', '0', '0', 'map');
INSERT INTO `fish_chart` VALUES ('14', '1', null, '2', '2018-09-14 11:52:29', '', '0', '0', 'map');

-- ----------------------------
-- Table structure for fish_group_chart
-- ----------------------------
DROP TABLE IF EXISTS `fish_group_chart`;
CREATE TABLE `fish_group_chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '群组消息/暂不使用',
  `group_id` int(11) DEFAULT NULL COMMENT '群组id',
  `tousers` varchar(255) DEFAULT NULL COMMENT '分组用户id，逗号分隔',
  `form_user_id` int(11) DEFAULT NULL COMMENT '发送用户id',
  `send_time` datetime DEFAULT NULL,
  `content` text COMMENT '信息内容',
  `state` tinyint(4) DEFAULT NULL COMMENT '发送状态：0成功  -1失败',
  `type` char(10) DEFAULT 'text' COMMENT '信息类型 :text-文本消息（默认）,pic-图片，voice-语音,info-透传信息（不显示的系统消息，如对方删除好友）,sys-系统提示消息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_group_chart
-- ----------------------------

-- ----------------------------
-- Table structure for fish_site_message
-- ----------------------------
DROP TABLE IF EXISTS `fish_site_message`;
CREATE TABLE `fish_site_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '站内信',
  `title` varchar(80) DEFAULT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `send_time` datetime DEFAULT NULL COMMENT '发送时间',
  `is_read` tinyint(4) DEFAULT '0' COMMENT '是否已读：0未读 1已读',
  `admin_id` int(11) DEFAULT '0' COMMENT '管理员id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `state` tinyint(4) DEFAULT '0' COMMENT '状态：0：正常 -1：删除',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_site_message
-- ----------------------------
INSERT INTO `fish_site_message` VALUES ('1', '标题', '测试内容', '2018-02-07 00:00:00', '1', null, '1', '1');

-- ----------------------------
-- Table structure for fish_uclass
-- ----------------------------
DROP TABLE IF EXISTS `fish_uclass`;
CREATE TABLE `fish_uclass` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '好友分类',
  `name` varchar(30) DEFAULT NULL COMMENT '分类标题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_uclass
-- ----------------------------

-- ----------------------------
-- Table structure for fish_ugroup
-- ----------------------------
DROP TABLE IF EXISTS `fish_ugroup`;
CREATE TABLE `fish_ugroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '群组',
  `name` varchar(30) DEFAULT NULL COMMENT '群组名称',
  `description` varchar(255) DEFAULT NULL COMMENT '备注消息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_ugroup
-- ----------------------------

-- ----------------------------
-- Table structure for fish_user
-- ----------------------------
DROP TABLE IF EXISTS `fish_user`;
CREATE TABLE `fish_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户',
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `nickname` varchar(30) DEFAULT NULL,
  `gender` char(2) DEFAULT NULL COMMENT '性别',
  `signature` varchar(200) DEFAULT NULL COMMENT '个性签名',
  `photo` varchar(255) DEFAULT NULL COMMENT '头像',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_user
-- ----------------------------

-- ----------------------------
-- Table structure for fish_user_uclass
-- ----------------------------
DROP TABLE IF EXISTS `fish_user_uclass`;
CREATE TABLE `fish_user_uclass` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户分类关系表',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `uclass_id` int(11) DEFAULT NULL COMMENT '用户分类id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_user_uclass
-- ----------------------------

-- ----------------------------
-- Table structure for fish_user_ugroup
-- ----------------------------
DROP TABLE IF EXISTS `fish_user_ugroup`;
CREATE TABLE `fish_user_ugroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户群组关系',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `ugroup_id` int(11) NOT NULL COMMENT '群组id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of fish_user_ugroup
-- ----------------------------
