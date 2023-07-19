<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Annotation as Eccube;
use Symfony\Bridge\Doctrine\Validator\Contraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;


/**
 * Shop
 * 
 * @ORM\Table(name="cmd_stripe_config")
 * @ORM\Entity(repositoryClass="Customize\Repository\StripeConfigRepository")
 */
class StripeConfig extends AbstractEntity {
    
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="publishable_key", type="string", length=255, nullable=true)
     */
    private $publishable_key;

    /**
     * @var string
     *
     * @ORM\Column(name="secret_key", type="string", length=255, nullable=true)
     */
    private $secret_key;

    
    /**
     * @var int
     *
     * @ORM\Column(name="is_capture_on", type="integer", options={"default" : 0}, nullable=true)
     */
    private $is_capture_on = 0;

    /**
     * @var int
     * 
     * @ORM\Column(name="signature", type="string", length=255, nullable=true)
     */
    private $signature;

    /**
     * @var string
     *
     * @ORM\Column(name="stripe_fees_percent", type="decimal", precision=12, scale=2, options={"unsigned":true,"default":0})
     */
    private $stripe_fees_percent = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="application_fees_percent", type="decimal", precision=12, scale=2, options={"unsigned":true,"default":0})
     */
    private $application_fees_percent = 0;

    public function getSignature()
    {
        return $this->signature;
    }
    public function setSignature($signature)
    {
        $this->signature = $signature;
        return $this;
    }

    
    public function getPublishableKey()
    {
        return $this->publishable_key;
    }
    public function setPublishableKey($publishable_key) 
    {
        $this->publishable_key = $publishable_key;
        return $this;
    }
    public function getSecretKey()
    {
        return $this->secret_key;
    }
    public function setSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
        return $this;
    }
    public function isCaptureOn()
    {
        return $this->is_capture_on;
    }
    public function setIsCaptureOn($is_capture_on)
    {
        $this->is_capture_on = $is_capture_on;
        return $this;
    }

    public function getStripeFeesPercent() 
    {
        return $this->stripe_fees_percent;
    }
    public function setStripeFeesPercent($stripe_fees_percent) 
    {
        $this->stripe_fees_percent = $stripe_fees_percent;
        return $this;
    }
    public function getApplicationFeesPercent() 
    {
        return $this->application_fees_percent;
    }
    public function setApplicationFeesPercent($application_fees_percent)
    {
        $this->application_fees_percent = $application_fees_percent;
        return $this;
    }
    
}