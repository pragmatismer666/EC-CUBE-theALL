<?php

namespace Customize\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Repository\QueryKey;
use Customize\Entity\Master\ShopStatus;

class ProductRepository extends \Eccube\Repository\ProductRepository
{
    /**
     * @return ArrayCollection
     */
    public function getDisplayNewProducts()
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect(['pc'])
            ->innerJoin('p.ProductClasses', 'pc')
            ->where('pc.visible = :visible')
            ->andWhere('p.Status = 1')
            ->setParameter('visible', true)
            ->orderBy('p.create_date', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setMaxResults($this->eccubeConfig->get('malldevel.display_new_product_count'));
        $qb = $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, []);
        return $qb->getQuery()->getResult();
    }

    public function getBySeries($Series)
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p, ps')
                ->innerJoin('p.Shop', 'ps')
                // ->where('ps.Series = :Series')
                ->innerJoin('ps.ShopSerieses', 'pss')
                ->where('pss.Series = :Series')
                ->setParameter('Series', $Series)
                ->andWhere('p.Status = 1')
                ->innerJoin('p.ProductClasses', 'pc')
                ->andWhere('pc.visible = :visible')
                ->setParameter('visible', true)
                ->orderBy('p.create_date', 'DESC')
                ->addOrderBy('p.id', 'DESC');
        return $this->queries->customize(QueryKey::PRODUCT_SEARCH, $qb, []);
    }
    public function getProductsByIds($ids)
    {
        if (empty($ids)) return [];
        $qb = $this->createQueryBuilder('p');
        return $qb->where($qb->expr()->in('p.id', $ids))
            ->andWhere('p.Status = 1')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();

    }
    public function getProductsByShop($Shop, $offset = 0, $limit = 10)
    {
        return $this->createQueryBuilder('p')
                ->select('p')
                ->where('p.Shop = :Shop')
                ->setParameter('Shop', $Shop)
                ->andWhere('p.Status = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getResult();
        
    }
    public function getProductsByShopIds($shop_ids, $offset = 0, $limit = 10)
    {
        // $qb = $this->createQueryBuilder('p')
        //         ->select('p')
        //         ->innerJoin('p.Shop', 'ps');
        // $result = $qb->where($qb->expr()->in('p.Shop', ':Shops'))
        //         ->setParameter('Shops', $shop_ids)
        //         ->andWhere('ps.Status=:ShopStatus')
        //         ->setParameter('ShopStatus', ShopStatus::DISPLAY_SHOW)
        //         ->andWhere('p.Status = 1')
        //         ->setMaxResults($limit)
        //         ->setFirstResult($offset)
        //         ->orderBy('p.id', 'DESC')
        //         ->getQuery()
        //         ->getResult();
        // return $result;

        return $this->createQueryBuilder('p')
                ->select('p')
                ->innerJoin('p.Shop', 'ps')
                ->where('ps.id IN(:shop_ids)')
                ->setParameter('shop_ids', $shop_ids)
                ->andWhere('ps.Status=:ShopStatus')
                ->setParameter('ShopStatus', ShopStatus::DISPLAY_SHOW)
                ->andWhere('p.Status = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getResult();
        
    }
}
