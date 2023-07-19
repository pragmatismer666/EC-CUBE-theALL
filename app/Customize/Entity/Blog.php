<?php

namespace Customize\Entity;

use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Customize\Entity\ContentCategory;
use Customize\Entity\BlogTag;
use Customize\Entity\Master\BlogType;
use Eccube\Annotation as Eccube;

/**
 * Class Blog
 *
 * @ORM\Table(name="cmd_blog")
 * @ORM\Entity(repositoryClass="Customize\Repository\BlogRepository")
 */
class Blog extends AbstractEntity
{

    public function __construct()
    {
        $this->BlogTags = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @var string
     *
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     * @Eccube\FormAppend
     */
    private $thumbnail;

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
     * @var BlogType
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Master\BlogType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_type_id", referencedColumnName="id")
     * })
     */
    private $BlogType;

    /**
     * @var ContentCategory
     * @ORM\ManyToOne(targetEntity="Customize\Entity\ContentCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $Category;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Customize\Entity\BlogTag", mappedBy="Blog", cascade={"remove"})
     */
    private $BlogTags;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return BlogTag
     */
    public function getBlogTags()
    {
        return $this->BlogTags;
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
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
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
     * @return BlogType
     */
    public function getBlogType()
    {
        return $this->BlogType;
    }

    /**
     * @return ContentCategory
     */
    public function getCategory()
    {
        return $this->Category;
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
     * @param string $thumbnail
     * @return $this
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

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
     * @param BlogType $blogType
     * @return $this
     */
    public function setBlogType(BlogType $blogType)
    {
        $this->BlogType = $blogType;

        return $this;
    }

    /**
     * @param ContentCategory $Category
     * @return $this
     */
    public function setCategory(ContentCategory $Category)
    {
        $this->Category = $Category;

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

    /**
     * @param BlogTag $blogTag
     * @return $this
     */
    public function addBlogTag(BlogTag $blogTag)
    {
        $this->BlogTags[] = $blogTag;

        return $this;
    }

    /**
     * @param \Customize\Entity\BlogTag $blogTag
     * @return bool
     */
    public function removeBlogTag(BlogTag $blogTag)
    {
        return $this->BlogTags->removeElement($blogTag);
    }
}
