<?php

namespace Customize;

use Eccube\Common\EccubeNav;

class MalldevelNav implements EccubeNav{

    public static function getNav(){
        return [
            'mshop' =>  [
                'name'  =>  'malldevel.admin.shop.label',
                'icon'  =>  'fas fa-store',
                'children'  =>  [
                    'list'  =>  [
                        'name'  =>  'malldevel.admin.shop.list',
                        'url'   =>  'malldevel_admin_shop_list',
                    ],
                    'create'  =>  [
                        'name'  =>  'malldevel.admin.shop.create',
                        'url'   =>  'malldevel_admin_shop_new',
                    ],
                    'apply_list' => [
                        'name'  =>  'malldevel.admin.apply.list',
                        'url'   =>  'malldevel_admin_apply_list',
                    ]
                ]
            ],
            'content' => [
                'children' => [
                    'notice' => [
                        'name' => 'malldevel.admin.content.notice',
                        'children' => [
                            'tag' => [
                                'name' => 'malldevel.admin.content.tag',
                                'url' => 'malldevel_admin_content_tag_notice'
                            ],
                            'category' => [
                                'name' => 'malldevel.admin.content.category',
                                'url' => 'malldevel_admin_content_category_notice'
                            ],
                            'master' => [
                                'name' => 'malldevel.admin.content.blog_list__notice',
                                'url' => 'malldevel_admin_content_blog_notice'
                            ],
                            'edit' => [
                                'name' => 'malldevel.admin.content.blog_registration__notice',
                                'url' => 'malldevel_admin_content_blog_new_notice'
                            ],
                        ],
                    ],
                    'info_site' => [
                        'name' => 'malldevel.admin.content.info_site',
                        'children' => [
                            'tag' => [
                                'name' => 'malldevel.admin.content.tag',
                                'url' => 'malldevel_admin_content_tag_info_site'
                            ],
                            'category'=> [
                                'name' => 'malldevel.admin.content.category',
                                'url' => 'malldevel_admin_content_category_info_site'
                            ],
                            'master' => [
                                'name' => 'malldevel.admin.content.blog_list__info_site',
                                'url' => 'malldevel_admin_content_blog_info_site'
                            ],
                            'edit' => [
                                'name' => 'malldevel.admin.content.blog_registration__info_site',
                                'url' => 'malldevel_admin_content_blog_new_info_site',
                            ]
                        ],
                    ],
                    'shop_blog' => [
                        'name' => 'malldevel.admin.content.shop_blog',
                        'url' => 'malldevel_admin_shop_blog'
                    ],
                    'feature'   => [
                        'name'  =>  'malldevel.common.feature',
                        'url'   =>  'malldevel_admin_feature'
                    ]
                ]
            ],
            'series'    =>  [
                'name'  =>  'malldevel.admin.series',
                'icon'  =>  'fas fa-tag',
                'children'  =>  [
                    'list'  =>  [
                        'name'  =>  'malldevel.admin.series.list',
                        'url'   =>  'malldevel_admin_series_list',
                    ],
                ]
            ],
            'stripe'    =>  [
                'name'  =>  'malldevel.admin.stripe',
                'icon'  =>  'fa-cc-stripe',
                'children'  =>  [
                    'stripe_config' =>  [
                        'name'  =>  'malldevel.admin.stripe.config',
                        'url'   =>  'malldevel_admin_stripe_config'
                    ]
                ]
            ]
        ];
    }
}
