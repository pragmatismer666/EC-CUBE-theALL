/*
Navicat MySQL Data Transfer

Source Server         : ourserver
Source Server Version : 80021
Source Host           : 192.168.0.254:3306
Source Database       : db_malldevel

Target Server Type    : MYSQL
Target Server Version : 80021
File Encoding         : 65001

Date: 2021-03-08 14:36:49
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dtb_layout
-- ----------------------------
DROP TABLE IF EXISTS `dtb_layout`;
CREATE TABLE `dtb_layout` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_type_id` smallint unsigned DEFAULT NULL,
  `layout_name` varchar(255) DEFAULT NULL,
  `create_date` datetime NOT NULL COMMENT '(DC2Type:datetimetz)',
  `update_date` datetime NOT NULL COMMENT '(DC2Type:datetimetz)',
  `discriminator_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5A62AA7C4FFA550E` (`device_type_id`),
  CONSTRAINT `FK_5A62AA7C4FFA550E` FOREIGN KEY (`device_type_id`) REFERENCES `mtb_device_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of dtb_layout
-- ----------------------------
INSERT INTO `dtb_layout` VALUES ('1', '10', 'トップページ用レイアウト', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'layout');
INSERT INTO `dtb_layout` VALUES ('2', '10', '下層ページ用レイアウト', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'layout');
INSERT INTO `dtb_layout` VALUES ('3', '10', '下層ページサイドバーあり', '2021-01-27 08:31:17', '2021-01-27 08:31:17', 'layout');
INSERT INTO `dtb_layout` VALUES ('5', '10', '商品一覧ページ', '2021-01-28 05:21:53', '2021-01-28 05:21:53', 'layout');
INSERT INTO `dtb_layout` VALUES ('6', '10', 'ガイドページ', '2021-01-28 05:48:27', '2021-01-28 05:48:27', 'layout');
INSERT INTO `dtb_layout` VALUES ('7', '10', 'ブログ一覧', '2021-01-28 07:11:29', '2021-01-28 07:11:29', 'layout');
INSERT INTO `dtb_layout` VALUES ('10', '10', 'プレビュー用レイアウト', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'layout');
