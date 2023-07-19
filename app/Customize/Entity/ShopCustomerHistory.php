<?php

namespace Customize\Entity;

use Customize\Entity\Mapped\CustomerHistory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ShopCustomerHistory
 *
 * @ORM\Entity
 */
class ShopCustomerHistory extends CustomerHistory
{
    /**
     * @var Shop
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop")
     */
    protected $Shop;

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->Shop;
    }

    /**
     * @param Shop $Shop
     * @return $this
     */
    public function setShop(Shop $Shop)
    {
        $this->Shop = $Shop;

        return $this;
    }
}
