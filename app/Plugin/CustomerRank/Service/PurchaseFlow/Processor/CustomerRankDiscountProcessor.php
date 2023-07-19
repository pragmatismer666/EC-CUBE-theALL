<?php
/*
 * Plugin Name : CustomerRank
 *
 * Copyright (C) BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CustomerRank\Service\PurchaseFlow\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Annotation\ShoppingFlow;
use Eccube\Annotation\OrderFlow;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\ItemInterface;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\TaxDisplayType;
use Eccube\Entity\Master\TaxType;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\PurchaseFlow\DiscountProcessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseProcessor;

/**
 * @ShoppingFlow
 * @OrderFlow
 */
class CustomerRankDiscountProcessor implements DiscountProcessor
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addDiscountItem(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {

        if (!$itemHolder instanceof Order) {
            return;
        }

        if (!$itemHolder->getCustomer()) {
            return;
        }

        $CustomerRank = $itemHolder->getCustomer()->getCustomerRank();
        if(!is_null($CustomerRank)){
            if($CustomerRank->getDiscountValue() > 0){
                $addDiscount = true;
                foreach($itemHolder->getOrderItems() as $OrderItem){
                    if($OrderItem->getProductName() == $CustomerRank->getName().trans('customerrank.common.rank_discount')){
                        $addDiscount = false;
                        break;
                    }
                }
                if($addDiscount){
                    $DiscountType = $this->entityManager->find(OrderItemType::class, OrderItemType::DISCOUNT);
                    $TaxInclude = $this->entityManager->find(TaxDisplayType::class, TaxDisplayType::INCLUDED);
                    $Taxation = $this->entityManager->find(TaxType::class, TaxType::NON_TAXABLE);

                    $OrderItem = new OrderItem();
                    $OrderItem->setProductName($CustomerRank->getName().trans('customerrank.common.rank_discount'))
                        ->setPrice($CustomerRank->getDiscountValue()*-1)
                        ->setQuantity(1)
                        ->setTax(0)
                        ->setTaxRate(0)
                        ->setTaxRuleId(null)
                        ->setRoundingType(null)
                        ->setOrderItemType($DiscountType)
                        ->setTaxDisplayType($TaxInclude)
                        ->setTaxType($Taxation)
                        ->setOrder($itemHolder);
                    $itemHolder->addItem($OrderItem);
                }
            }
        }
    }

    public function removeDiscountItem(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {

    }
}
