<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * Class Feature
 *
 * @ORM\Table(name="cmd_feature")
 * @ORM\Entity(repositoryClass="Customize\Repository\FeatureRepository")
 */
class Feature extends AbstractEntity
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean", options={"default":true}, nullable=true)
     */
    private $visible;

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
     * @var string
     * 
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     */
    private $thumbnail;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Customize\Entity\FeatureProduct", mappedBy="Feature", cascade={"persist","remove"})
     */
    private $FeatureProducts;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Customize\Entity\FeatureShop", mappedBy="Feature", cascade={"persist","remove"})
     */
    private $FeatureShops;

    public function __construct()
    {
        $this->FeatureProducts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->FeatureShops = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function getFeatureProducts()
    {
        return $this->FeatureProducts;
    }
    public function addFeatureProduct(FeatureProduct $FeatureProduct)
    {
        return $this->FeatureProducts[] = $FeatureProduct;
    }
    public function removeFeatureProduct(FeatureProduct $FeatureProduct)
    {
        return $this->FeatureProducts->removeElement($FeatureProduct);
    }


    public function getFeatureShops()
    {
        return $this->FeatureShops;
    }
    public function addFeatureShop(FeatureShop $FeatureShop)
    {
        return $this->FeatureShops[] = $FeatureShop;
    }
    public function removeFeatureShop(FeatureShop $FeatureShop)
    {
        return $this->FeatureShops->removeElement($FeatureShop);
    }

    public function getShops()
    {
        if ($this->FeatureShops) {
            $Shops = [];
            foreach($this->FeatureShops as $FeatureShop) {
                $Shops[] = $FeatureShop->getShop();
            }
            return $Shops;
        }
        return [];
    }
    

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function isVisible()
    {
        return $this->visible > 0;
    }
    public function setVisible($visible)
    {
        $this->visible = $visible > 0 ? 1 : 0;
        return $this;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
       return $this->update_date;
    }
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;
        return $this;
    }

    public function getThumbnail() 
    {
        return $this->thumbnail;
    }
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }
    public function getThumbnailPath() 
    {
        return 'admin/' . $this->thumbnail;
    }
}
