<?php

namespace Customize\Repository;

use Customize\Entity\Master\ShopStatus;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\Shop;
use Eccube\Doctrine\Query\Queries;

class ShopRepository extends AbstractRepository {

    protected $queries;
    
    public function __construct(
        RegistryInterface $registry,
        Queries $queries
    ) {
        parent::__construct( $registry, Shop::class );
        $this->queries = $queries;
    
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
    public function getShopsQueryBuilder( $search_data = null) {

        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.is_deleted = :deleted')
            ->setParameter('deleted', 0)
            ->andWhere('s.Status = :status')
            ->setParameter('status', ShopStatus::DISPLAY_SHOW);
        return $qb;
        // TODO search data parse

        // ----------------------
    }
    
    public function getAllShopsQueryBuilder() {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.is_deleted = :deleted')
            ->setParameter('deleted', 0);
        return $qb;
    }

    /**
     * @param $searchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdminShopsQueryBuilder($searchData)
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->andWhere('s.is_deleted = :deleted')
            ->setParameter('deleted', 0);
        return $qb;
    }

    public function getByCategoryAndKata($Category, $Kata, $isVisible = true ) {
        $qb = $this->createQueryBuilder('s')
            ->select(['s', 'c', 'sc', 'sk'])
            ->leftJoin('s.ShopCategories', 'sc')
            ->leftJoin('sc.Category','c')
            ->leftJoin('s.Katakana', 'sk')
            ->where('c = :Category')
            ->setParameter('Category', $Category)
            ->andWhere('sk = :Kata')
            ->setParameter('Kata', $Kata);
        if ($isVisible) {
            $qb->andWhere('s.is_deleted = :deleted')
                ->setParameter('deleted', 0)
                ->andWhere('s.Status = :Status')
                ->setParameter('Status', ShopStatus::DISPLAY_SHOW);
        }
        return $qb->getQuery()
                ->getResult();
    }
    public function getByIdList( $ids ) {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->add('where', $qb->expr()->in('s.id', $ids));
        return $qb->getQuery()->getResult();
    }
}