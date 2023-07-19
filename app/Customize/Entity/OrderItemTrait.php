<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Eccube\Annotation as Eccube;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\OrderItem")
 */
trait OrderItemTrait {

    public function getShop() {
        return $this->Order->getShop();
    }
}