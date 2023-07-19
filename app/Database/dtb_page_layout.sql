/*
Navicat MySQL Data Transfer

Source Server         : ourserver
Source Server Version : 80021
Source Host           : 192.168.0.254:3306
Source Database       : db_malldevel

Target Server Type    : MYSQL
Target Server Version : 80021
File Encoding         : 65001

Date: 2021-03-08 14:36:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dtb_page_layout
-- ----------------------------
DROP TABLE IF EXISTS `dtb_page_layout`;
CREATE TABLE `dtb_page_layout` (
  `page_id` int unsigned NOT NULL,
  `layout_id` int unsigned NOT NULL,
  `sort_no` smallint unsigned NOT NULL,
  `discriminator_type` varchar(255) NOT NULL,
  PRIMARY KEY (`page_id`,`layout_id`),
  KEY `IDX_F2799941C4663E4` (`page_id`),
  KEY `IDX_F27999418C22AA1A` (`layout_id`),
  CONSTRAINT `FK_F27999418C22AA1A` FOREIGN KEY (`layout_id`) REFERENCES `dtb_layout` (`id`),
  CONSTRAINT `FK_F2799941C4663E4` FOREIGN KEY (`page_id`) REFERENCES `dtb_page` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of dtb_page_layout
-- ----------------------------
INSERT INTO `dtb_page_layout` VALUES ('1', '1', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('2', '5', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('3', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('4', '2', '6', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('5', '2', '7', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('6', '2', '8', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('7', '2', '36', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('8', '2', '37', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('9', '2', '9', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('10', '2', '10', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('11', '2', '11', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('12', '2', '12', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('13', '2', '14', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('14', '2', '13', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('15', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('16', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('17', '2', '17', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('18', '2', '18', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('19', '2', '33', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('20', '2', '19', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('21', '2', '20', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('22', '2', '21', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('23', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('24', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('25', '2', '34', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('28', '2', '23', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('29', '2', '24', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('30', '2', '25', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('31', '2', '26', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('32', '2', '27', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('33', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('34', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('35', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('36', '2', '31', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('37', '2', '32', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('38', '2', '39', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('42', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('44', '2', '40', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('45', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('46', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('47', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('48', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('49', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('50', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('51', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('52', '3', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('53', '6', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('54', '6', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('55', '6', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('56', '6', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('57', '6', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('59', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('60', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('61', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('62', '7', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('63', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('123', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('126', '7', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('127', '7', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('128', '7', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('129', '7', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('130', '7', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('131', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('133', '2', '0', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('134', '2', '0', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('135', '2', '0', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('136', '2', '0', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('137', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('138', '3', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('139', '3', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('140', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('141', '2', '41', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('142', '2', '0', 'pagelayout');
INSERT INTO `dtb_page_layout` VALUES ('143', '5', '41', 'pagelayout');
