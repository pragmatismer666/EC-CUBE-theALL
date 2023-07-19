<?php

namespace Customize\Entity;

use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Customize\Entity\Blog;
use Customize\Entity\ContentTag;

/**
 * Class BlogTag
 *
 * @ORM\Table("cmd_blog_tag")
 * @ORM\Entity(repositoryClass="Customize\Repository\BlogTagRepository")
 */
class BlogTag extends AbstractEntity
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var int
     *
     * @ORM\Column(name="blog_id", type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $blog_id;

    /**
     * @var Blog
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\Blog", inversedBy="BlogTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="blog_id", referencedColumnName="id")
     * })
     */
    private $Blog;

    /**
     * @var int
     *
     * @ORM\Column(name="tag_id", type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $tag_id;

    /**
     * @var ContentTag
     *
     * @ORM\ManyToOne(targetEntity="Customize\Entity\ContentTag", inversedBy="BlogTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $ContentTag;

    /**
     * @return int
     */
    public function getBlogId()
    {
        return $this->blog_id;
    }

    /**
     * @return Blog|null
     */
    public function getBlog()
    {
        return $this->Blog;
    }

    /**
     * @return int
     */
    public function getTagId()
    {
        return $this->tag_id;
    }

    /**
     * @return ContentTag|null
     */
    public function getTag()
    {
        return $this->ContentTag;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
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
     * @param int $blogId
     * @return $this
     */
    public function setBlogId($blogId)
    {
        $this->blog_id = $blogId;

        return $this;
    }

    /**
     * @param Blog $blog
     * @return $this
     */
    public function setBlog(Blog $blog)
    {
        $this->Blog = $blog;

        return $this;
    }

    /**
     * @param int $tagId
     * @return $this
     */
    public function setTagId($tagId)
    {
        $this->tag_id = $tagId;

        return $this;
    }

    /**
     * @param ContentTag $contentTag
     * @return $this
     */
    public function setTag(ContentTag $contentTag)
    {
        $this->ContentTag = $contentTag;

        return $this;
    }
}
