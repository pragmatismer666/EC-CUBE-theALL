<?php

namespace Customize\Services\Cart;

use Eccube\Service\Cart\CartItemAllocator;
use Eccube\Entity\CartItem;

class ShopAndSaleTypeCartAllocator implements CartItemAllocator
{
    public function allocate(CartItem $item)
    {
        $ProductClass = $item->getProductClass();
        
        if (!$ProductClass || !($ProductClass->getSaleType())) {
            throw new \InvalidArgumentException('ProductClass\SaleType not found');
        }
        
        $Product = $ProductClass->getProduct();
        if (!$Product || !($Product->getShop())) {
            throw new \InvalidArgumentException("This product's shop not found");
        }

        $Shop = $Product->getShop();

        $sales_type_id = (string) $ProductClass->getSaleType()->getId();
        $shop_id = $Shop->getId();

        $spec = $shop_id . "__" . $sales_type_id;

        return $spec;
    }
}