<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Eccube\Annotation as Eccube;
use Eccube\Annotation\EntityExtension;
use Customize\Entity\Apply;

/**
 * @EntityExtension("Eccube\Entity\Member")
 */
trait MemberTrait {
    
    /**
     * @var Shop|null
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    /**
     * @var integer
     * 
     * @ORM\Column(name="apply_id",  type="integer", options={"unsigned" : true}, nullable=true)
     */
    private $apply_id;

    /**
     * @var Apply|null
     * 
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Apply")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="apply_id", referencedColumnName="id")
     * })
     */
    private $Apply;

    public function getShop(){
        return $this->Shop;
    }

    /**
     * @param Shop|null $shop
     * @return $this
     */
    public function setShop(Shop $Shop = null) {
        $this->Shop = $Shop;
        return $this;
    }
    
    public function getApplyId()
    {
        return $this->apply_id;
    }
    public function setApplyId($apply_id)
    {
        $this->apply_id = $apply_id;
        return $this;
    }

    public function getApply()
    {
        return $this->Apply;
    }
    public function setApply(Apply $apply)
    {
        $this->Apply = $apply;
        return $this;
    }
    /**
     * @return bool
     */
    public function hasShop(){
        return !is_null($this->Shop);
    }

    public function copyFromApply(Apply $Apply)
    {
        $this->name = $Apply->getName();
        $this->department = $Apply->getCompanyName();
        $this->login_id = $Apply->getLoginId();
        $this->apply_id = $Apply->getId();
        $this->Apply = $Apply;

    }
    public function getRole() {
        
        if( $this->Authority->getId() < 2) {
            return 'ROLE_ADMIN';
        } else if ($this->Authority->getId() == 2 ){
            return 'ROLE_SHOP_OWNER';
        } else {
            return 'ROLE_APPLICANT';
        }
    }
    
}