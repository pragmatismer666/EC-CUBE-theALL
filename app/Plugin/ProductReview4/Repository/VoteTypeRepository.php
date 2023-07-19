<?php

namespace Plugin\ProductReview4\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\ProductReview4\Entity\VoteType;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoteTypeRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VoteType::class);
    }
}
