<?php

namespace Customize\Repository;

use Customize\Entity\Master\ShopStatus;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\BuyerReview;
use Eccube\Doctrine\Query\Queries;

class BuyerReviewRepository extends AbstractRepository {

    
    public function __construct(
        RegistryInterface $registry
    ) {
        parent::__construct( $registry, BuyerReview::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }

    public function getByProduct($Product)
    {
        return $this->createQueryBuilder('brc')
            ->select("brc")
            ->where("brc.Product=:Product")
            ->setParameter("Product", $Product)
            ->getQuery()
            ->getResult();
    }
}