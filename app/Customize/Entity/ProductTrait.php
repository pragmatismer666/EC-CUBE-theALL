<?php

namespace Customize\Entity;

use Customize\Entity\Master\ShopStatus;
use Doctrine\ORM\Mapping as ORM;
// use Eccube\Annotation as Eccube;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait {
    
    /**
     * @var Shop|null
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    public function getShop(){
        return $this->Shop;
    }

    /**
     * @param Shop|null $shop
     * @return $this
     */
    public function setShop(Shop $Shop = null) {
        $this->Shop = $Shop;
        return $this;
    }
    public function getAssetFolder() 
    {
        return $this->Shop->getAssetFolder();
    }

    public function isEnabled()
    {
        return $this->getShop() && $this->getShop()->isEnabled() && $this->isEnable();
    }

    public function getAsArray()
    {
        $res = [];
        $res['id'] = $this->id;
        $res['name'] = $this->name;
        $res['image'] = $this->getMainFileName();
        if (!$res['image']) {
            $res['image'] = '_no_image_product.png';
        } else {
            $res['image'] = $res['image']->getFileName();
        }
        $res['code'] = $this->getCodeString()??"";
        $res['price'] = $this->getPriceString()??0;
        $res['stock'] = $this->getStockString()??"";
        return $res;
    }
    public function getCodeString()
    {
        $code_min = $this->getCodeMin();
        $code_max = $this->getCodeMax();

        if ($code_min != $code_max) {
            return $code_min . trans('admin.common.separator__range') . $code_max;
        } else {
            return $code_min;
        }
    }
    public function getPriceString()
    {
        $price_min = $this->getPrice02Min();
        $price_max = $this->getPrice02Max();

        if ($price_min != $price_max) {
            return $price_min . trans('admin.common.separator__range') . $price_max;
        } else {
            return $price_min;
        }
    }
    public function getStockString(){
        if ($this->hasProductClass()) {
            return "multiple";
        } else {
            $stockunlimited_min = $this->getStockunlimitedMin();
            if ($stockunlimited_min) {
                return trans('admin.product.stock_unlimited__short');
            } else {
                return $this->getStockMin();
            }
        }
    }
}