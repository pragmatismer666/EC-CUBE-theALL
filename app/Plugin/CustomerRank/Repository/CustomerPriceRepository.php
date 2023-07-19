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

namespace Plugin\CustomerRank\Repository;

use Eccube\Repository\AbstractRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Service\TaxRuleService;
use Plugin\CustomerRank\Entity\CustomerPrice;
use Plugin\CustomerRank\Util\CommonUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CustomerPriceRepository extends AbstractRepository
{
    private $productClassRepository;

    private $taxRuleRepository;

    private $taxRuleService;

    public function __construct(
            RegistryInterface $registry,
            string $entityClass = CustomerPrice::class,
            ProductClassRepository $productClassRepository,
            TaxRuleRepository $taxRuleRepository,
            TaxRuleService $taxRuleService
            )
    {
        parent::__construct($registry, $entityClass);
        $this->productClassRepository = $productClassRepository;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxRuleService = $taxRuleService;
    }

    public function getCustomerPriceByProductClass($CustomerRank, $ProductClass)
    {
        $price = null;
        $CustomerPrice = $this->findOneBy(['CustomerRank' => $CustomerRank, 'ProductClass' => $ProductClass]);
        if($CustomerPrice){
            $price = $CustomerPrice->getPrice();
        }

        if(!is_null($CustomerRank)){
            $discount_rate = $CustomerRank->getDiscountRate();
            if(!is_null($discount_rate) && is_null($price)){
                $TaxRule = $ProductClass->getTaxRule();
                $price = $ProductClass->getPrice02() * (1.0 - $discount_rate/100);
                if(is_null($TaxRule)){
                    $TaxRule = $this->taxRuleRepository->getByRule($ProductClass->getProduct(), $ProductClass);
                }
                $price = CommonUtil::roundByCalcRule($price, $TaxRule->getRoundingType()->getId());
            }
        }

        if(is_null($price))$price = $ProductClass->getPrice02();

        return $price;
    }

    public function getCustomerPriceTaxByProductClass($CustomerRank,$ProductClass)
    {
        $price = $this->getCustomerPriceByProductClass($CustomerRank,$ProductClass);
        if(!is_null($price))$tax = $this->taxRuleService->getTax($price, $ProductClass->getProduct(), $ProductClass);

        return $tax;
    }

    public function getCustomerPriceIncTaxByProductClass($CustomerRank,$ProductClass)
    {
        $price = $this->getCustomerPriceByProductClass($CustomerRank,$ProductClass);
        if(!is_null($price))$price = $price + $this->taxRuleService->getTax($price, $ProductClass->getProduct(), $ProductClass);

        return $price;
    }

}