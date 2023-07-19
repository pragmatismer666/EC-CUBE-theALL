<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
use Customize\Entity\Shop;

/**
 * @Eccube\EntityExtension("Eccube\Entity\ClassCategory")
 */
trait ClassCategoryTrait
{
    
    /**
     * @return Shop|null
     */
    public function getShop()
    {
        if ($this->getClassName()) {
            return $this->getClassName()->getShop();
        }
        return null;
    }

    
}
