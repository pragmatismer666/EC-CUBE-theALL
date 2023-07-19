<?php

namespace Customize\Repository\Customizer\Admin;

use Eccube\Doctrine\Query\OrderByClause;
use Eccube\Doctrine\Query\OrderByCustomizer;
use Eccube\Repository\QueryKey;

class ProductSearchOrderByCustomizer extends OrderByCustomizer
{
    /**
     * @param array $params
     * @param $queryKey
     *
     * @return OrderByClause[]
     */
    protected function createStatements($params, $queryKey)
    {
        if (!empty($params['order_by'])) {
            $sort_by = $params['sort_by']??"desc";
            return [new OrderByClause("p." . $params['order_by'], $sort_by)];
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryKey()
    {
        return QueryKey::PRODUCT_SEARCH_ADMIN;
    }
}
