<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Annotation as Eccube;
use Symfony\Bridge\Doctrine\Validator\Contraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;


/**
 * Shop
 * 
 * @ORM\Table(name="cmd_shop_category")
 * @ORM\Entity(repositoryClass="Customize\Repository\ShopCategoryRepository")
 */
class ShopCategory extends AbstractEntity {
    /**
     * @var int
     * @ORM\Column(name="shop_id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $shop_id;

    /**
     * @var int
     * @ORM\Column(name="category_id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $category_id;

    /**
     * @var \Customize\Entity\Shop
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop", inversedBy="ShopCategories")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    /**
     * @var \Eccube\Entity\Category
     * 
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category", inversedBy="ShopCategories")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $Category;

    public function setShopId($shop_id) {
        $this->shop_id = $shop_id;
        return $this;
    }
    public function getShopId() {
        return $this->shop_id;
    }
    public function getCategoryId() {
        return $this->category_id;
    }

    public function setCategoryId($category_id) {
        $this->category_id = $category_id;
        return $this;
    }
    public function getShop() {
        return $this->Shop;
    }
    public function setShop($Shop) {
        $this->Shop = $Shop;
        return $this;
    }
    public function getCategory() {
        return $this->Category;
    }
    public function setCategory( $Category ) {
        $this->Category = $Category;
        return $this;
    }
}