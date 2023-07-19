<?php

namespace Plugin\ProductReview4\Service;

use Doctrine\ORM\EntityManager;
use Eccube\Entity\Customer;
use Plugin\ProductReview4\Entity\ProductReview;
use Plugin\ProductReview4\Entity\ProductReviewVote;
use Plugin\ProductReview4\Entity\VoteType;
use Plugin\ProductReview4\Repository\ProductReviewVoteRepository;
use Plugin\ProductReview4\Repository\VoteTypeRepository;

class ProductReviewService
{
    /**
     * @var ProductReviewVoteRepository
     */
    protected $productReviewVoteRepository;

    /**
     * @var VoteTypeRepository
     */
    protected $voteTypeRepository;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(
        ProductReviewVoteRepository $productReviewVoteRepository,
        VoteTypeRepository $voteTypeRepository,
        EntityManager $entityManager
    )
    {
        $this->productReviewVoteRepository = $productReviewVoteRepository;
        $this->voteTypeRepository = $voteTypeRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ProductReview $ProductReview
     * @param Customer $Customer
     * @param VoteType|null $VoteType
     */
    public function handleVote(ProductReview $ProductReview, Customer $Customer, $VoteType)
    {
        /** @var ProductReviewVote|null $Upvote */
        $Upvote = $this->productReviewVoteRepository->getQueryBuilderBySearchData([
            'ProductReview' => $ProductReview,
            'Customer' => $Customer,
            'VoteType' => $this->voteTypeRepository->find(VoteType::UPVOTE)
        ])->getResult();
        /** @var ProductReviewVote|null $Downvote */
        $Downvote = $this->productReviewVoteRepository->getQueryBuilderBySearchData([
            'ProductReview' => $ProductReview,
            'Customer' => $Customer,
            'VoteType' => $this->voteTypeRepository->find(VoteType::DOWNVOTE)
        ])->getResult();
        if (!empty($Upvote[0])) {
            $Upvote = $Upvote[0];
        } else {
            $Upvote = null;
        }
        if (!empty($Downvote[0])) {
            $Downvote = $Downvote[0];
        } else {
            $Downvote = null;
        }
        if ($Upvote) {
            $this->entityManager->remove($Upvote);
            $ProductReview->subtractVoteCount(VoteType::UPVOTE);
            $this->entityManager->persist($ProductReview);
            $this->entityManager->flush();
        }
        if ($Downvote) {
            $this->entityManager->remove($Downvote);
            $ProductReview->subtractVoteCount(VoteType::DOWNVOTE);
            $this->entityManager->persist($ProductReview);
            $this->entityManager->flush();
        }
        if ($VoteType->getId() === VoteType::UPVOTE && !$Upvote) {
            $Vote = new ProductReviewVote();
            $Vote->setCustomer($Customer);
            $Vote->setCustomerId($Customer->getId());
            $Vote->setProductReview($ProductReview);
            $Vote->setProductReviewId($ProductReview->getId());
            $Vote->setVoteType($this->voteTypeRepository->find(VoteType::UPVOTE));
            $Vote->setVoteTypeId(VoteType::UPVOTE);
            $this->entityManager->persist($Vote);
            $ProductReview->addVoteCount(VoteType::UPVOTE);
            $this->entityManager->persist($ProductReview);
            $this->entityManager->flush();
        }
        if ($VoteType->getId() === VoteType::DOWNVOTE && !$Downvote) {
            $Vote = new ProductReviewVote();
            $Vote->setCustomer($Customer);
            $Vote->setCustomerId($Customer->getId());
            $Vote->setProductReview($ProductReview);
            $Vote->setProductReviewId($ProductReview->getId());
            $Vote->setVoteType($this->voteTypeRepository->find(VoteType::DOWNVOTE));
            $Vote->setVoteTypeId(VoteType::DOWNVOTE);
            $this->entityManager->persist($Vote);
            $ProductReview->addVoteCount(VoteType::DOWNVOTE);
            $this->entityManager->persist($ProductReview);
            $this->entityManager->flush();
        }
        return;
    }
}