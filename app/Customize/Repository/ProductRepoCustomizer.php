<?php

namespace Customize\Repository;

use Eccube\Doctrine\Query\WhereClause;
use Eccube\Doctrine\Query\WhereCustomizer;
use Eccube\Repository\QueryKey;

class ProductRepoCustomizer extends WhereCustomizer
{
    /**
     * @param array $params
     * @param $queryKey
     *
     * @return WhereClause[]
     */
    protected function createStatements($params, $queryKey)
    {
        if (isset($params['Shop'])) {
            return [WhereClause::eq('p.Shop', ':Shop', ['Shop' => $params['Shop']])];
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
