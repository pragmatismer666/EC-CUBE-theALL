<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * Class FeatureShop
 *
 * @ORM\Table(name="cmd_feature_shop")
 * @ORM\Entity(repositoryClass="Customize\Repository\FeatureShopRepository")
 */
class FeatureShop extends AbstractEntity
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
     * @var int
     *
     * @ORM\Column(name="feature_id", type="integer", options={"unsigned":true})
     */
    private $feature_id;

    /**
     * @var int
     * 
     * @ORM\Column(name="shop_id", type="integer", options={"unsigned": true})
     */
    private $shop_id;

    /**
     * @var Customize\Entity\Feature
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Feature", inversedBy="FeatureShops", cascade={"persist"})
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="feature_id", referencedColumnName="id")
     * })
     */
    private $Feature;

    /**
     * @var Customize\Entity\Shop
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    public function getId()
    {
        return $this->id;
    }

    public function getFeatureId()
    {
        return $this->feature_id;
    }
    public function setFeatureId($feature_id)
    {
        $this->feature_id = $feature_id;
        return $this;
    }
    public function getShopId()
    {
        return $this->shop_id;
    }
    public function setShopId($shop_id)
    {
        $this->shop_id = $shop_id;
        return $this;
    }
    public function getFeature()
    {
        return $this->Feature;
    }
    public function setFeature($Feature)
    {
        $this->Feature = $Feature;
        return $this;
    }
    public function getShop()
    {
        return $this->Shop;
    }
    public function setShop($Shop)
    {
        $this->Shop = $Shop;
        return $this;
    }
}