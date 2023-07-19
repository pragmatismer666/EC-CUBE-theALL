<?php

namespace Customize\Controller;

use Customize\Entity\Shop;
use Customize\Event\MallDevelEvents;
use Customize\Repository\ShopBlogRepository;
use Eccube\Controller\AbstractController;

use Eccube\Event\EventArgs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Repository\ShopRepository;
use Customize\Services\ShopService;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\BaseInfoRepository;
use Customize\Entity\Apply;
use Customize\Form\Type\Front\ApplyType;
use Customize\Services\StripeService;
use Customize\Repository\ApplyRepository;
use Eccube\Repository\MemberRepository;

class ShopController extends AbstractController
{

    protected $container;
    protected $shopRepository;
    protected $shopService;
    protected $productRepository;

    const SHOP_CREATION_SESSION = "malldevel.shop.creation.session";
    /**
     * @var \Customize\Repository\ShopBlogRepository;
     */
    protected $shopBlogRepository;
    protected $BaseInfo;

    public function __construct(
        ContainerInterface $container,
        ShopRepository $shopRepository,
        ShopService $shopService,
        ProductRepository $productRepository,
        ShopBlogRepository $shopBlogRepository,
        BaseInfoRepository $baseInfoRepository
    )
    {
        $this->container = $container;
        $this->shopRepository = $shopRepository;
        $this->shopService = $shopService;
        $this->productRepository = $productRepository;
        $this->shopBlogRepository = $shopBlogRepository;
        $this->BaseInfo = $baseInfoRepository->get();
    }

    /**
     * @Route("/shops/list", name="malldevel_front_shop_list")
     * @Template("Shop/list.twig")
     */
    public function list(Request $request)
    {

        return [];
    }

    /**
     * @Route("/shops/detail/{id}", name="malldevel_front_shop_detail")
     * @Template("Shop/detail.twig")
     */
    public function detail(Request $request, $id, Paginator $paginator)
    {
        $Shop = $this->shopRepository->find($id);
        if (!$Shop || !$this->checkVisibility($Shop)) {
            throw new NotFoundHttpException();
        }
        
        if ($this->BaseInfo->isOptionNostockHidden()) {
            $this->entityManager->getFilters()->enable('option_nostock_hidden');
        }

        $pageno = $request->get('pageno', 1);
        $per_page = $request->get('disp_number', 18);

        $qb = $this->productRepository->getQueryBuilderBySearchData(['Shop' => $Shop]);

        $query = $qb->getQuery();

        $Pagination = $paginator->paginate(
            $query,
            $pageno,
            $per_page
        );

        $ShopBlogs = $this->shopBlogRepository->getDisplayShopBlogs($Shop->getId());

        $pageData = compact(
            'Shop',
            'Pagination',
            'ShopBlogs',
            'pageno'
        );

        $event = new EventArgs($pageData, $request);
        $this->eventDispatcher->dispatch(MallDevelEvents::FRONT_SHOP_DETAIL_COMPLETE, $event);

        return $pageData;
    }

