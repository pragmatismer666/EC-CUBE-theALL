<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * Class FeatureProduct
 *
 * @ORM\Table(name="cmd_feature_product")
 * @ORM\Entity(repositoryClass="Customize\Repository\FeatureProductRepository")
 */
class FeatureProduct extends AbstractEntity
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
     * @ORM\Column(name="product_id", type="integer", options={"unsigned": true})
     */
    private $product_id;

    /**
     * @var Customize\Entity\Feature
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Feature", inversedBy="FeatureProducts")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="feature_id", referencedColumnName="id")
     * })
     */
    private $Feature;

    /**
     * @var Eccube\Entity\Product
     * 
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * })
     */
    private $Product;

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
    public function getProductId()
    {
        return $this->product_id;
    }
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
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
    public function getProduct()
    {
        return $this->Product;
    }
    public function setProduct($Product)
    {
        $this->Product = $Product;
        return $this;
    }
}