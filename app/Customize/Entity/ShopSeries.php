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
 * @ORM\Table(name="cmd_shop_series")
 * @ORM\Entity(repositoryClass="Customize\Repository\ShopSeriesRepository")
 */
class ShopSeries extends AbstractEntity {
    /**
     * @var int
     * @ORM\Column(name="shop_id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $shop_id;

    /**
     * @var int
     * @ORM\Column(name="series_id", type="smallint", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $series_id;

    /**
     * @var \Customize\Entity\Shop
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop", inversedBy="ShopSerieses")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    /**
     * @var \Customize\Entity\Master\Series
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Master\Series", inversedBy="ShopSerieses")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="series_id", referencedColumnName="id")
     * })
     */
    private $Series;

    public function getShopId() {
        return $this->shop_id;
    }
    public function setShopId($shop_id) {
        $this->shop_id = $shop_id;
        return $this;
    }
    public function getSeriesId() {
        return $this->series_id;
    }

    public function setSeriesId($series_id) {
        $this->series_id = $series_id;
        return $this;
    }
    public function getShop() {
        return $this->Shop;
    }
    public function setShop($Shop) {
        $this->Shop = $Shop;
        return $this;
    }

    public function getSeries() {
        return $this->Series;
    }
    public function setSeries( $Series ) {
        $this->Series = $Series;
        return $this;
    }
}