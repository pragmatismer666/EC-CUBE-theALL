<?php

namespace Customize\Repository\Master;

use Customize\Entity\Master\ShopStatus;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ShopStatusRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShopStatus::class);
    }
}
