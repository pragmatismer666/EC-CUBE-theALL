<?php

namespace Plugin\ProductReview4\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;
use Plugin\ProductReview4\Entity\ProductReview;
use Plugin\ProductReview4\Entity\VoteType;

/**
 * Class ProductReviewVote
 * @ORM\Table(name="plg_product_review_vote")
 * @ORM\Entity(repositoryClass="Customize\Repository\ProductReviewVoteRepository")
 */
class ProductReviewVote extends AbstractEntity
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
     * @ORM\Column(name="customer_id", type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $customer_id;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $Customer;

    /**
     * @var int
     *
     * @ORM\Column(name="product_review_id", type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $product_review_id;

    /**
     * @var ProductReview
     *
     * @ORM\ManyToOne(targetEntity="Plugin\ProductReview4\Entity\ProductReview", inversedBy="ProductReviewVotes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_review_id", referencedColumnName="id")
     * })
     */
    private $ProductReview;

    /**
     * @var int
     *
     * @ORM\Column(name="vote_type_id", type="smallint", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $vote_type_id;

    /**
     * @var VoteType
     *
     * @ORM\ManyToOne(targetEntity="Plugin\ProductReview4\Entity\VoteType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vote_type_id", referencedColumnName="id")
     * })
     */
    private $VoteType;

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * @return int
     */
    public function getProductReviewId()
    {
        return $this->product_review_id;
    }

    /**
     * @return ProductReview
     */
    public function getProductReview()
    {
        return $this->ProductReview;
    }

    /**
     * @return int
     */
    public function getVoteTypeId()
    {
        return $this->vote_type_id;
    }

    /**
     * @return VoteType
     */
    public function getVoteType()
    {
        return $this->VoteType;
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
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->customer_id = $customerId;

        return $this;
    }

    /**
     * @param Customer $Customer
     * @return $this
     */
    public function setCustomer(Customer $Customer)
    {
        $this->Customer = $Customer;

        return $this;
    }

    /**
     * @param int $productReviewId
     * @return $this
     */
    public function setProductReviewId($productReviewId)
    {
        $this->product_review_id = $productReviewId;

        return $this;
    }

    /**
     * @param ProductReview $ProductReview
     * @return $this
     */
    public function setProductReview(ProductReview $ProductReview)
    {
        $this->ProductReview = $ProductReview;

        return $this;
    }

    /**
     * @param int $voteTypeId
     * @return $this
     */
    public function setVoteTypeId($voteTypeId)
    {
        $this->vote_type_id = $voteTypeId;

        return $this;
    }

    /**
     * @param VoteType $VoteType
     * @return $this
     */
    public function setVoteType(VoteType $VoteType)
    {
        $this->VoteType = $VoteType;

        return $this;
    }
}
