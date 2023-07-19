<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview4\Repository;

use Eccube\Entity\Product;
use Eccube\Entity\Customer;
use Eccube\Repository\AbstractRepository;
use Plugin\ProductReview4\Entity\ProductPurchasedStatus;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductPurchasedStatusRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ProductPurchasedStatus::class);
    }

    /**
     * @param Product $Product
     * @param Customer|null $Customer
     * @return ProductPurchasedStatus
     */
    public function getPurchasedStatus(Product $Product, $Customer)
    {
        if (!$Customer) {
            return $this->find(ProductPurchasedStatus::NOT_PURCHASED);
        }
        $orderRepository = $this->getEntityManager()->getRepository('Eccube\Entity\Order');
        $qb = $orderRepository->createQueryBuilder('o');
        $result = count($qb->addSelect(['oi', 'p', 'c'])
            ->innerJoin('o.OrderItems', 'oi')
            ->innerJoin('oi.Product', 'p')
            ->innerJoin('o.Customer', 'c')
            ->andWhere('p = :Product')
            ->setParameter('Product', $Product)
            ->andWhere('c = :Customer')
            ->setParameter('Customer', $Customer)
            ->getQuery()
            ->getResult());
        if ($result > 0) {
            return $this->find(ProductPurchasedStatus::PURCHASED);
        } else {
            return $this->find(ProductPurchasedStatus::NOT_PURCHASED);
        }
    }
}
