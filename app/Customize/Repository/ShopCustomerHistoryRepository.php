<?php

namespace Customize\Repository;

use Customize\Entity\ShopCustomerHistory;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ShopCustomerHistoryRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry, EccubeConfig $eccubeConfig)
    {
        parent::__construct($registry, ShopCustomerHistory::class);

        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomerHistory(Customer $Customer)
    {
        return $this->createQueryBuilder('h')
            ->addSelect('s')
            ->innerJoin('h.Shop', 's')
            ->where('h.Customer = :Customer')
            ->setParameter('Customer', $Customer)
            ->orderBy('h.create_date', 'DESC')
            ->addOrderBy('h.id', 'DESC')
            ->setMaxResults($this->eccubeConfig->get('malldevel.customer_history.max'))
            ->getQuery()
            ->getResult();
    }

    public function saveCustomerHistory($ShopHistories)
    {
        if (count($ShopHistories) === 0) {
            return;
        }
        /** @var ShopCustomerHistory $First */
        $First = $ShopHistories[0];
        $this->createQueryBuilder('h')
            ->delete('h')
            ->where('h.Customer = :Customer', $First->getCustomer())
            ->getQuery()
            ->execute();
        foreach($ShopHistories as $ShopHistory) {
            $this->getEntityManager()->persist($ShopHistory);
        }
        $this->getEntityManager()->flush();
    }
}
