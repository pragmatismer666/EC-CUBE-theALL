<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\StripeConfig;
use Eccube\Doctrine\Query\Queries;

class StripeConfigRepository extends AbstractRepository {
    
    public function __construct(
        RegistryInterface $registry
    ) {
        parent::__construct( $registry, StripeConfig::class );
    }
    public function get() {
        $arr = $this->findAll();
        if (count($arr) > 0) {
            return $arr[0];
        }
        return null;
    }
}