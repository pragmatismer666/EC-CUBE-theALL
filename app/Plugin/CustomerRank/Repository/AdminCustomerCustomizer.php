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

namespace Plugin\CustomerRank\Repository;

use Eccube\Doctrine\Query\WhereClause;
use Eccube\Doctrine\Query\WhereCustomizer;
use Eccube\Repository\QueryKey;

class AdminCustomerCustomizer extends WhereCustomizer
{
    /**
     *
     * @param array $params
     * @param $queryKey
     *
     * @return WhereClause[]
     */
    protected function createStatements($params, $queryKey)
    {
        if(!empty($params['customer_rank']) && count($params['customer_rank']) > 0){
            return [WhereClause::in('c.CustomerRank', ':customer_rank_ids', ['customer_rank_ids' => $params['customer_rank']])];
        }
        return [];
    }
    /**
     *
     * @return string
     */
    public function getQueryKey()
    {
        return QueryKey::CUSTOMER_SEARCH;
    }
}