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

namespace Plugin\CustomerRank\Entity;

use Eccube\Annotation\EntityExtension;
use Doctrine\ORM\Mapping as ORM;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
        private $_calc_customer_rank = false;
        private $customer_price = [];
        private $customerPriceIncTaxs = [];
        private $customer_rank_price = [];
        private $customerRankPriceIncTaxs = [];

        public function _calc_customer_rank()
        {
            if (!$this->_calc_customer_rank) {
                foreach ($this->getProductClasses() as $ProductClass) {
                    /* @var $ProductClass \Eccube\Entity\ProductClass */
                    // stock_find
                    if ($ProductClass->isVisible() == false) {
                        continue;
                    }
                    $ClassCategory1 = $ProductClass->getClassCategory1();
                    $ClassCategory2 = $ProductClass->getClassCategory2();
                    if ($ClassCategory1 && !$ClassCategory1->isVisible()) {
                        continue;
                    }
                    if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                        continue;
                    }

                    if (!is_null($ProductClass->getCustomerRankPrice())) {
                        $this->customer_price[] = $ProductClass->getCustomerRankPrice();
                        $this->customerPriceIncTaxs[] = $ProductClass->getCustomerRankPriceIncTax();
                    }
                    if(count($ProductClass->getCustomerRankPrices()) > 0){
                        foreach($ProductClass->getCustomerRankPrices() as $customer_rank_id => $customerPrice){
                            $this->customer_rank_price[$customer_rank_id][$ProductClass->getId()] = $customerPrice;
                            $this->customerRankPriceIncTaxs[$customer_rank_id][$ProductClass->getId()] = $ProductClass->getCustomerRankPriceIncTaxes($customer_rank_id);
                        }
                    }
                }

                $this->_calc_customer_rank = true;
            }
        }

        public function getCustomerRankPriceMin($customer_rank_id = null)
        {
            $this->_calc_customer_rank();
            if(!is_null($customer_rank_id) && isset($this->customer_rank_price[$customer_rank_id])){
                return min($this->customer_rank_price[$customer_rank_id]);
            }

            return min($this->customer_price);
        }

        public function getCustomerRankPriceMax($customer_rank_id = null)
        {
            $this->_calc_customer_rank();
            if(!is_null($customer_rank_id) && isset($this->customer_rank_price[$customer_rank_id])){
                return max($this->customer_rank_price[$customer_rank_id]);
            }

            return max($this->customer_price);
        }

        public function getCustomerRankPriceIncTaxMin($customer_rank_id = null)
        {
            $this->_calc_customer_rank();
            if(!is_null($customer_rank_id) && isset($this->customerRankPriceIncTaxs[$customer_rank_id])){
                return min($this->customerRankPriceIncTaxs[$customer_rank_id]);
            }

            return min($this->customerPriceIncTaxs);
        }

        public function getCustomerRankPriceIncTaxMax($customer_rank_id = null)
        {
            $this->_calc_customer_rank();
            if(!is_null($customer_rank_id) && isset($this->customerRankPriceIncTaxs[$customer_rank_id])){
                return max($this->customerRankPriceIncTaxs[$customer_rank_id]);
            }

            return max($this->customerPriceIncTaxs);
        }
}
