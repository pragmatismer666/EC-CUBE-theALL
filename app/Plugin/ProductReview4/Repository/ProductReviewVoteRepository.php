<?php

namespace Plugin\ProductReview4\Repository;

use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;
use Plugin\ProductReview4\Entity\ProductReview;
use Plugin\ProductReview4\Entity\ProductReviewVote;
use Plugin\ProductReview4\Entity\VoteType;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductReviewVoteRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductReviewVote::class);
    }

    /**
     * @param ProductReview $ProductReview
     */
    public function deleteByReview(ProductReview $ProductReview)
    {
        $this->createQueryBuilder('v')
            ->delete('v')
            ->where('v.ProductReview = :ProductReview')
            ->setParameter('ProductReview', $ProductReview)
            ->getQuery()
            ->execute();
        $this->getEntityManager()->flush();
    }

    /**
     * @param Customer $Customer
     */
    public function deleteByCustomer(Customer $Customer)
    {
        $this->createQueryBuilder('v')
            ->delete('v')
            ->where('v.Customer = :Customer')
            ->setParameter('Customer', $Customer)
            ->getQuery()
            ->execute();
        $this->getEntityManager()->flush();
    }

    /**
     * @param ProductReview $ProductReview
     * @param VoteType $voteType
     */
    public function getVoteCount(ProductReview $ProductReview, VoteType $VoteType)
    {
        return $this->createQueryBuilder('v')
            ->select('count(v)')
            ->andWhere('v.ProductReview = :ProductReview')
            ->setParameter('ProductReview', $ProductReview)
            ->andWhere('v.VoteType = :VoteType')
            ->setParameter('VoteType', $VoteType)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param array $searchData
     * @return \Doctrine\ORM\Query
     */
    public function getQueryBuilderBySearchData(array $searchData)
    {
        $qb = $this->createQueryBuilder('v');
        if (!empty($searchData['ProductReview'])) {
            $qb->andWhere('v.ProductReview = :ProductReview')
                ->setParameter('ProductReview', $searchData['ProductReview']->getId());
        }
        if (!empty($searchData['Customer'])) {
            $qb->andWhere('v.Customer = :Customer')
                ->setParameter('Customer', $searchData['Customer']->getId());
        }
        if (!empty($searchData['VoteType'])) {
            $qb->andWhere('v.VoteType = :VoteType')
                ->setParameter('VoteType', $searchData['VoteType']->getId());
        }

//        dump($query->getSQL());
//
//        dump($query->getParameters());
//        exit;

        return $qb->getQuery();
    }
}
