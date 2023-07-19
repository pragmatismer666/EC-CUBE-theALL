/*
Navicat MySQL Data Transfer

Source Server         : ourserver
Source Server Version : 80021
Source Host           : 192.168.0.254:3306
Source Database       : db_malldevel

Target Server Type    : MYSQL
Target Server Version : 80021
File Encoding         : 65001

Date: 2021-03-08 14:36:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dtb_block
-- ----------------------------
DROP TABLE IF EXISTS `dtb_block`;
CREATE TABLE `dtb_block` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_type_id` smallint unsigned DEFAULT NULL,
  `block_name` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `use_controller` tinyint(1) NOT NULL DEFAULT '0',
  `deletable` tinyint(1) NOT NULL DEFAULT '1',
  `create_date` datetime NOT NULL COMMENT '(DC2Type:datetimetz)',
  `update_date` datetime NOT NULL COMMENT '(DC2Type:datetimetz)',
  `discriminator_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_type_id` (`device_type_id`,`file_name`),
  KEY `IDX_6B54DCBD4FFA550E` (`device_type_id`),
  CONSTRAINT `FK_6B54DCBD4FFA550E` FOREIGN KEY (`device_type_id`) REFERENCES `mtb_device_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of dtb_block
-- ----------------------------
INSERT INTO `dtb_block` VALUES ('1', '10', 'カート', 'cart', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('2', '10', 'カテゴリ', 'category', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('3', '10', 'カテゴリナビ(PC)', 'category_nav_pc', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('4', '10', 'カテゴリナビ(SP)', 'category_nav_sp', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('5', '10', '新入荷商品特集', 'eyecatch', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('6', '10', 'フッター', 'footer', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('7', '10', 'ヘッダー(商品検索・ログインナビ・カート)', 'header', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('8', '10', 'ログインナビ(共通)', 'login', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('9', '10', 'ログインナビ(SP)', 'login_sp', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('10', '10', 'ロゴ', 'logo', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('11', '10', '新着商品', 'new_item', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('12', '10', '新着情報', 'news', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('13', '10', '商品検索', 'search_product', '1', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('14', '10', 'トピック', 'topic', '0', '0', '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'block');
INSERT INTO `dtb_block` VALUES ('15', '10', 'PCナビゲーション', 'pc_navigation', '0', '1', '2021-01-27 07:16:25', '2021-01-27 07:16:25', 'block');
INSERT INTO `dtb_block` VALUES ('16', '10', 'PCアイコンナビゲーション', 'pc_icon_navigation', '0', '1', '2021-01-27 07:17:43', '2021-01-27 07:17:43', 'block');
INSERT INTO `dtb_block` VALUES ('17', '10', 'メインビジュアル', 'mainvisual', '0', '1', '2021-01-27 07:23:45', '2021-01-27 07:23:45', 'block');
INSERT INTO `dtb_block` VALUES ('18', '10', 'お知らせ', 'news_block', '0', '1', '2021-01-27 07:34:34', '2021-01-27 07:34:34', 'block');
INSERT INTO `dtb_block` VALUES ('19', '10', 'サイドメニュー（ショップ）', 'sidemenu_shop', '0', '1', '2021-01-27 07:38:22', '2021-01-27 07:38:22', 'block');
INSERT INTO `dtb_block` VALUES ('20', '10', 'トップページ特集', 'top_feature', '0', '1', '2021-01-27 07:41:35', '2021-01-27 07:41:35', 'block');
INSERT INTO `dtb_block` VALUES ('21', '10', 'トップページカテゴリー', 'top_category', '0', '1', '2021-01-27 07:43:35', '2021-01-27 07:43:35', 'block');
INSERT INTO `dtb_block` VALUES ('22', '10', 'SPナビゲーションメニュー', 'sp_navigation', '0', '1', '2021-01-27 07:49:59', '2021-01-27 07:49:59', 'block');
INSERT INTO `dtb_block` VALUES ('23', '10', 'サイドメニュー（特集）', 'sidemenu_feature', '0', '1', '2021-01-28 05:19:44', '2021-01-28 05:19:44', 'block');
INSERT INTO `dtb_block` VALUES ('24', '10', 'ガイド用サイドメニュー', 'sidebar_guide', '0', '1', '2021-01-28 05:49:01', '2021-01-28 05:49:01', 'block');
INSERT INTO `dtb_block` VALUES ('25', '10', 'ブログカテゴリー', 'sidebar_blogcat', '0', '1', '2021-01-28 07:11:52', '2021-01-28 07:11:52', 'block');
INSERT INTO `dtb_block` VALUES ('27', '10', 'パンくずプラグイン', 'BreadcrumbList4', '0', '1', '2021-01-29 02:17:06', '2021-01-29 02:17:06', 'block');
INSERT INTO `dtb_block` VALUES ('35', '10', 'ブログ一覧タイトル', 'block_list_title', '0', '1', '2021-02-17 18:59:57', '2021-02-17 18:59:57', 'block');
INSERT INTO `dtb_block` VALUES ('36', '10', 'Topバナー', 'top_banner', '0', '1', '2021-02-18 14:41:59', '2021-02-18 14:41:59', 'block');
INSERT INTO `dtb_block` VALUES ('37', '10', 'シリーズタイトル', 'series_title', '0', '1', '2021-02-19 15:58:46', '2021-02-19 15:58:46', 'block');
