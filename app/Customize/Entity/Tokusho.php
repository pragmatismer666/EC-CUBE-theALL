<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Customize\Entity\Shop;

/**
 * Class Tokusho
 *
 * @ORM\Table(name="cmd_tokusho")
 * @ORM\Entity(repositoryClass="Customize\Repository\TokushoRepository")
 */
class Tokusho extends AbstractEntity
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
     * @var Shop|null
     * 
     * @ORM\OneToOne(targetEntity="Customize\Entity\Shop")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery", type="text", nullable=true)
     */
    private $delivery;
    
    /**
     * @var string
     *
     * @ORM\Column(name="delivery_time", type="text", nullable=true)
     */
    private $delivery_time;

    /**
     * @var string
     *
     * @ORM\Column(name="receipt", type="text", nullable=true)
     */
    private $receipt;
   
    /**
     * @var string
     * 
     * @ORM\Column(name="rcx_overview", type="text", nullable=true)
     */
    protected $rcx_overview;

    /**
     * @var string
     * 
     * @ORM\Column(name="cancel", type="text", nullable=true)
     */
    protected $cancel;

    /**
     * @var string
     * 
     * @ORM\Column(name="refund", type="text", nullable=true)
     */
    protected $refund;

    /**
     * @var string
     * 
     * @ORM\Column(name="exchange", type="text", nullable=true)
     */
    protected $exchange;

    /**
     * @var string
     * 
     * @ORM\Column(name="payment_method", type="text", nullable=true)
     */
    protected $payment_method;
   

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getPaymentMethod()
    {
        return $this->payment_method;
    }
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->Shop;
    }

    public function setShop($Shop)
    {
        $this->Shop = $Shop;
        return $this;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }
    public function setDelivery($delivery) 
    {
        $this->delivery = $delivery;
        return $this;
    }

    public function getDeliveryTime()
    {
        return $this->delivery_time;
    }

    public function setDeliveryTime($delivery_time)
    {
        $this->delivery_time = $delivery_time;
        return $this;
    }
    public function getReceipt()
    {
        return $this->receipt;
    }
    public function setReceipt($receipt)
    {
        $this->receipt = $receipt;
        return $this;
    }

    public function getRcxOverview()
    {
        return $this->rcx_overview;
    }
    public function setRcxOverview($rcx_overview)
    {
        $this->rcx_overview = $rcx_overview;
        return $this;
    }

    public function getCancel()
    {
        return $this->cancel;
    }
    public function setCancel($cancel)
    {
        $this->cancel = $cancel;
        return $this;
    }

    public function getRefund()
    {
        return $this->refund;
    }
    public function setRefund($refund)
    {
        $this->refund = $refund;
        return $this;
    }
    public function getExchange()
    {
        return $this->exchange;
    }
    public function setExchange($exchange)
    {
        $this->exchange = $exchange;
        return $this;
    }
}
