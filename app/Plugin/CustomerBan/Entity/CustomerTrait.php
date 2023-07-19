<?php

namespace Plugin\CustomerBan\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
use Plugin\CustomerBan\Entity\CustomerBan;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @var CustomerBan|null
     *
     * @ORM\OneToOne(targetEntity="Plugin\CustomerBan\Entity\CustomerBan", mappedBy="Customer", cascade={"remove"})
     */
    private $CustomerBan;

    /**
     * @return CustomerBan|null
     */
    public function getCustomerBan()
    {
        return $this->CustomerBan;
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return !!$this->CustomerBan;
    }

    /**
     * @param CustomerBan $CustomerBan
     * @return $this
     */
    public function setCustomerBan(CustomerBan $CustomerBan)
    {
        $this->CustomerBan = $CustomerBan;

        return $this;
    }
}
