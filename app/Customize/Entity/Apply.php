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
 * @ORM\Table(name="cmd_apply")
 * @ORM\Entity(repositoryClass="Customize\Repository\ApplyRepository")
 */
class Apply extends AbstractEntity {

    const STATUS_PROCESSING = 0;
    const STATUS_ALLOWED = 1;
    const STATUS_HOLD = 2;
    const STATUS_CANCELED = 3;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @var string
     * @ORM\Column(name="shop_name", type="string", length=255)
     */
    private $shop_name;


    /**
     * @var string
     * @ORM\Column(name="shop_name_kana", type="string", length=255, nullable=true)
     */
    private $shop_name_kana;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_mail", type="string", length=255)
     */
    private $order_mail;

    /**
     * @var string
     * 
     * @ORM\Column(name="login_id", type="string", length=255)
     */
    private $login_id;

    /**
     * @var string
     * 
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    private $company_name;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="postal_code", type="string", length=255, nullable=true)
     */
    private $postal_code;

    /**
     * @var string
     * 
     * @ORM\Column(name="addr01", type="string", length=255, nullable=true)
     */
    private $addr01;
    /**
     * @var string
     * 
     * @ORM\Column(name="addr02", type="string", length=255, nullable=true)
     */
    private $addr02;

    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pref_id", referencedColumnName="id")
     * })
     */
    private $Pref;


    /**
     * @var string
     * 
     * @ORM\Column(name="representative", type="string", length=255, nullable=true)
     * @Eccube\FormAppend
     */
    private $representative;

    /**
     * @var string
     * 
     * @ORM\Column(name="founded_at", type="string", length=255, nullable=true)
     */
    private $founded_at;

    /**
     * @var string
     * 
     * @ORM\Column(name="capital", type="string", length=255, nullable=true)
     */
    private $capital;

    /**
     * @var string
     * 
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @var string
     * 
     * @ORM\Column(name="stripe_id", type="string", length=255, nullable=true)
     */
    private $stripe_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $created_at;

    /**
     * @var boolean
     * @ORM\Column(name="charge_enabled", type="smallint", options={"default" : 0}, nullable=true)
     */
    private $charge_enabled;

    /**
     * @var string
     * 
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;
    
    /**
     * @var string
     * 
     * @ORM\Column(name="status", type="smallint", options={"default" : 0}, nullable=true)
     */
    private $status;

    /**
     * @var string
     * 
     * @ORM\Column(name="exp_online_shop", type="smallint", length=255, nullable=true)
     */
    private $exp_online_shop;

    /**
     * @var string
     * 
     * @ORM\Column(name="open_schedule", type="date", nullable=true)
     */
    private $open_schedule;

    /**
     * @var string
     * @ORM\Column(name="inquiry_content", type="text", nullable=true)
     */
    private $inquiry_content;

    /**
     * @var string
     * @ORM\Column(name="uuid", type="string", nullable=true)
     */
    private $uuid;

    /**
     * @var string
     * @ORM\Column(name="phone_number", type="string", nullable=true)
     */
    private $phone_number;

    public function __construct() {
        $this->created_at = new \DateTime();
        $this->charge_enabled = 0;
    }

    public function getShopNameKana()
    {
        return $this->shop_name_kana;
    }
    public function setShopNameKana($shop_name_kana)
    {
        $this->shop_name_kana = $shop_name_kana;
        return $this;
    }

    public function getUuid() {
        return $this->uuid;
    }
    public function setUuid($uuid) {
        $this->uuid = $uuid;
        return $this;
    }

    public function getPhoneNUmber()
    {
        return $this->phone_number;
    }
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
        return $this;
    }

    public function getInquiryContent()
    {
        return $this->inquiry_content;
    }
    public function setInquiryContent($inquiry_content)
    {
        $this->inquiry_content = $inquiry_content;
        return $this;
    }

    public function getExpOnlineShop()
    {
        return $this->exp_online_shop;
    }
    public function setExpOnlineShop($exp_online_shop)
    {
        $this->exp_online_shop = $exp_online_shop;
        return $this;
    }

    public function getOpenSchedule()
    {
        return $this->open_schedule;
    }
    public function setOpenSchedule($open_schedule)
    {
        $this->open_schedule = $open_schedule;
        return $this;
    }

    public function getStatus()
    {
        if ($this->status == null) return self::STATUS_PROCESSING;
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    public function getStatusLabel() {
        switch($this->status) {
            case self::STATUS_PROCESSING:
                return 'malldevel.admin.apply.status.processig';
            case self::STATUS_ALLOWED:
                return 'malldevel.admin.apply.status.allowed';
            case self::STATUS_HOLD:
                return 'malldevel.admin.apply.status.on_hold';
            case self::STATUS_CANCELED:
                return 'malldevel.admin.apply.status.canceled';
        }
        return 'malldevel.admin.apply.status.processig';
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function isChargeEnabled()
    {
        return $this->charge_enabled > 0;
    }
    public function setChargeEnabled($charge_enabled)
    {
        $this->charge_enabled = $charge_enabled > 0 ? 1 : 0;
    }

    public function getStripeId() 
    {
        return $this->stripe_id;
    }
    public function setStripeId($stripe_id)
    {
        $this->stripe_id = $stripe_id;
        return $this;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getShopName() {
        return $this->shop_name;
    }
    public function setShopName( $shop_name ) {
        $this->shop_name = $shop_name;
        return $this;
    }
    
    public function getDepartment() {
        return $this->department;
    }
    
    public function getLoginId() {
        return $this->login_id;
    }
    public function setLoginId( $login_id ) {
        $this->login_id = $login_id;
        return $this;
    }
    
    public function getPref() {
        return $this->Pref;
    }
    public function setPref( $Pref ) {
        $this->Pref = $Pref;
        return $this;
    }

    public function getPostalCode() {
        return $this->postal_code;
    }
    public function setPostalCode( $postal_code ) {
        $this->postal_code = $postal_code;
        return $this;
    }

    public function setAddr01( $addr01 = null ) {
        $this->addr01 = $addr01;
        return $this;
    }
    public function getAddr01() {
        return $this->addr01;
    }
    public function setAddr02( $addr02 = null ) {
        $this->addr02 = $addr02;
        return $this;
    }
    public function getAddr02() {
        return $this->addr02;
    }
    public function getCompanyName(){
        return $this->company_name ?? $this->name;
    }
    public function setCompanyName($company_name) {
        $this->company_name = $company_name;
        return $this;
    }
    public function setAddress( $address ) {
        $this->address = $address;
        return $this;
    }
    public function getAddress() {
        return $this->address;
    }

    public function getRepresentative() {
        return $this->representative;
    }

    public function setRepresentative( $representative ) {
        $this->representative = $representative;
        return $this;
    }

    public function getFoundedAt() {
        return $this->founded_at;
    }
    public function setFoundedAt($founded_at) {
        $this->founded_at = $founded_at;
        return $this;
    }

    public function getCapital(){
        return $this->capital;
    }
    public function setCapital( $capital ) {
        $this->capital = $capital;
        return $this;
    }
    
    public function setContact( $contact ) {
        $this->contact = $contact;
        return $this;
    }
    public function getContact() {
        return $this->contact;
    }

    public function getId(){
        return $this->id;
    }

    public function setName($name){
        $this->name = $name;
        return $this;
    }
    public function getName(){
        return $this->name;
    }
    public function getOrderMail(){
        return $this->order_mail;
    }
    public function setOrderMail($order_mail){
        $this->order_mail = $order_mail;
        return $this;
    }

    public function setLogo($logo){
        $this->logo = $logo;
        return $this;
    }
    public function getLogo(){
        return $this->logo;
    }

    public function getAssetFolder() {

        return sprintf( "%05d", $this->getId() );
    }
    public function getLogoPath() {
        return $this->getAssetFolder() . '/' . $this->logo;
    }

    public function checkStatus($status) {
        return $this->status === $status;
    }
}