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
 * @EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="check_date", type="datetimetz", nullable=true)
     */
    private $check_date;

    /**
     * @var \Plugin\CustomerRank\Entity\CustomerRank
     *
     * @ORM\ManyToOne(targetEntity="Plugin\CustomerRank\Entity\CustomerRank")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_rank_id", referencedColumnName="id")
     * })
     */
    private $CustomerRank;

    public function setCheckDate($checkDate)
    {
        $this->check_date = $checkDate;

        return $this;
    }

    public function getCheckDate()
    {
        return $this->check_date;
    }

    public function setCustomerRank(\Plugin\CustomerRank\Entity\CustomerRank $customerRank = null)
    {
        $this->CustomerRank = $customerRank;

        return $this;
    }

    public function getCustomerRank()
    {
        return $this->CustomerRank;
    }
}
