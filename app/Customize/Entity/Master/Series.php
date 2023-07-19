<?php

namespace Customize\Entity\Master;

use Customize\Entity\Master\BlogType;
use Customize\Entity\BlogTag;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Master\AbstractMasterEntity;
/**
 * Class Series
 *
 * @ORM\Table(name="cme_series")
 * @ORM\Entity(repositoryClass="Customize\Repository\Master\SeriesRepository")
 */
class Series extends AbstractMasterEntity
{
    const DC2MALL = 1;
    const KODAWARI = 2;
    const SPECIALTY = 3;
    const SUPER_GENERATION = 4;

    /** {@inheritdoc} */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopSeries", mappedBy="Shop", cascade={"persist", "remove"})
     */
    private $ShopSerieses;

    /**
     * @var string
     *
     * @ORM\Column(name="desciption", type="blob", nullable=true)
     */
    private $description;

    /**
     * @var string
     * 
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     */
    private $thumbnail;

    public function __construct()
    {
        $this->ShopSerieses = new ArrayCollection();
    }
    
    public function getDescription()
    {
        if ( !empty($this->description)) {
            return \stream_get_contents( $this->description );
        }
        return "";
    }
    public function setDescription($description)
    {
        $this->description = $description;
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

    public function getShopSerieses()
    {
        return $this->ShopSerieses;
    }
}
