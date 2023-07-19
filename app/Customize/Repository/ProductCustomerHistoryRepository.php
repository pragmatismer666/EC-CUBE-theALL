<?php

namespace Customize\Repository;

use Customize\Entity\ProductCustomerHistory;
use Doctrine\Common\Collections\Collection;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductCustomerHistoryRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry, EccubeConfig $eccubeConfig)
    {
        parent::__construct($registry, ProductCustomerHistory::class);

        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomerHistory(Customer $Customer)
    {
        return $this->createQueryBuilder('h')
            ->addSelect('p')
            ->innerJoin('h.Product', 'p')
            ->where('h.Customer = :Customer')
            ->setParameter('Customer', $Customer)
            ->orderBy('h.create_date', 'DESC')
            ->addOrderBy('h.id', 'DESC')
            ->setMaxResults($this->eccubeConfig->get('malldevel.customer_history.max'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array|Collection $ProductHistories
     */
    public function saveCustomerHistory($ProductHistories)
    {
        if (count($ProductHistories) === 0) {
            return;
        }
        /** @var ProductCustomerHistory $First */
        $First = $ProductHistories[0];
        $this->createQueryBuilder('h')
            ->delete()
            ->where('h.Customer = :Customer')
            ->setParameter('Customer', $First->getCustomer())
            ->getQuery()
            ->execute();
        dump($ProductHistories); exit;
        foreach($ProductHistories as $ProductHistory) {
            $this->getEntityManager()->persist($ProductHistory);
        }
        $this->getEntityManager()->flush();
    }
}
