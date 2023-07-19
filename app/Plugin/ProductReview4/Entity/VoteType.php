<?php

namespace Plugin\ProductReview4\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\AbstractMasterEntity;

/**
 * Class VoteType
 * @ORM\Table("plg_product_review_vote_type")
 * @ORM\Entity(repositoryClass="Plugin\ProductReview4\Repository\VoteTypeRepository")
 */
class VoteType extends AbstractMasterEntity
{
    const UPVOTE = 1;

    const DOWNVOTE = 2;
}
