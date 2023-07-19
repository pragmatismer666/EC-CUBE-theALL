<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Eccube\Annotation as Eccube;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait {
    
    public function getShop(){
        if ($this->getProduct()) {
            return $this->getProduct()->getShop();
        }
        return null;
    }

    
}