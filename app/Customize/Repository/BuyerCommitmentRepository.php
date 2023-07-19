<?php

namespace Customize\Repository;

use Customize\Entity\Master\ShopStatus;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\BuyerCommitment;
use Eccube\Doctrine\Query\Queries;

class BuyerCommitmentRepository extends AbstractRepository {

    
    public function __construct(
        RegistryInterface $registry
    ) {
        parent::__construct( $registry, BuyerCommitment::class );
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }

    public function getByProduct($Product)
    {
        return $this->createQueryBuilder('bc')
            ->select("bc")
            ->where("bc.Product=:Product")
            ->setParameter("Product", $Product)
            ->getQuery()
            ->getResult();
    }
}