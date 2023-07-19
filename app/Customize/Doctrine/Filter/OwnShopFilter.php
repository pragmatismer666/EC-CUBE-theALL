<?php

namespace Customize\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Eccube\Entity\ClassName;
use Eccube\Entity\Delivery;
use Eccube\Entity\Order;
use Eccube\Entity\Product;
use Customize\Entity\Shop;
use Customize\Entity\ShopBlog;

class OwnShopFilter extends SQLFilter
{
    private $shopId = null;
    private $saleTypeId = null;

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $filterClasses = [
            Product::class,
            ClassName::class,
            Order::class,
            Delivery::class,
            ShopBlog::class,
        ];
        if (class_exists("Plugin\ProductReview4\Entity\ProductReview")) {
            $filterClasses[] = "Plugin\ProductReview4\Entity\ProductReview";
        }
        if (!is_null($this->shopId)) {
            if (in_array(
                $targetEntity->reflClass->getName(),
                $filterClasses
            )) {
                return $targetTableAlias.'.shop_id = '.$this->getParameter('shop_id');
            }
        }
        return '';
    }

    public function setShopId(Shop $shop)
    {
        $this->shopId = $shop->getId();
        $this->setParameter('shop_id', $this->shopId);
    }
}
