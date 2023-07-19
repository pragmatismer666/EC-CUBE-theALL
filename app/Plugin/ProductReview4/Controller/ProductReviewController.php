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

namespace Plugin\ProductReview4\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Plugin\ProductReview4\Entity\ProductPurchasedStatus;
use Plugin\ProductReview4\Entity\ProductReview;
use Plugin\ProductReview4\Entity\ProductReviewStatus;
use Plugin\ProductReview4\Entity\VoteType;
use Plugin\ProductReview4\Form\Type\ProductReviewType;
use Plugin\ProductReview4\Repository\ProductPurchasedStatusRepository;
use Plugin\ProductReview4\Repository\ProductReviewRepository;
use Plugin\ProductReview4\Repository\ProductReviewStatusRepository;
use Plugin\ProductReview4\Repository\ProductReviewVoteRepository;
use Plugin\ProductReview4\Repository\VoteTypeRepository;
use Plugin\ProductReview4\Service\ProductReviewService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class ProductReviewController front.
 */
class ProductReviewController extends AbstractController
{
    /**
     * @var ProductReviewStatusRepository
     */
    private $productReviewStatusRepository;

    /**
     * @var ProductPurchasedStatusRepository
     */
    private $productPurchasedStatusRepository;

    /**
     * @var ProductReviewRepository
     */
    private $productReviewRepository;

    /**
     * @var ProductReviewVoteRepository
     */
    private $productReviewVoteRepository;

    /**
     * @var VoteTypeRepository
     */
    private $voteTypeRepository;

    /**
     * @var ProductReviewService
     */
    private $productReviewService;

    const VOTE_TYPES = [
        'up' => VoteType::UPVOTE,
        'down' => VoteType::DOWNVOTE
    ];

    /**
     * ProductReviewController constructor.
     * @param ProductReviewStatusRepository $productStatusRepository
     * @param ProductReviewRepository $productReviewRepository
     * @param ProductPurchasedStatusRepository $productPurchasedStatusRepository
     * @param ProductReviewVoteRepository $productReviewVoteRepository
     * @param VoteTypeRepository $voteTypeRepository
     * @param ProductReviewService $productReviewService
     */
    public function __construct(
        ProductReviewStatusRepository $productStatusRepository,
        ProductReviewRepository $productReviewRepository,
        ProductPurchasedStatusRepository $productPurchasedStatusRepository,
        ProductReviewVoteRepository $productReviewVoteRepository,
        VoteTypeRepository $voteTypeRepository,
        ProductReviewService $productReviewService
    ) {
        $this->productReviewStatusRepository = $productStatusRepository;
        $this->productReviewRepository = $productReviewRepository;
        $this->productPurchasedStatusRepository = $productPurchasedStatusRepository;
        $this->productReviewVoteRepository = $productReviewVoteRepository;
        $this->voteTypeRepository = $voteTypeRepository;
        $this->productReviewService = $productReviewService;
    }

    /**
     * @Route("/product_review/{id}/review", name="product_review_index", requirements={"id" = "\d+"})
     * @Route("/product_review/{id}/review", name="product_review_confirm", requirements={"id" = "\d+"})
     *
     * @param Request $request
     * @param Product $Product
     *
     * @return RedirectResponse|Response
     */
    public function index(Request $request, Product $Product)
    {
        if (!$this->session->has('_security_admin') && $Product->getStatus()->getId() !== ProductStatus::DISPLAY_SHOW) {
            log_info('Product review', ['status' => 'Not permission']);

            throw new NotFoundHttpException();
        }

        $ProductReview = new ProductReview();
        $form = $this->createForm(ProductReviewType::class, $ProductReview);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $ProductReview ProductReview */
            $ProductReview = $form->getData();

            switch ($request->get('mode')) {
                case 'confirm':
                    log_info('Product review config confirm');

                    return $this->render('ProductReview4/Resource/template/default/confirm.twig', [
                        'form' => $form->createView(),
                        'Product' => $Product,
                        'ProductReview' => $ProductReview,
                    ]);
                    break;

                case 'complete':
                    log_info('Product review complete');
                    if ($this->isGranted('ROLE_USER')) {
                        $Customer = $this->getUser();
                        $ProductReview->setCustomer($Customer);
                    }
                    $ProductReview->setShop($Product->getShop());
                    $ProductReview->setProduct($Product);
                    $ProductReview->setStatus($this->productReviewStatusRepository->find(ProductReviewStatus::HIDE));
                    $ProductReview->setPurchasedStatus(
                        $this->productPurchasedStatusRepository->getPurchasedStatus($Product, $Customer ?? null)
                    );
                    $this->entityManager->persist($ProductReview);
                    $this->entityManager->flush($ProductReview);

                    log_info('Product review complete', ['id' => $Product->getId()]);

                    return $this->redirectToRoute('product_review_complete', ['id' => $Product->getId()]);
                    break;

                case 'back':
                    // 確認画面から投稿画面へ戻る
                    break;

                default:
                    // do nothing
                    break;
            }
        }

        return $this->render('ProductReview4/Resource/template/default/index.twig', [
            'Product' => $Product,
            'ProductReview' => $ProductReview,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Complete.
     *
     * @Route("/product_review/{id}/complete", name="product_review_complete", requirements={"id" = "\d+"})
     * @Template("ProductReview4/Resource/template/default/complete.twig")
     *
     * @param $id
     *
     * @return array
     */
    public function complete($id)
    {
        return ['id' => $id];
    }

    /**
     * ページ管理表示用のダミールーティング.
     *
     * @Route("/product_review/display", name="product_review_display")
     */
    public function display()
    {
        return new Response();
    }

    /**
     * * @Route("/product_review/{id}/vote/{vote_type}", requirements={"vote_type" = "(up|down)"}, name="product_review_vote")
     *
     * @param Request $request
     * @param $id
     * @param $vote_type
     * @return \Symfony\Component\HttpFoundation\JsonResponse|RedirectResponse
     */
    public function vote(Request $request, $id, $vote_type)
    {
        /** @var ProductReview $ProductReview */
        $ProductReview = $this->productReviewRepository->find($id);
        if (!$ProductReview || !$ProductReview->getProduct()) {
            throw new NotFoundHttpException();
        }
        /** @var VoteType $VoteType */
        $VoteType = $this->voteTypeRepository->find(self::VOTE_TYPES[$vote_type] ?? null);
        if (!$VoteType) {
            throw new UnprocessableEntityHttpException();
        }
        if (!$this->isGranted('ROLE_USER')) {
            $this->setLoginTargetPath($this->generateUrl('product_detail', ['id' => $ProductReview->getProduct()->getId()]));
            return $this->redirectToRoute('mypage_login');
        }
        /** @var Customer $Customer */
        $Customer = $this->getUser();
        $this->productReviewService->handleVote($ProductReview, $Customer, $VoteType);
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            $CustomerVote = $this->productReviewVoteRepository->getQueryBuilderBySearchData([
                'ProductReview' => $ProductReview,
                'Customer' => $Customer
            ])->getFirstResult();
            return $this->json([
                'ProductReview' => $ProductReview,
                'CustomerVote' => $CustomerVote
            ]);
        }
        return $this->redirectToRoute('product_detail', ['id' => $ProductReview->getProduct()->getId()]);
    }

}
