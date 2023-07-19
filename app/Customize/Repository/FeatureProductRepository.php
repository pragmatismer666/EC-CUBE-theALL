<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\FeatureProduct;

class FeatureProductRepository extends AbstractRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, FeatureProduct::class );
    }

    public function getByFeature($Feature)
    {
        $qb = $this->createQueryBuilder('fp')
            ->select('fp, p')
            ->leftJoin('fp.Product', 'p')
            ->leftJoin('fp.Feature', 'f')
            ->where('fp.Feature = :Feature')
            ->setParameter('Feature', $Feature)
            ->andWhere('p.Status = 1');
        return $qb;
    }
}