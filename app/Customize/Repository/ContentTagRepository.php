<?php

namespace Customize\Repository;

use Customize\Entity\ContentTag;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ContentTagRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ContentTag::class);
    }

    public function getList($blogTypeId = null)
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect('bt')
            ->innerJoin('t.BlogType', 'bt')
            ->orderBy('t.sort_no', 'DESC')
            ->addOrderBy('t.id', 'DESC');
        if (!empty($blogTypeId)) {
            $qb->andWhere('bt.id = :blog_type_id')
                ->setParameter('blog_type_id', $blogTypeId);
        }
        return $qb->getQuery()->getResult();
    }

    public function getNewSortNo()
    {
        $last = $this->createQueryBuilder('t')
            ->select('t.sort_no')
            ->orderBy('t.sort_no', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        if (empty($last)) {
            return 1;
        }
        return $last['sort_no'] + 1;
    }
}
