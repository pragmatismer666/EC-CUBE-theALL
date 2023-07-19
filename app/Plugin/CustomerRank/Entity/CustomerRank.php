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

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerRank
 *
 * @ORM\Table(name="plg_customerrank_dtb_customer_rank")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\CustomerRank\Repository\CustomerRankRepository")
 */
class CustomerRank extends \Eccube\Entity\AbstractEntity
{
    const ENABLED = 1;
    const DISABLED = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="discount_rate", type="integer", nullable=true)
     */
    private $discount_rate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="discount_value", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true})
     */
    private $discount_value;

    /**
     * @var int|null
     *
     * @ORM\Column(name="point_rate", type="integer", nullable=true)
     */
    private $point_rate;


    /**
     * @var string|null
     *
     * @ORM\Column(name="delivery_free_condition", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true})
     */
    private $delivery_free_condition;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cond_amount", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true})
     */
    private $cond_amount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="cond_buytimes", type="integer", nullable=true)
     */
    private $cond_buytimes;

    /**
     * @var boolean|null
     *
     * @ORM\Column(name="initial_flg", type="boolean", nullable=true)
     */
    private $initial_flg;

    /**
     * @var boolean|null
     *
     * @ORM\Column(name="fixed_flg", type="boolean", nullable=true)
     */
    private $fixed_flg;

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDiscountRate($discountRate)
    {
        $this->discount_rate = $discountRate;

        return $this;
    }

    public function getDiscountRate()
    {
        return $this->discount_rate;
    }

    public function setDiscountValue($discountValue)
    {
        $this->discount_value = $discountValue;

        return $this;
    }

    public function getDiscountValue()
    {
        return $this->discount_value;
    }

    public function setPointRate($pointRate)
    {
        $this->point_rate = $pointRate;

        return $this;
    }

    public function getPointRate()
    {
        return $this->point_rate;
    }

    public function setDeliveryFreeCondition($deliveryFreeCondition)
    {
        $this->delivery_free_condition = $deliveryFreeCondition;

        return $this;
    }

    public function getDeliveryFreeCondition()
    {
        return $this->delivery_free_condition;
    }

    public function setCondAmount($condAmount)
    {
        $this->cond_amount = $condAmount;

        return $this;
    }

    public function getCondAmount()
    {
        return $this->cond_amount;
    }

    public function setCondBuytimes($condBuytimes)
    {
        $this->cond_buytimes = $condBuytimes;

        return $this;
    }

    public function getCondBuytimes()
    {
        return $this->cond_buytimes;
    }

    public function setInitialFlg($initialFlg)
    {
        $this->initial_flg = $initialFlg;

        return $this;
    }

    public function getInitialFlg()
    {
        return $this->initial_flg;
    }

    public function setFixedFlg($fixedFlg)
    {
        $this->fixed_flg = $fixedFlg;

        return $this;
    }

    public function getFixedFlg()
    {
        return $this->fixed_flg;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setCreateDate($date)
    {
        $this->create_date = $date;

        return $this;
    }

    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function setUpdateDate($date)
    {
        $this->update_date = $date;

        return $this;
    }

    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
