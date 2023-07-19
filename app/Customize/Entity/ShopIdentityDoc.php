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
 * @ORM\Table(name="cmd_shop_identity_doc")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Customize\Repository\ShopIdentityDocRepository")
 */

class ShopIdentityDoc extends AbstractEntity {
    const STATUS_SHOW = "show";
    const STATUS_HIDE = "hide";
    const STATUS_BLOCKED = "blocked";
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", options={"unsigned" : true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="file_name", type="string", length=255)
     */
    private $file_name;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_no", type="smallint", options={"unsigned":true})
     */
    private $sort_no;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \Customize\Entity\Shop
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop", inversedBy="ShopIdentityDocs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    public function getId() {
        return $this->id;
    }
    public function getFileName()
    {
        return $this->file_name;
    }
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
        return $this;
    }

    public function getSortNo()
    {
        return $this->sort_no;
    }
    public function setSortNo($sort_no)
    {
        $this->sort_no = $sort_no;
    }

    public function getCreateDate()
    {
        return $this->create_date;
    }
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }
    public function getShop()
    {
        return $this->Shop;
    }
    public function setShop($Shop)
    {
        $this->Shop = $Shop;
        return $this;
    }
    
}