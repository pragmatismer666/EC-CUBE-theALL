/*
Navicat MySQL Data Transfer

Source Server         : ourserver
Source Server Version : 80021
Source Host           : 192.168.0.254:3306
Source Database       : db_malldevel

Target Server Type    : MYSQL
Target Server Version : 80021
File Encoding         : 65001

Date: 2021-03-08 14:36:23
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dtb_block_position
-- ----------------------------
DROP TABLE IF EXISTS `dtb_block_position`;
CREATE TABLE `dtb_block_position` (
  `section` int unsigned NOT NULL,
  `block_id` int unsigned NOT NULL,
  `layout_id` int unsigned NOT NULL,
  `block_row` int unsigned DEFAULT NULL,
  `discriminator_type` varchar(255) NOT NULL,
  PRIMARY KEY (`section`,`block_id`,`layout_id`),
  KEY `IDX_35DCD731E9ED820C` (`block_id`),
  KEY `IDX_35DCD7318C22AA1A` (`layout_id`),
  CONSTRAINT `FK_35DCD7318C22AA1A` FOREIGN KEY (`layout_id`) REFERENCES `dtb_layout` (`id`),
  CONSTRAINT `FK_35DCD731E9ED820C` FOREIGN KEY (`block_id`) REFERENCES `dtb_block` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of dtb_block_position
-- ----------------------------
INSERT INTO `dtb_block_position` VALUES ('3', '10', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '10', '2', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '10', '3', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '10', '5', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '10', '6', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '10', '7', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '15', '1', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '15', '2', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '15', '3', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '15', '5', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '15', '6', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '15', '7', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '16', '1', '2', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '16', '2', '2', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '16', '3', '2', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '16', '5', '2', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '16', '6', '2', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('3', '16', '7', '2', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '17', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '18', '1', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '27', '2', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '27', '3', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '27', '5', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '27', '6', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '27', '7', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('4', '35', '7', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('5', '19', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('5', '19', '3', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('5', '19', '5', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('5', '23', '5', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('5', '24', '6', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('5', '25', '7', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('6', '36', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('7', '11', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('9', '20', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('9', '21', '1', '1', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('10', '6', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('10', '6', '2', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('10', '6', '3', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('10', '6', '5', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('10', '6', '6', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('10', '6', '7', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('11', '22', '1', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('11', '22', '2', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('11', '22', '3', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('11', '22', '5', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('11', '22', '6', '0', 'blockposition');
INSERT INTO `dtb_block_position` VALUES ('11', '22', '7', '0', 'blockposition');
