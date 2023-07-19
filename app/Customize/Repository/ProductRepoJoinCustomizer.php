<?php

namespace Customize\Repository;

use Eccube\Doctrine\Query\JoinClause;
use Eccube\Doctrine\Query\WhereClause;
use Eccube\Doctrine\Query\JoinCustomizer;
use Eccube\Repository\QueryKey;

class ProductRepoJoinCustomizer extends JoinCustomizer
{
    /**
     * @param array $params
     * @param $queryKey
     *
     * @return JoinClause[]
     */
    public function createStatements($params, $queryKey)
    {
        if (isset($params['series_id'])) {
            
            return [
                JoinClause::leftJoin('p.Shop', 'ps')
                    ->addWhere(
                        WhereClause::eq('ps.is_deleted', ':deleted', ['deleted' => 0])
                    ),
                JoinClause::innerJoin('ps.ShopSerieses', 'pss')
                    ->addWhere(
                        WhereClause::eq('pss.Series', ':Series', ['Series' => $params['series_id']])
                    ),
            ];
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryKey()
    {
        return QueryKey::PRODUCT_SEARCH;
    }
}
