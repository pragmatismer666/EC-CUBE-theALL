<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Eccube\Annotation as Eccube;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait {
    
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
    /**
     * @return Shop|null
     */
    public function getShopFromItems()
    {
        $ShopFromItems = null;
        $OrderItems = $this->getOrderItems();
        if (!empty($OrderItems)) {
            /** @var Shop[] $Shops */
            $Shops = [];
            foreach ($OrderItems as $OrderItem) {
                $Product = $OrderItem->getProduct();
                if (is_null($Product)) {
                    continue;
                }
                $Shop = $Product->getShop();
                if (is_null($Shop)) {
                    continue;
                }
                $Shops[$Shop->getId()] = $Shop;
            }
            if (count($Shops) === 1) { // 受注商品が全て同一ショップの場合
                $ShopFromItems = current($Shops);
            }
        }

        return $ShopFromItems;
    }
}