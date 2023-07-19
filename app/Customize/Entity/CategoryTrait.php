<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
use Eccube\Annotation\EntityExtension;
use Doctrine\Common\Collections\Criteria;

/**
 * @EntityExtension("Eccube\Entity\Category")
 */
trait CategoryTrait {
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopCategory", mappedBy="Category", fetch="EXTRA_LAZY")
     */
    private $ShopCategories;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="thumbnail", nullable=true, length=255)
     * @Eccube\FormAppend
     */
    private $thumbnail;

    public function __construct() {
        $this->ShopCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function hasShopCategories() {
        $criteria = Criteria::create()
        ->orderBy(['category_id' => Criteria::ASC])
        ->setFirstResult(0)
        ->setMaxResults(1);

        return $this->ShopCategories->matching($criteria)->count() > 0;
    }

    public function addShopCategory(\Customize\Entity\ShopCategory $ShopCategory) {
        $this->ShopCategories[] = $ShopCategory;
        return $this;
    }

    public function removeShopCategory(\Customize\Entity\ShopCategory $ShopCategory ) {
        return $this->ShopCategories->removeElement($ShopCategory);
    }

    public function getShopCategories() {
        return $this->ShopCategories;
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     * @return $this
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }
}
