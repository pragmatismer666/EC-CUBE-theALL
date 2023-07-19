<?php

namespace Customize\Entity;

use Customize\Entity\Mapped\CustomerHistory;
use Eccube\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProductCustomerHistory
 *
 * @ORM\Entity
 */
class ProductCustomerHistory extends CustomerHistory
{
    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
     */
    protected $Product;

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * @param Product $Product
     * @return $this
     */
    public function setProduct(Product $Product)
    {
        $this->Product = $Product;

        return $this;
    }
}
