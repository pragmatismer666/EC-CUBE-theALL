<?php

namespace Plugin\CustomerBan;

use Eccube\Common\EccubeNav;

class CustomerBanNav implements EccubeNav
{
    public static function getNav()
    {
        return [
            'customer' => [
                'children' => [
                    'ban' => [
                        'name' => 'customer_ban.admin.master.title',
                        'url' => 'customer_ban_admin_customer_ban'
                    ]
                ],
            ]
        ];
    }
}
