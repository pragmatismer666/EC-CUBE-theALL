<?php

namespace Customize\Entity;

use Customize\Entity\Master\ShopStatus;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Annotation as Eccube;
use Symfony\Bridge\Doctrine\Validator\Contraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Customize\Entity\Apply;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Entity\Member;
use Customize\Entity\ShopSeries;
use Customize\Entity\Master\Series;
use Customize\Entity\ShopPhoto;
/**
 * Shop
 * 
 * @ORM\Table(name="cmd_shop")
 * @ORM\Entity(repositoryClass="Customize\Repository\ShopRepository")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 */
class Shop extends AbstractEntity {
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
     * @ORM\Column(name="kana", type="string", length=255, nullable=true)
     */
    private $kana;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_mail", type="string", length=255)
     */
    private $order_mail;

    /**
     * @var string
     * 
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     * @Eccube\FormAppend
     */
    private $logo;


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
     * @Eccube\FormAppend
     */
    private $capital;

    /**
     * @var string
     * 
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     * @Eccube\FormAppend
     */
    private $contact;

    
    /**
     * @var boolean
     * @ORM\Column(name="is_deleted", type="smallint", options={"default" : 0}, nullable=true)
     */
    private $is_deleted;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopCategory", mappedBy="Shop", cascade={"persist", "remove"})
     */
    private $ShopCategories;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopSeries", mappedBy="Shop", cascade={"persist", "remove"})
     */
    private $ShopSerieses;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopBlog", mappedBy="Shop", cascade={"persist", "remove"})
     */
    private $ShopBlogs;

    /**
     * @var string
     * 
     * @ORM\Column(name="hp", type="blob", nullable=true)
     */
    private $hp;

    /**
     * @var \Customize\Entity\Katakana
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Katakana")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="kata_id", referencedColumnName="id")
     * })
     */
    private $Katakana;
    // /**
    //  * @var \Customize\Entity\Master\Series
    //  *
    //  * @ORM\ManyToOne(targetEntity="Customize\Entity\Master\Series")
    //  * @ORM\JoinColumns({
    //  *   @ORM\JoinColumn(name="series_id", referencedColumnName="id")
    //  * })
    //  */
    // private $Series;

    /**
     * @var \Customize\Entity\Tokusho
     *
     * @ORM\OneToOne(targetEntity="Customize\Entity\Tokusho", mappedBy="Shop", cascade={"persist", "remove"})
     */
    private $Tokusho;

    /**
     * @var integer
     * 
     * @ORM\Column(name="apply_id",  type="integer", options={"unsigned" : true}, nullable=true)
     */
    private $apply_id;

    /**
     * @var string
     * 
     * @ORM\Column(name="stripe_id", type="string", length=255, nullable=true)
     */
    private $stripe_id;

