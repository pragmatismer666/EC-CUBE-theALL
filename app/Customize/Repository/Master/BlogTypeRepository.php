<?php

namespace Customize\Repository\Master;

use Customize\Entity\Master\BlogType;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BlogTypeRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BlogType::class);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getList()
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.sort_no', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
