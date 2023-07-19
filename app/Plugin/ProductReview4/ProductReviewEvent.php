<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview4;

use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\Master\ProductStatusRepository;
use Plugin\ProductReview4\Entity\ProductReview;
use Plugin\ProductReview4\Entity\ProductReviewStatus;
use Plugin\ProductReview4\Entity\ProductReviewVote;
use Plugin\ProductReview4\Repository\ProductReviewConfigRepository;
use Plugin\ProductReview4\Repository\ProductReviewRepository;
use Plugin\ProductReview4\Repository\ProductReviewVoteRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Eccube\Entity\Customer;

class ProductReviewEvent implements EventSubscriberInterface
{
    /**
     * @var ProductReviewConfigRepository
     */
    protected $productReviewConfigRepository;

    /**
     * @var ProductReviewRepository
     */
    protected $productReviewRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var ProductReviewVoteRepository
     */
    protected $productReviewVoteRepositoy;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * ProductReview constructor.
     *
     * @param ProductReviewConfigRepository $productReviewConfigRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param ProductReviewRepository $productReviewRepository
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ProductReviewConfigRepository $productReviewConfigRepository,
        ProductStatusRepository $productStatusRepository,
        ProductReviewRepository $productReviewRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->productReviewConfigRepository = $productReviewConfigRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->productReviewRepository = $productReviewRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Product/detail.twig' => 'detail'
        ];
    }

    /**
     * @param TemplateEvent $event
     */
    public function detail(TemplateEvent $event)
    {
        $event->addSnippet('ProductReview4/Resource/template/default/review.twig');

        $Config = $this->productReviewConfigRepository->get();

        /** @var Product $Product */
        $Product = $event->getParameter('Product');

        /** @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token */
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $Customer = $token->getUser();
        } else {
            $Customer = null;
        }
        
        if (!$Customer instanceof Customer) {
            $Customer = null;
        }
        $ProductReviews = $this->productReviewRepository->getDisplayReviews($Product, $Customer);
        $CustomerVotes = [];
        /** @var ProductReview $ProductReview */
        foreach($ProductReviews as $ProductReview) {
            $ProductReviewVotes = $ProductReview->getProductReviewVotes();
            /** @var ProductReviewVote $CustomerVote */
            $CustomerVote = $ProductReviewVotes->filter(function ($item /** @var ProductReviewVote $item */) use ($Customer) {
                if ($Customer) {
                    return $item->getCustomerId() === $Customer->getId();
                } else {
                    return false;
                }
            })->first();
            $CustomerVotes[] = $CustomerVote ? $CustomerVote->getVoteTypeId() : null;
        }
        $rate = $this->productReviewRepository->getAvgAll($Product);
        $avg = round($rate['recommend_avg']);
        $count = intval($rate['review_count']);

        $parameters = $event->getParameters();
        $parameters['ProductReviews'] = $ProductReviews;
        $parameters['CustomerVotes'] = $CustomerVotes;
        $parameters['ProductReviewAvg'] = $avg;
        $parameters['ProductReviewCount'] = $count;
        $event->setParameters($parameters);
    }

}
