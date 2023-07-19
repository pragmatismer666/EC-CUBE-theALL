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
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    private $_calc_customer_rank = false;
    private $present_customer_rank_name = '';

    public function _calc_customer_rank()
    {
        if (!$this->_calc_customer_rank) {
            $Customer = $this->getCustomer();
            if(!is_null($Customer)){
                $CustomerRank = $Customer->getCustomerRank();
                if(!is_null($CustomerRank)){
                    $this->present_customer_rank_name = $CustomerRank->getName();
                }
            }
            $this->_calc_customer_rank = true;
        }
    }

    public function getPresentCustomerRankName()
    {
        $this->_calc_customer_rank();
        return $this->present_customer_rank_name;
    }

    /**
     * @var int|null
     *
     * @ORM\Column(name="customer_rank_id", type="integer", nullable=true)
     */
    private $customer_rank_id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="customer_rank_name",  type="string", length=255, nullable=true)
     */
    private $customer_rank_name;

    public function setCustomerRankId($customerRankId)
    {
        $this->customer_rank_id = $customerRankId;

        return $this;
    }

    public function getCustomerRankId()
    {
        return $this->customer_rank_id;
    }

    public function setCustomerRankName($customerRankName)
    {
        $this->customer_rank_name = $customerRankName;

        return $this;
    }

    public function getCustomerRankName()
    {
        return $this->customer_rank_name;
    }
}
