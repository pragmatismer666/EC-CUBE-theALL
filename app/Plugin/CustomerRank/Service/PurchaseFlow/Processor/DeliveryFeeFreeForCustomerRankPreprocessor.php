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

use Eccube\Annotation\CartFlow;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\CustomerRank\Service\CustomerRankService;

/**
 * @CartFlow
 */
class DeliveryFeeFreeForCustomerRankPreprocessor implements ItemHolderPreprocessor
{
    private $customerRankService;

    public function __construct(
            CustomerRankService $customerRankService
            )
    {
        $this->customerRankService = $customerRankService;
    }

    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        $Customer = $this->customerRankService->getLoginCustomer();
        if(!is_null($Customer))$this->customerRankService->checkRank($Customer);
        $CustomerRank = $this->customerRankService->getCustomerRank();
        if(!is_null($CustomerRank)){
            if (strlen($CustomerRank->getDeliveryFreeCondition()) > 0) {
                if ($CustomerRank->getDeliveryFreeCondition() <= $itemHolder->getTotal()) {
                    $items = $itemHolder->getItems();
                    foreach ($items as $item) {
                        if ($item->isDeliveryFee()) {
                            $item->setQuantity(0);
                        }
                    }
                }
            }
        }
    }
}
