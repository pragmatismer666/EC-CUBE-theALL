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

namespace Customize\Services\PurchaseFlow\Processor;

use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\Processor\AbstractPurchaseProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Customize\Services\Payment\Method\StripeCredit;
use Customize\Entity\StripeConfig;
/**
 * 受注情報更新処理.
 */
class OrderUpdateProcessor extends AbstractPurchaseProcessor
{
    /**
     * @var OrderStatusRepository
     */
    private $orderStatusRepository;

    /**
     * OrderUpdateProcessor constructor.
     *
     * @param OrderStatusRepository $orderStatusRepository
     */
    public function __construct(OrderStatusRepository $orderStatusRepository)
    {
        $this->orderStatusRepository = $orderStatusRepository;
    }

    public function commit(ItemHolderInterface $target, PurchaseContext $context)
    {
        if (!$target instanceof Order) {
            return;
        }

        if ($target->getPayment()->getMethodClass() == StripeCredit::class) {
            if ($target->getOrderStatus()->getId() == OrderStatus::PAID) return;
        }
        $OrderStatus = $this->orderStatusRepository->find(OrderStatus::NEW);
        $target->setOrderStatus($OrderStatus);
        $target->setOrderDate(new \DateTime());
    }
}
