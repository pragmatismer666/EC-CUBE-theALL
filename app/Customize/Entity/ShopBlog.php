<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * Class ShopBlog
 *
 * @ORM\Table(name="cmd_shop_blog")
 * @ORM\Entity(repositoryClass="Customize\Repository\ShopBlogRepository")
 */
class ShopBlog extends AbstractEntity
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
     * @var int
     * @ORM\Column(name="shop_id", type="integer", options={"unsigned": true}, nullable=false)
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop", inversedBy="ShopBlogs")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $shop_id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="publish_date", type="datetimetz", nullable=true)
     */
    private $publish_date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean", options={"default":true})
     */
    private $visible;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var \Customize\Entity\Shop
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Shop", inversedBy="ShopBlogs")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     * })
     */
    private $Shop;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->Shop;
    }

    /**
     * @return Shop
     */
    public function getShopId()
    {
        return $this->shop_id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishDate()
    {
        return $this->publish_date;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
       return $this->update_date;
    }

    /**
     * @param Shop $Shop
     * @return $this
     */
    public function setShop(Shop $Shop)
    {
        $this->Shop = $Shop;

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setPublishDate($date)
    {
        $this->publish_date = $date;

        return $this;
    }

    /**
     * @param \DateTime $createDate
     * @return $this
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * @param \DateTime $updateDate
     * @return $this
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

}
