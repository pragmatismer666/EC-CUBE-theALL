<?php


namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Order;
use Eccube\Entity\AbstractEntity;

/**
 * Order
 * 
 * @ORM\Table(name="cmd_stripe_order")
 * @ORM\Entity(repositoryClass="Customize\Repository\StripeOrderRepository")
 */
class StripeCreditOrder extends AbstractEntity 
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Order
     *
     * @ORM\OneToOne(targetEntity="Eccube\Entity\Order")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    private $Order;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_token", type="string", nullable=true)
     */
    private $stripe_payment_intent_id; //stripe_token => stripe_payment_intent_id

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_customer_id_for_guest_checkout", type="string", nullable=true)
     */
    private $stripe_customer_id_for_guest_checkout;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_charge_id", type="string", nullable=true)
     */
    private $stripe_charge_id;

    /**
     * @var int
     *
     * @ORM\Column(name="is_charge_captured", type="integer", options={"default" : 0}, nullable=true)
     */
    private $is_charge_captured;

    /**
     * @var int
     *
     * @ORM\Column(name="is_charge_refunded", type="integer", options={"default" : 0}, nullable=true)
     */
    private $is_charge_refunded;

    /**
     * @var int
     *
     * @ORM\Column(name="selected_refund_option", type="integer", options={"unsigned":true,"default":0,"comment":"1=full_refund, 2=refund_full_amount_minus_stripe_fee, 3=partial_refund"}, nullable=true)
     */
    private $selected_refund_option;

    /**
     * @var string
     *
     * @ORM\Column(name="refunded_amount", type="decimal", precision=12, scale=2, options={"unsigned":true,"default":0})
     */
    private $refunded_amount = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Order.
     *
     * @param Order $Order
     *
     * @return $this
     */
    public function setOrder(Order $Order)
    {
        $this->Order = $Order;

        return $this;
    }

    /**
     * Get Order.
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->Order;
    }


    /**
     * @return string
     */
    public function getStripePaymentIntentId()
    {
        return $this->stripe_payment_intent_id;
    }

    /**
     * @param string $stripe_payment_intent_id
     *
     * @return $this;
     */
    public function setStripePaymentIntentId($stripe_payment_intent_id)
    {
        $this->stripe_payment_intent_id = $stripe_payment_intent_id;

        return $this;
    }

    /**
     * @return string
     */
    public function getStripeCustomerIdForGuestCheckout()
    {
        return $this->stripe_customer_id_for_guest_checkout;
    }

    /**
     * @param string $stripe_customer_id_for_guest_checkout
     *
     * @return $this;
     */
    public function setStripeCustomerIdForGuestCheckout($stripe_customer_id_for_guest_checkout)
    {
        $this->stripe_customer_id_for_guest_checkout = $stripe_customer_id_for_guest_checkout;

        return $this;
    }

    /**
     * @return string
     */
    public function getStripeChargeId()
    {
        return $this->stripe_charge_id;
    }

    /**
     * @param string $stripe_charge_id
     *
     * @return $this;
     */
    public function setStripeChargeId($stripe_charge_id)
    {
        $this->stripe_charge_id = $stripe_charge_id;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsChargeCaptured()
    {
        return $this->is_charge_captured > 0? true:false;
    }

    /**
     * @param boolean $is_charge_captured
     *
     * @return $this;
     */
    public function setIsChargeCaptured($is_charge_captured)
    {
        $this->is_charge_captured = $is_charge_captured? 1:0;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsChargeRefunded()
    {
        return $this->is_charge_refunded > 0? true:false;
    }

    /**
     * @param boolean $is_charge_refunded
     *
     * @return $this;
     */
    public function setIsChargeRefunded($is_charge_refunded)
    {
        $this->is_charge_refunded = $is_charge_refunded? 1:0;

        return $this;
    }

    /**
     * @return int
     */
    public function getSelectedRefundOption()
    {
        return $this->selected_refund_option;
    }

    /**
     * @param int $selected_refund_option
     *
     * @return $this;
     */
    public function setSelectedRefundOption($selected_refund_option)
    {
        $this->selected_refund_option = $selected_refund_option;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefundedAmount()
    {
        return $this->refunded_amount;
    }

    /**
     * @param string $refunded_amount
     *
     * @return $this;
     */
    public function setRefundedAmount($refunded_amount)
    {
        $this->refunded_amount = $refunded_amount;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     *
     * @return $this;
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}