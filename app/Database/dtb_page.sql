/*
Navicat MySQL Data Transfer

Source Server         : ourserver
Source Server Version : 80021
Source Host           : 192.168.0.254:3306
Source Database       : db_malldevel

Target Server Type    : MYSQL
Target Server Version : 80021
File Encoding         : 65001

Date: 2021-03-08 14:36:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dtb_page
-- ----------------------------
DROP TABLE IF EXISTS `dtb_page`;
CREATE TABLE `dtb_page` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `master_page_id` int unsigned DEFAULT NULL,
  `page_name` varchar(255) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `edit_type` smallint unsigned NOT NULL DEFAULT '1',
  `author` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `keyword` varchar(255) DEFAULT NULL,
  `create_date` datetime NOT NULL COMMENT '(DC2Type:datetimetz)',
  `update_date` datetime NOT NULL COMMENT '(DC2Type:datetimetz)',
  `meta_robots` varchar(255) DEFAULT NULL,
  `meta_tags` varchar(4000) DEFAULT NULL,
  `discriminator_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E3951A67D0618E8C` (`master_page_id`),
  KEY `dtb_page_url_idx` (`url`),
  CONSTRAINT `FK_E3951A67D0618E8C` FOREIGN KEY (`master_page_id`) REFERENCES `dtb_page` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of dtb_page
-- ----------------------------
INSERT INTO `dtb_page` VALUES ('1', null, 'TOPページ', 'homepage', 'index', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('2', null, '商品一覧ページ', 'product_list', 'Product/list', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('3', null, '商品詳細ページ', 'product_detail', 'Product/detail', '2', null, null, null, '2017-03-07 10:14:52', '2021-01-29 02:52:24', null, '<meta property=\"og:type\" content=\"og:product\" /><meta property=\"og:title\" content=\"{{ Product.name }}\" />\r\n<meta property=\"og:image\" content=\"{{ url(\'homepage\') }}{{ asset(Product.main_list_image|no_image_product, \'save_image\') }}\" />\r\n<meta property=\"og:description\" content=\"{{ Product.description_list|striptags }}\" />\r\n<meta property=\"og:url\" content=\"{{ url(\'product_detail\', {\'id\': Product.id}) }}\" />\r\n<meta property=\"product:price:amount\" content=\"{{ Product.getPrice02IncTaxMin }}\"/>\r\n<meta property=\"product:price:currency\" content=\"{{ eccube_config.currency }}\"/>\r\n<meta property=\"product:product_link\" content=\"{{ url(\'product_detail\', {\'id\': Product.id}) }}\"/>\r\n<meta property=\"product:retailer_title\" content=\"{{ BaseInfo.shop_name }}\"/>', 'page');
INSERT INTO `dtb_page` VALUES ('4', null, 'MYページ', 'mypage', 'Mypage/index', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('5', null, 'MYページ/会員登録内容変更(入力ページ)', 'mypage_change', 'Mypage/change', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('6', null, 'MYページ/会員登録内容変更(完了ページ)', 'mypage_change_complete', 'Mypage/change_complete', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('7', null, 'MYページ/お届け先一覧', 'mypage_delivery', 'Mypage/delivery', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('8', null, 'MYページ/お届け先追加', 'mypage_delivery_new', 'Mypage/delivery_edit', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('9', null, 'MYページ/お気に入り一覧', 'mypage_favorite', 'Mypage/favorite', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('10', null, 'MYページ/購入履歴詳細', 'mypage_history', 'Mypage/history', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('11', null, 'MYページ/ログイン', 'mypage_login', 'Mypage/login', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('12', null, 'MYページ/退会手続き(入力ページ)', 'mypage_withdraw', 'Mypage/withdraw', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('13', null, 'MYページ/退会手続き(完了ページ)', 'mypage_withdraw_complete', 'Mypage/withdraw_complete', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('14', null, '当サイトについて', 'help_about', 'Help/about', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('15', null, '現在のカゴの中', 'cart', 'Cart/index', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('16', null, 'お問い合わせ(入力ページ)', 'contact', 'Contact/index', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('17', null, 'お問い合わせ(完了ページ)', 'contact_complete', 'Contact/complete', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('18', null, '会員登録(入力ページ)', 'entry', 'Entry/index', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('19', null, 'ご利用規約', 'help_agreement', 'Help/agreement', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('20', null, '会員登録(完了ページ)', 'entry_complete', 'Entry/complete', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('21', null, '特定商取引に関する法律に基づく表記', 'help_tradelaw', 'Help/tradelaw', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('22', null, '本会員登録(完了ページ)', 'entry_activate', 'Entry/activate', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('23', null, '商品購入', 'shopping', 'Shopping/index', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('24', null, '商品購入/お届け先の指定', 'shopping_shipping', 'Shopping/shipping', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('25', null, '商品購入/お届け先の複数指定', 'shopping_shipping_multiple', 'Shopping/shipping_multiple', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('28', null, '商品購入/ご注文完了', 'shopping_complete', 'Shopping/complete', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('29', null, 'プライバシーポリシー', 'help_privacy', 'Help/privacy', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('30', null, '商品購入ログイン', 'shopping_login', 'Shopping/login', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('31', null, '非会員購入情報入力', 'shopping_nonmember', 'Shopping/nonmember', '2', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('32', null, '商品購入/お届け先の追加', 'shopping_shipping_edit', 'Shopping/shipping_edit', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:02', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('33', null, '商品購入/お届け先の複数指定(お届け先の追加)', 'shopping_shipping_multiple_edit', 'Shopping/shipping_multiple_edit', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:02', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('34', null, '商品購入/購入エラー', 'shopping_error', 'Shopping/shopping_error', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:02', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('35', null, 'ご利用ガイド', 'help_guide', 'Help/guide', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:02', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('36', null, 'パスワード再発行(入力ページ)', 'forgot', 'Forgot/index', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:02', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('37', null, 'パスワード再発行(完了ページ)', 'forgot_complete', 'Forgot/complete', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:02', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('38', null, 'パスワード再発行(再設定ページ)', 'forgot_reset', 'Forgot/reset', '2', null, null, null, '2017-03-07 01:15:02', '2017-03-07 01:15:05', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('42', null, '商品購入/遷移', 'shopping_redirect_to', 'Shopping/index', '2', null, null, null, '2017-03-07 01:15:03', '2017-03-07 01:15:03', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('44', '8', 'MYページ/お届け先編集', 'mypage_delivery_edit', 'Mypage/delivery_edit', '2', null, null, null, '2017-03-07 01:15:05', '2017-03-07 01:15:05', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('45', null, '商品購入/ご注文確認', 'shopping_confirm', 'Shopping/confirm', '2', null, null, null, '2017-03-07 01:15:03', '2017-03-07 01:15:03', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('46', null, '会社概要', 'malldevel_front_company', 'Help/company', '2', null, null, null, '2021-01-27 07:58:42', '2021-01-27 07:58:42', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('47', null, '小売店・製造販売業者の皆様へ', 'malldevel_front_dealer', 'Help/dealer', '2', null, null, null, '2021-01-27 08:16:16', '2021-01-27 08:16:16', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('48', null, 'ジオールモール利用規約', 'malldevel_front_terms', 'Help/terms', '2', null, null, null, '2021-01-27 08:17:43', '2021-01-27 08:17:43', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('49', null, 'ＴＳＰＣ　ID規約', 'malldevel_front_id_term', 'Help/id-term', '2', null, null, null, '2021-01-27 08:18:40', '2021-01-27 08:18:40', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('50', null, 'プライバシーポリシー', 'malldevel_front_privacy', 'Help/privacy', '2', null, null, null, '2021-01-27 08:19:28', '2021-01-27 08:19:28', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('51', null, '特定商取引法に基づく表示について', 'malldevel_front_tokusho', 'Help/tokusho', '2', null, null, null, '2021-01-27 08:27:29', '2021-01-27 08:27:29', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('52', null, 'THE ALLについて', 'shopdetail', 'shopdetail', '0', null, null, null, '2021-01-27 08:33:17', '2021-01-27 08:33:17', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('53', null, '注文の流れ', 'flow', 'flow', '0', null, null, null, '2021-01-28 05:50:15', '2021-01-28 05:50:15', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('54', null, '会員登録について', 'register', 'register', '0', null, null, null, '2021-01-28 05:55:09', '2021-01-28 05:55:09', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('55', null, '支払方法について', 'payment', 'payment', '0', null, null, null, '2021-01-28 05:55:49', '2021-01-28 05:55:49', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('56', null, '送料・配送について', 'shipping', 'shipping', '0', null, null, null, '2021-01-28 05:56:37', '2021-01-28 05:56:37', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('57', null, '注文のキャンセル、返品、交換', 'return', 'return', '0', null, null, null, '2021-01-28 05:57:28', '2021-01-28 05:57:28', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('59', null, 'ショップ一覧', 'malldevel_front_shop_list', 'Shop/list', '2', null, null, null, '2021-01-28 06:25:23', '2021-01-28 06:25:23', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('60', null, 'ショップ詳細', 'malldevel_front_shop_detail', 'Shop/detail', '2', null, null, null, '2021-01-28 06:26:31', '2021-01-28 06:26:31', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('61', null, 'ショップブログ詳細', 'malldevel_front_shop_blog', 'ShopBlog/view', '2', null, null, null, '2021-01-28 07:09:13', '2021-01-28 07:10:04', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('62', null, 'お知らせ', 'malldevel_front_blog_notice', 'Blog/list', '2', null, null, null, '2021-01-28 07:10:49', '2021-02-17 18:35:36', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('63', null, 'ブログ詳細', 'malldevel_front_blog_view', 'Blog/view', '2', null, null, null, '2021-01-28 07:13:23', '2021-01-28 07:13:23', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('122', null, 'プレビューデータ', 'preview', null, '1', null, null, null, '2017-03-07 10:14:52', '2017-03-07 10:14:52', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('123', null, 'shopowner', 'malldevel_shop_register', 'Shop/register', '2', null, null, null, '2021-02-17 08:32:15', '2021-02-17 08:32:15', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('126', null, '情報発信サイト', 'malldevel_front_blog_info_site', 'Blog/list', '2', null, null, null, '2021-02-18 02:47:59', '2021-02-18 02:47:59', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('127', null, 'お知らせカテゴリー', 'malldevel_front_blog_category_notice', 'Blog/list', '2', null, null, null, '2021-02-18 02:49:07', '2021-02-18 02:49:07', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('128', null, '情報発信サイトカテゴリー', 'malldevel_front_blog_category_info_site', 'Blog/list', '2', null, null, null, '2021-02-18 02:49:48', '2021-02-18 02:49:48', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('129', null, 'お知らせタグ', 'malldevel_front_blog_tag_notice', 'Blog/list', '2', null, null, null, '2021-02-18 02:50:45', '2021-02-18 02:50:45', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('130', null, '情報発信サイトタグ', 'malldevel_front_blog_tag_info_site', 'Blog/list', '2', null, null, null, '2021-02-18 02:51:55', '2021-02-18 02:51:55', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('131', null, 'カテゴリー一覧', 'malldevel_front_category', 'Category/list', '2', null, null, null, '2021-02-19 05:47:26', '2021-02-19 05:47:26', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('133', null, 'レビューを表示', 'product_review_display', 'ProductReview4/Resource/template/default/review', '2', null, null, null, '2021-02-19 06:45:47', '2021-02-19 06:45:47', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('134', null, 'レビューを投稿', 'product_review_index', 'ProductReview4/Resource/template/default/index', '2', null, null, null, '2021-02-19 06:45:47', '2021-02-19 06:45:47', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('135', null, 'レビューを投稿(確認)', 'product_review_confirm', 'ProductReview4/Resource/template/default/confirm', '2', null, null, null, '2021-02-19 06:45:47', '2021-02-19 06:45:47', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('136', null, 'レビューを投稿(完了)', 'product_review_complete', 'ProductReview4/Resource/template/default/complete', '2', null, null, null, '2021-02-19 06:45:47', '2021-02-19 06:45:47', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('137', null, 'Shop Tokusho', 'malldevel_shop_transaction_law', 'Shop/tokusho', '2', null, null, null, '2021-02-19 10:17:42', '2021-02-19 10:17:42', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('138', null, 'シリーズ一覧', 'malldevel_front_series_list', 'Series/list', '2', null, null, null, '2021-02-19 15:45:23', '2021-02-19 16:02:15', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('139', null, 'シリーズ詳細', 'malldevel_front_series_detail', 'Series/detail', '2', null, null, null, '2021-02-19 18:21:39', '2021-02-19 18:21:39', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('140', null, 'shop register return', 'malldevel_shop_register_return', 'Shop/register_return', '2', null, null, null, '2021-02-19 21:38:21', '2021-02-19 21:38:21', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('141', null, 'Credit Card Payment', 'shopping_credit_card', 'Shopping/credit_card', '2', null, null, null, '2021-03-02 18:31:57', '2021-03-02 18:31:57', null, null, 'page');
INSERT INTO `dtb_page` VALUES ('142', null, '商品購入/クーポン利用', 'plugin_coupon_shopping', 'Coupon4/Resource/template/default/shopping_coupon', '2', null, null, null, '2021-03-04 13:17:05', '2021-03-04 13:17:05', 'noindex', null, 'page');
INSERT INTO `dtb_page` VALUES ('143', null, '特集ページ', 'malldevel_front_feature_detail', 'Feature/detail', '2', null, null, null, '2021-03-05 06:53:52', '2021-03-05 06:53:52', null, null, 'page');
