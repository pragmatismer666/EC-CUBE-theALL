<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @var string
     * 
     * @ORM\Column(name="stripe_customer_id", type="string", nullable=true)
     */
    private $stripe_customer_id;

    /**
     * @var integer
     * 
     * @ORM\Column(name="card_saved", type="smallint", options={"default" : 0}, nullable=true)
     */
    private $card_saved;

    public function getStripeCustomerId() 
    {
        return $this->stripe_customer_id;
    }
    public function setStripeCustomerId($stripe_customer_id)
    {
        $this->stripe_customer_id = $stripe_customer_id;
        return $this;
    }
    public function isCardSaved()
    {
        return $this->card_saved > 0;
    }
    public function setCardSaved($card_saved) 
    {
        $this->card_saved = $card_saved > 0 ? 1 : 0;
    }
    public function isStripeRegistered()
    {
        return !empty($this->stripe_customer_id);
    }
}
