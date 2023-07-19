<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\FeatureShop;
use Customize\Entity\Master\ShopStatus;

class FeatureShopRepository extends AbstractRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct( $registry, FeatureShop::class );
    }

    public function getByFeature($Feature)
    {
        $qb = $this->createQueryBuilder('fs')
            ->select('fs, p')
            ->leftJoin('fs.Shop', 'fss')
            ->where('fss.Status = :Status')
            ->setParameter('Status', ShopStatus::DISPLAY_SHOW)
            ->leftJoin('fs.Feature', 'fsf')
            ->where('fsf.Feature = :Feature')
            ->setParameter('Feature', $Feature);
            
            
        return $qb;
    }
}