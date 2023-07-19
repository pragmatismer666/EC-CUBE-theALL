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
use Eccube\Annotation\ShoppingFlow;
use Eccube\Entity\ItemInterface;
use Eccube\Entity\OrderItem;
use Eccube\Service\TaxRuleService;
use Eccube\Service\PurchaseFlow\ItemValidator;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\CustomerRank\Repository\CustomerPriceRepository;
use Plugin\CustomerRank\Service\CustomerRankService;

/**
 * @CartFlow
 * @ShoppingFlow
 */
class PriceChangeProcessor extends ItemValidator
{
    private $customerPriceRepository;

    private $customerRankService;

    private $taxRuleService;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(
            CustomerPriceRepository $customerPriceRepository,
            CustomerRankService $customerRankService,
            TaxRuleService $taxRuleService
            )
    {
        $this->customerPriceRepository = $customerPriceRepository;
        $this->customerRankService = $customerRankService;
        $this->taxRuleService = $taxRuleService;
    }

    /**
     * @param ItemInterface $item
     * @param PurchaseContext $context
     */
    public function validate(ItemInterface $item, PurchaseContext $context)
    {
        if (!$item->isProduct()) {
            return;
        }

        $CustomerRank = $this->customerRankService->getCustomerRank();

        if(!is_null($CustomerRank)){
            if ($item instanceof OrderItem) {
                $price = $this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank,$item->getProductClass());
                $tax = $this->customerPriceRepository->getCustomerPriceTaxByProductClass($CustomerRank,$item->getProductClass());
                if(method_exists($item, 'getOptionPrice')){
                    $price += $item->getOptionPrice();
                    $tax += $item->getOptionTax();
                }
                $item->setTax($tax);
            } else {
                $price = $this->customerPriceRepository->getCustomerPriceIncTaxByProductClass($CustomerRank,$item->getProductClass());
                if(method_exists($item, 'getOptionPrice')){
                    $price = $this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank,$item->getProductClass());
                    $price += $item->getOptionPrice();
                    $price += $this->taxRuleService->getTax($price,$item->getProductClass()->getProduct(),$item->getProductClass());
                }
            }
            if(!is_null($price)){
                $item->setPrice($price);
            }
        }
    }
}