    /**
     * @Route("/shop/register", name="malldevel_shop_register")
     * @Template("Shop/register.twig")
     */
    public function registerShop(Request $request, StripeService $stripe_service) 
    {
        $Apply = new Apply;
        
        $builder = $this->formFactory->createBuilder(ApplyType::class, $Apply);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $mode = $request->get('mode');
            if( $mode == 'confirm') {
                
                return $this->render(
                    'Shop/register_confirm.twig',
                    [
                        'form'  =>  $form->createView(),
                    ]
                );
            } else if ($mode == "complete") {
                $Apply = $form->getData();
                // $stripe_account = $stripe_service->createAccount($temp_owner);
                // $temp_owner->setStripeId($stripe_account->id);
    
                $order_mail = $Apply->getOrderMail();
                $uuid = $this->shopService->generateApplyUuid();
                $Apply->setUuid($uuid);
                $Apply->setStatus(Apply::STATUS_PROCESSING);
                
                $this->entityManager->persist($Apply);
                $this->entityManager->flush();

                $mailService = $this->get("malldevel.email.service");
                $mailService->sendApplicationRegisteredMail($Apply);
                
                // $account_links = $stripe_service->createAccountLink($stripe_account);
                // $this->session->set(self::SHOP_CREATION_SESSION, $temp_owner->getId());
                // if ($account_links) {
                    // return $this->redirect($account_links->url);
                // }
                return $this->render("Shop/register_complete.twig");
            }
        }
        return [
            'form'  =>  $form->createView(),
        ];
    }
    /**
     * @Route("/shop/cancel_apply/{uuid}", name="malldevel_shop_apply_cancel")
     * @Template("Shop/register_cancel.twig")
     */
    public function cancelApply(Request $request, $uuid) 
    {
        if (!$uuid) {
            throw new NotFoundHttpException();
        }
        $Apply = $this->entityManager->getRepository(Apply::class)->findOneBy(['uuid' => $uuid]);
        if (!$Apply) {
            throw new NotFoundHttpException();
        }
        if ($Apply->getStatus() === Apply::STATUS_ALLOWED) {
            return [
                'msg'    =>  trans('malldevel.admin.apply.cancel.error.already_accepted'),
                'cancellable' => false
            ];
        }

        if ($Apply->getStatus() === Apply::STATUS_CANCELED) {
            return [
                'msg'   =>  trans('malldevel.admin.apply.cancel.error.already_canceled'),
                'cancellable'   =>  false,
            ];
        }

        if ($Apply->getStatus() === Apply::STATUS_HOLD) {
            return [
                'msg'   =>  trans('malldevel.admin.apply.cancel.error.already_holded'),
                'cancellable'   =>  false,
            ];
        }

        if ($request->getMethod() == "POST") {
            $Apply->setStatus(Apply::STATUS_CANCELED);
            $this->entityManager->persist($Apply);
            $this->entityManager->flush();
            return [
                'msg'           =>  trans('malldevel.admin.apply.cancel.completed'),
                'cancellable'   =>  false
            ];
        }

        return [
            'msg'   =>  trans('malldevel.admin.apply.cancel.confirm'),
            'cancellable'   =>  true,
            'Apply' =>  $Apply
        ];
    }
    
    // public function shopCreationReturn(
    //     Request $request, 
    //     ApplyRepository $apply_repo, 
    //     ShopRepository $shop_repo,
    //     MemberRepository $member_repo,
    //     StripeService $stripe_service) 
    // {
    //     $temp_id = $this->session->get(self::SHOP_CREATION_SESSION, null);
    //     if (!$temp_id) {
    //         $this->addError('malldevel.shop.register.stripe_failed', "front");
    //         return $this->redirectToRoute("malldevel_shop_register");
    //     }
    //     $Apply = $apply_repo->find($temp_id);
    //     if (!$Apply) {
    //         return $this->redirectToRoute("malldevel_shop_register");
    //     }
// 
    //     if ($Apply->isChargeEnabled()) {
    //         $msg = "malldevel.shop.register.already_registered";
    //     } else {
    //         $account = $stripe_service->retrieveAccount($Apply->getStripeId());
    //         if (!$account) {
    //             return $this->redirectToRoute("malldevel_shop_register");
    //         }
    //         // accept Tos
    //         $account = $stripe_service->acceptTos($account);
    //         // TODO add stripe connect tos section int tos
    //         if ($account->charges_enabled) {
    //             $shop = $shop_repo->findOneBy(['apply_id' => $Apply->getId()]);
    //             if (!$shop) {
    //                 $this->shopService->createShopFromApply($Apply);
    //             }
    //             $Apply->setChargeEnabled(1);
    //             $this->entityManager->persist($Apply);
    //             $this->entityManager->flush();
    //             $msg = "malldevel.shop.register.successfully_registered";
    //         } else {
    //             $msg = "malldevel.shop.register.not_yet_enabled";
    //         }
    //     }
    //     return [
    //         'msg'   =>  trans($msg)
    //     ];
    // }
    
    /**
     * @Route("/shop/register/refresh", name="malldevel_shop_register_refresh")
     */
    public function shopCreationRefresh(Request $request) 
    {
        $this->addError("Stripe business registration failed!!!", "front");
        return $this->redirectToRoute("malldevel_shop_register");
    }

    /**
     * @Route("/shops/transaction_law/{id}", name="malldevel_shop_transaction_law")
     * @Template("Shop/tokusho.twig")
     */
    public function shopTransactionLaw($id) 
    {
        $Shop = $this->shopRepository->get($id);
        if (!$Shop) {
            throw new NotFoundHttpException();
        }
        
        return [
            'Shop'  =>  $Shop,
        ];
    }

    protected function checkVisibility(Shop $Shop)
    {
        $is_admin = $this->session->has('_security_admin');
        if ($is_admin) {
            return true;
        }
        return $Shop->isEnabled();
    }
}