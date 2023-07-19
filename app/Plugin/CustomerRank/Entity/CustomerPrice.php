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
 * @ORM\Table(name="plg_customerrank_dtb_customer_price")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\CustomerRank\Repository\CustomerPriceRepository")
 */
class CustomerPrice extends \Eccube\Entity\AbstractEntity
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Plugin\CustomerRank\Entity\CustomerRank
     *
     * @ORM\ManyToOne(targetEntity="Plugin\CustomerRank\Entity\CustomerRank")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_rank_id", referencedColumnName="id")
     * })
     */
    private $CustomerRank;

    /**
     * @var \Eccube\Entity\ProductClass
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\ProductClass", inversedBy="CustomerPrices", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_class_id", referencedColumnName="id")
     * })
     */
    private $ProductClass;

    /**
     * @var string|null
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true})
     */
    private $price;

    public function getId()
    {
        return $this->id;
    }

    public function setCustomerRank($customerRank)
    {
        $this->CustomerRank = $customerRank;

        return $this;
    }

    public function getCustomerRank()
    {
        return $this->CustomerRank;
    }

    public function setProductClass($productClass)
    {
        $this->ProductClass = $productClass;

        return $this;
    }

    public function getProductClass()
    {
        return $this->ProductClass;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }
}
