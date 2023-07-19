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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait
{
        /**
         * @var \Doctrine\Common\Collections\Collection
         *
         * @ORM\OneToMany(targetEntity="Plugin\CustomerRank\Entity\CustomerPrice", mappedBy="ProductClass", cascade={"persist","remove"})
         */
        private $CustomerPrices;

        private $customer_rank_prices = [];
        private $customer_rank_price_inc_taxes = [];
        private $customer_rank_price = null;
        private $customer_rank_price_inc_tax = null;

        public function setCustomerRankPrices($price, $customer_rank_id)
        {
            $this->customer_rank_prices[$customer_rank_id] = $price;

            return $this;
        }

        public function getCustomerRankPrices($customer_rank_id = null)
        {
            if(!is_null($customer_rank_id) && isset($this->customer_rank_prices[$customer_rank_id])){
                return $this->customer_rank_prices[$customer_rank_id];
            }
            return $this->customer_rank_prices;
        }

        public function setCustomerRankPrice($price)
        {
            $this->customer_rank_price = $price;

            return $this;
        }

        public function getCustomerRankPrice($customer_rank_id = null)
        {
            if(!is_null($customer_rank_id)){
                foreach($this->customer_rank_prices as $key => $customer_rank_price){
                    if($key == $customer_rank_id)return $customer_rank_price;
                }
            }
            return $this->customer_rank_price;
        }

        public function setCustomerRankPriceIncTax($price_inc_tax)
        {
            $this->customer_rank_price_inc_tax = $price_inc_tax;

            return $this;
        }

        public function getCustomerRankPriceIncTax($customer_rank_id = null)
        {
            if(!is_null($customer_rank_id)){
                foreach($this->customer_rank_price_inc_taxes as $key => $customer_rank_price_inc_tax){
                    if($key == $customer_rank_id)return $customer_rank_price_inc_tax;
                }
            }
            return $this->customer_rank_price_inc_tax;
        }

        public function setCustomerRankPriceIncTaxes($price_inc_tax, $customer_rank_id)
        {
            $this->customer_rank_price_inc_taxes[$customer_rank_id] = $price_inc_tax;

            return $this;
        }

        public function getCustomerRankPriceIncTaxes($customer_rank_id = null)
        {
            if(!is_null($customer_rank_id) && isset($this->customer_rank_price_inc_taxes[$customer_rank_id])){
                return $this->customer_rank_price_inc_taxes[$customer_rank_id];
            }
            return $this->customer_rank_price_inc_taxes;
        }

        public function addCustomerPrice(\Plugin\CustomerRank\Entity\CustomerPrice $customerPrice)
        {
            $this->CustomerPrices[] = $customerPrice;

            return $this;
        }

        public function removeCustomerPrice(\Plugin\CustomerRank\Entity\CustomerPrice $customerPrice)
        {
            return $this->CustomerPrices->removeElement($customerPrice);
        }

        public function getCustomerPrices()
        {
            return $this->CustomerPrices;
        }
}
