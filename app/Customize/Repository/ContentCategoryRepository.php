<?php

namespace Customize\Repository;

use Customize\Entity\ContentCategory;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ContentCategoryRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ContentCategory::class);
    }

    public function getList($blogTypeId = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('bt')
            ->innerJoin('c.BlogType', 'bt')
            ->orderBy('c.sort_no', 'DESC')
            ->addOrderBy('c.id', 'DESC');
        if (!empty($blogTypeId)) {
            $qb->andWhere('bt.id = :blog_type_id')
                ->setParameter('blog_type_id', $blogTypeId);
        }
        return $qb->getQuery()->getResult();

    }

    public function getNewSortNo()
    {
        $sort_no = $this->createQueryBuilder('c')
            ->select('c.sort_no')
            ->orderBy('c.sort_no', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        if (empty($sort_no)) {
            return 1;
        } else {
            return $sort_no['sort_no'] + 1;
        }
    }
}