    /**
     * @var \Customize\Entity\Master\ShopStatus
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Master\ShopStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shop_status_id", referencedColumnName="id")
     * })
     */
    private $Status;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="Eccube\Entity\Member", mappedBy="Shop")
     */
    private $Members;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopIdentityDoc", mappedBy="Shop", cascade={"remove"})
     * @ORM\OrderBy({
     *     "sort_no"="ASC"
     * })
     */
    private $ShopIdentityDocs;

    /**
     * @var string|null
     *
     * @ORM\Column(name="delivery_free_amount", type="decimal", precision=12, scale=2, nullable=true, options={"unsigned":true})
     */
    private $delivery_free_amount;

    /**
     * @var int|null
     *
     * @ORM\Column(name="delivery_free_quantity", type="integer", nullable=true, options={"unsigned":true})
     */
    private $delivery_free_quantity;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="gmap_enabled", type="boolean", options={"default":false})
     */
    private $gmap_enabled = false;

    /**
     * @var string
     * @ORM\Column(name="phone_number", type="string", nullable=true)
     */
    private $phone_number;

    
    /**
     * @var string
     * @ORM\Column(name="intro", type="text", nullable=true)
     */
    private $intro;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Customize\Entity\ShopPhoto", mappedBy="Shop", cascade={"persist", "remove"})
     */
    private $ShopPhotos;
    
    public function __construct() {
        $this->ShopCategories = new ArrayCollection();
        
        if ( \is_null($this->is_deleted) )
        {
            $this->is_deleted = 0;
        }
        $this->Members = new ArrayCollection();
        $this->ShopIdentityDocs = new ArrayCollection();
        $this->ShopSerieses = new ArrayCollection();
        $this->ShopPhotos = new ArrayCollection();
    }

    public function getKana()
    {
        return $this->kana;
    }
    public function setKana($kana)
    {
        $this->kana = $kana;
        return $this;
    }
    public function getIntro()
    {
        return $this->intro;
    }
    public function setIntro($intro)
    {
        $this->intro = $intro;
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

    public function getShopPhotos() {
        return $this->ShopPhotos;
    }
    public function setShopPhotos($ShopPhotos) {
        $this->ShopPhotos = $ShopPhotos;
        return $this;
    }

    public function addShopPhoto(ShopPhoto $ShopPhoto)
    {
        $this->ShopPhotos[] = $ShopPhoto;
        return $this;
    }
    public function removeShopPhoto($ShopPhoto)
    {
        return $this->ShopPhotos->removeElement($ShopPhoto);
    }

    public function getGmapEnabled() {
        return $this->gmap_enabled;
    }
    public function setGmapEnabled($gmap_enabled) {
        $this->gmap_enabled = $gmap_enabled;
        return $this;
    }

    public function getDeliveryFreeAmount() {
        return $this->delivery_free_amount;
    }
    public function setDeliveryFreeAmount($delivery_free_amount) {
        $this->delivery_free_amount = $delivery_free_amount;
        return $this;
    }
    public function getDeliveryFreeQuantity() {
        return $this->delivery_free_quantity;
    }
    public function setDeliveryFreeQuantity($delivery_free_quantity) {
        $this->delivery_free_quantity = $delivery_free_quantity;
        return $this;
    }

    public function getMembers() 
    {
        return $this->Members->toArray();
    }
    public function addMember(Member $Member)
    {
        $this->Members[] = $Member;
        return $this;
    }
    public function removeMember(Member $Member)
    {
        $this->Members->removeElement($Member);
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
    public function getTokusho()
    {
        return $this->Tokusho;
    }
    public function setTokusho($Tokusho)
    {
        $this->Tokusho = $Tokusho;
        return $this;
    }

    public function isCommitmentSeries()
    {
        foreach($this->ShopSerieses as $ShopSerieses) {
            if ($ShopSerieses->getSeriesId() == Series::SUPER_GENERATION) return true;
        }
        return false;
    }

    public function getShopSerieses()
    {
        return $this->ShopSerieses;
    }
    public function addShopSeries(\Customize\Entity\ShopSeries $ShopSeries) {
        return $this->ShopSerieses[] = $ShopSeries;
    }
    public function removeShopSeries(\Customize\Entity\ShopSeries $ShopSeries) {
        return $this->ShopSerieses->removeElement($ShopSeries);
    }

    public function getSerieses()
    {
        $Serieses = [];
        foreach($this->ShopSerieses as $ShopSeries)
        {
            $Serieses[] = $ShopSeries->getSeries();
        }
        return $Serieses;
    }

    public function getKatakana() {
        return $this->Katakana;
    }
    public function setKatakana( $Katakana ) {
        $this->Katakana = $Katakana;
        return $this;
    }

    public function getHp() {
        if ( !empty($this->hp)) {
            return \stream_get_contents( $this->hp );
        }
        return "";
    }
    public function setHp( $hp ) {
        $this->hp = $hp;
        return $this;
    }

    public function getShopCategories() {
        return $this->ShopCategories;
    }

    public function addShopCategory(\Customize\Entity\ShopCategory $Category) {
        return $this->ShopCategories[] = $Category;
    }
    public function removeShopCategory(\Customize\Entity\ShopCategory $Category) {
        return $this->ShopCategories->removeElement($Category);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShopBlogs() {
        return $this->ShopBlogs;
    }

    /**
     * @return ShopStatus
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param ShopStatus $Status
     * @return $this
     */
    public function setStatus(ShopStatus $Status)
    {
        $this->Status = $Status;

        return $this;
    }

    public function isDeleted() {
        return $this->is_deleted > 0;
    }
    public function setIsDeleted($is_deleted) {
        $this->is_deleted = $is_deleted > 0;
        return $this;
    }
    
    

    public function getPref() {
        return $this->Pref;
    }
    public function setPref( $Pref ) {
        $this->Pref = $Pref;
        return $this;
    }

    public function getAddress() 
    {
        return "〒" . $this->postal_code . " " . $this->addr01 . " " . $this->addr02;
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

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return !$this->isDeleted() &&
            $this->Status &&
            $this->Status->getId() == ShopStatus::DISPLAY_SHOW;
    }

    public function getAssetFolder() {

        return sprintf( "%09d", $this->getId() );
    }
    public function getLogoPath() {
        return $this->getAssetFolder() . '/' . $this->logo;
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
    public function isStripeRegistered() 
    {
        return !empty($this->stripe_id);
    }

    public function addShopIdentityDoc(ShopIdentityDoc $ShopIdentityDoc)
    {
        $this->ShopIdentityDocs[] = $ShopIdentityDoc;
        return $this;
    }
    public function removeShopIdentityDoc($ShopIdentityDoc)
    {
        return $this->ShopIdentityDocs->removeElement($ShopIdentityDoc);
    }

    public function getShopIdentityDocs()
    {
        return $this->ShopIdentityDocs;
    }

    public function copyFromApply(Apply $Apply)
    {
        $this->name = $Apply->getShopName();
        $this->kana = $Apply->getShopNameKana();
        $this->order_mail = $Apply->getOrderMail();
        $this->company_name = $Apply->getCompanyName();
        $this->postal_code = $Apply->getPostalCode();
        $this->addr01 = $Apply->getAddr01();
        $this->addr02 = $Apply->getAddr02();
        $this->Pref = $Apply->getPref();
        $this->representative = $Apply->getRepresentative();
        $this->founded_at = $Apply->getFoundedAt();
        $this->capital = $Apply->getCapital();
        $this->contact = $Apply->getContact();
        $this->stripe_id = $Apply->getStripeId();
        $this->apply_id = $Apply->getId();
        $this->phone_number = $Apply->getPhoneNumber();
    }
}