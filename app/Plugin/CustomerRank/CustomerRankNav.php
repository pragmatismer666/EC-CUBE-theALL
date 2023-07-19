<?php
/*
* Plugin Name : CustomerRank
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\CustomerRank;

use Eccube\Common\EccubeNav;

class CustomerRankNav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        return [
            'customer' => [
                'children' => [
                    'rank' => [
                        'id' => 'admin_customer_rank',
                        'name' => 'customerrank.admin.nav.customer.rank',
                        'url' => 'admin_customer_rank',
                    ],
                    'rank_import_csv' => [
                        'id' => 'admin_customer_rank_csv_import',
                        'name' => 'customerrank.admin.nav.customer.rank_import_csv',
                        'url' => 'admin_customer_rank_csv_import',
                    ],
                ],
            ],
            'content' => [
                'children' => [
                    'customerrank' => [
                        'id' => 'admin_content_customerrank',
                        'name' => 'customerrank.admin.nav.content.customerrank',
                        'children' => [
                            'list' => [
                                'id' => 'admin_content_customerrank_list',
                                'name' => 'customerrank.admin.nav.content.customerrank.list',
                                'url' => 'admin_content_customerrank_list',
                            ],
                            'detail' => [
                                'id' => 'admin_content_customerrank_detail',
                                'name' => 'customerrank.admin.nav.content.customerrank.detail',
                                'url' => 'admin_content_customerrank_detail',
                            ]
                        ]
                    ],
                ],
            ],
            'setting' => [
                'children' => [
                    'rank' => [
                        'id' => 'admin_setting_customerrank',
                        'name' => 'customerrank.admin.nav.setting.rank',
                        'url' => 'admin_setting_customerrank',
                    ],
                ],
            ],
        ];
    }
}