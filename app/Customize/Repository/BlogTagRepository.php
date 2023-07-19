<?php

namespace Customize\Repository;

use Customize\Entity\BlogTag;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BlogTagRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BlogTag::class);
    }
}
