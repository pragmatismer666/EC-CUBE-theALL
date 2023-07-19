<?php

namespace Customize\Entity;

use Customize\Entity\Master\BlogType;
use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Customize\Entity\BlogTag;
use Doctrine\Common\Collections\Criteria;

/**
 * Class ContentTag
 *
 * @ORM\Table(name="cmd_content_tag")
 * @ORM\Entity(repositoryClass="Customize\Repository\ContentTagRepository")
 */
class ContentTag extends AbstractEntity
{
    public function __construct()
    {
        $this->BlogTags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        return $this->getName();
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
     * @ORM\Column(name="tag_name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="sort_no", type="smallint", options={"unsigned":true, "default":0})
     */
    private $sort_no;

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
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Customize\Entity\BlogTag", mappedBy="ContentTag", cascade={"remove"})
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
     * @return BlogType
     */
    public function getBlogType()
    {
        return $this->BlogType;
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

    public function hasBlogTags()
    {
        $criteria = Criteria::create()
            ->orderBy(['tag_id' => Criteria::ASC])
            ->setFirstResult(0)
            ->setMaxResults(1);
        return $this->BlogTags->matching($criteria)->count() > 0;
    }
}
