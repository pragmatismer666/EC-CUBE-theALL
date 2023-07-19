<?php

namespace Customize\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProductCustomerHistory
 *
 * @ORM\Entity
 * @ORM\Table(name="cmd_buyer_commitment")
 * @ORM\Entity(repositoryClass="Customize\Repository\BuyerCommitmentRepository")
 * BuyerCommitmentRepository
 */
class BuyerCommitment extends AbstractEntity
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
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;
    

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
     */
    protected $Product;


    protected $asset_folder;

    public function getId()
    {
        return $this->id;
    }
    
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

    public function getImage()
    {
        return $this->image;
    }
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
    public function getImagePath()
    {
        if (!$this->image) return null;
        if (!$this->asset_folder) {
            $this->asset_folder = $this->getProduct()->getShop()->getAssetFolder();
        }
        return $this->asset_folder . '/' . $this->image;
    }

    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
}
