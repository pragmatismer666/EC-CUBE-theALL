<?php

namespace Customize\Repository\Master;

use Customize\Entity\Master\Series;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SeriesRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Series::class);
    }

    public function getList() 
    {
        $qb = $this->createQueryBuilder('sr')
            ->select('sr')
            ->orderBy('sr.sort_no', 'asc');
        return $qb->getQuery()->getResult();
    }
}
