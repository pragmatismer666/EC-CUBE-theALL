<?php

namespace Plugin\CustomerBan\Repository;

use Eccube\Repository\AbstractRepository;
use Eccube\Repository\QueryKey;
use Plugin\CustomerBan\Entity\CustomerBan;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Doctrine\Query\Queries;

class CustomerBanRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    protected $queries;

    public function __construct(
        RegistryInterface $registry,
        Queries $queries
    )
    {
        parent::__construct($registry, CustomerBan::class);
        $this->queries = $queries;
    }

    public function getQueryBuilderBySearchData($searchData = [])
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b, c')
            ->innerJoin('b.Customer', 'c');
        if (!empty($searchData['multi'])) {
            $clean_key_multi = preg_replace('/\s+|[　]+/u', '', $searchData['multi']);
            $id = preg_match('/^\d{0,10}$/', $clean_key_multi) ? $clean_key_multi : null;
            $qb
                ->andWhere('c.id = :customer_id OR CONCAT(c.name01, c.name02) LIKE :name OR CONCAT(c.kana01, c.kana02) LIKE :kana OR c.email LIKE :email')
                ->setParameter('customer_id', $id)
                ->setParameter('name', '%'.$clean_key_multi.'%')
                ->setParameter('kana', '%'.$clean_key_multi.'%')
                ->setParameter('email', '%'.$clean_key_multi.'%');
        }

        $qb->addOrderBy('c.update_date', 'DESC');

        return $this->queries->customize(QueryKey::CUSTOMER_SEARCH, $qb, $searchData);
    }
}
