<?php

namespace Customize\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Eccube\Util\StringUtil;
use Customize\Entity\Apply;
use Eccube\Doctrine\Query\Queries;

class ApplyRepository extends AbstractRepository {

    protected $queries;
    
    
    public function __construct(
        RegistryInterface $registry,
        Queries $queries
    ) {
        parent::__construct( $registry, Apply::class );
        $this->queries = $queries;
    
    }

    public function get( $id = 1 ) {
        return $this->find($id);
    }
    public function getAdminQueryBuilder($searchData = null) {

        $qb = $this->createQueryBuilder('a')->where("1=1");
        if (!empty($searchData['name'])) {
            $qb->andWhere('a.name like :name')
                ->setParameter('name',  '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['name']).'%');
        }
        if (!empty($searchData['shop_name'])) {
            $qb->andWhere("a.shop_name like :shop_name")
                ->setParameter("shop_name", '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['shop_name']).'%');
        }
        if (!empty($searchData['order_mail'])) {
            $qb->andWhere("a.order_mail like :order_mail")
                ->setParameter("order_mail", '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['order_mail']).'%');
        }
        if (!empty($searchData['login_id'])) {
            $qb->andWhere("a.login_id like :login_id")
                ->setParameter("login_id", '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['login_id']).'%');
        }
        if (!empty($searchData['company_name'])) {
            $qb->andWhere("a.company_name like :company_name")
                ->setParameter("company_name", '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['company_name']).'%');
        }
        if (!empty($searchData['representative'])) {
            $qb->andWhere("a.representative like :representative")
                ->setParameter("representative", '%'.str_replace(['%', '_'], ['\\%', '\\_'], $searchData['representative']).'%');
        }
        if (!empty($searchData['Pref'])) {
            $qb->andWhere("a.Pref=:Pref")
                ->setParameter("Pref", $searchData['Pref']);
        }
        if (!empty($searchData['status'])) {
            $qb->andWhere($qb->expr()->in('a.status', ':status'))
                ->setParameter('status', $searchData['status']);
        }
        $qb->addOrderBy('a.status', 'asc')
            ->addOrderBy('a.created_at', 'desc');
            
        return $qb;
    }
}