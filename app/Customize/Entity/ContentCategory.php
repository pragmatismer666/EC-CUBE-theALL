<?php

namespace Customize\Entity;

use Customize\Entity\Master\BlogType;
use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Class BlogCategory
 *
 * @ORM\Table(name="cmd_content_category")
 * @ORM\Entity(repositoryClass="Customize\Repository\ContentCategoryRepository")
 */
class ContentCategory extends AbstractEntity
{
    public function __construct()
    {
        $this->Blogs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @ORM\Column(name="category_name", type="text", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_no", type="smallint", options={"unsigned":true, "default":0})
     */
    protected $sort_no;

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
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Master\BlogType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_type_id", referencedColumnName="id")
     * })
     */
    private $BlogType;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|Blog[]
     *
     * @ORM\OneToMany(targetEntity="Customize\Entity\Blog", mappedBy="Category", cascade={"persist", "remove"})
     */
    private $Blogs;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSortNo()
    {
        return $this->sort_no;
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     *
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
     * @return Blog[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBlogs()
    {
        return $this->Blogs;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param int $sort_no
     * @return $this
     */
    public function setSortNo($sort_no)
    {
        $this->sort_no = $sort_no;

        return $this;
    }

    /**
     * @param BlogType $BlogType
     * @return $this
     */
    public function setBlogType(BlogType $BlogType)
    {
        $this->BlogType = $BlogType;

        return $this;
    }

    /**
     *
     * @param \DateTime $createDate
     *
     * @return $this
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     *
     * @param \DateTime $updateDate
     *
     * @return $this
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * @param Blog $Blog
     * @return $this
     */
    public function addBlog(Blog $Blog) {
        $this->Blogs[] = $Blog;

        return $this;
    }

    /**
     * @param Blog $Blog
     * @return bool
     */
    public function removeBlog(Blog $Blog) {
        return $this->Blogs->removeElement($Blog);
    }

    /**
     * @return bool
     */
    public function hasBlogs()
    {
        return count($this->Blogs) > 0;
    }
}
