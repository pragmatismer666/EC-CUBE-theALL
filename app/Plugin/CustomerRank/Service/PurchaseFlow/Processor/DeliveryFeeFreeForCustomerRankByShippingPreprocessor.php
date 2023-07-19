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

use Eccube\Annotation\ShoppingFlow;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Order;
use Eccube\Entity\Customer;
use Eccube\Repository\DeliveryFeeRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\CustomerRank\Service\CustomerRankService;

/**
 * @ShoppingFlow
 */
class DeliveryFeeFreeForCustomerRankByShippingPreprocessor implements ItemHolderPreprocessor
{
    private $deliveryFeeRepository;
    private $customerRankService;

    public function __construct(
            DeliveryFeeRepository $deliveryFeeRepository,
            CustomerRankService $customerRankService
            )
    {
        $this->deliveryFeeRepository = $deliveryFeeRepository;
        $this->customerRankService = $customerRankService;
    }

    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        $Customer = $this->customerRankService->getLoginCustomer();
        if(!is_null($Customer)){
            $this->customerRankService->checkRank($Customer);
            if ($itemHolder instanceof Order) {
                $Order = $itemHolder;
                if($Customer instanceof Customer){
                    $CustomerRank = $Customer->getCustomerRank();
                    if(!is_null($CustomerRank)){
                        if (strlen($CustomerRank->getDeliveryFreeCondition()) > 0) {
                            $total = 0;
                            foreach ($Order->getShippings() as $Shipping) {
                                foreach ($Shipping->getProductOrderItems() as $Item) {
                                    $total += $Item->getPriceIncTax() * $Item->getQuantity();
                                }
                            }
                            if ($CustomerRank->getDeliveryFreeCondition() <= $total) {
                                foreach ($Order->getShippings() as $Shipping) {
                                    foreach ($Shipping->getOrderItems() as $Item) {
                                        if ($Item->isDeliveryFee()) {
                                            $Item->setQuantity(0);
                                        }
                                    }
                                }
                            }else{
                                foreach ($Order->getShippings() as $Shipping) {
                                    $Delivery = $Shipping->getDelivery();
                                    $Pref = $Shipping->getPref();
                                    foreach ($Shipping->getOrderItems() as $Item) {
                                        if ($Item->isDeliveryFee()) {
                                            $DeliveryFee = $this->deliveryFeeRepository->findOneBy([
                                                'Delivery' => $Delivery,
                                                'Pref' => $Pref,
                                            ]);
                                            if($DeliveryFee){
                                                $Item->setPrice($DeliveryFee->getFee());
                                                $Item->setQuantity(1);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
