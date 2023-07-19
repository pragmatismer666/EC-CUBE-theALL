<?php

namespace Customize;


use Customize\Entity\Shop;
use Customize\Event\MallDevelEvents;
use Customize\Services\CustomerHistoryService;
use Customize\Services\Payment\Method\StripeCredit;
use Customize\Repository\StripeCreditOrderRepository;
use Customize\Repository\StripeConfigRepository;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Request\Context;
use Symfony\Component\Routing\RouterInterface;
use Customize\Doctrine\Filter\OwnShopFilter;
use Eccube\Entity\Member;
use Eccube\Entity\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MalldevelEvent implements EventSubscriberInterface {

    /** @var ContainerInterface */
    protected $container;

    /** @var EntityManagerInterface  */
    protected $entityManager;

    /** @var Context  */
    protected $requestContext;

    /** @var RouterInterface  */
    protected $router;

    /** @var  CustomerHistoryService */
    protected $customerHistoryService;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        Context $requestContext,
        RouterInterface $router,
        CustomerHistoryService $customerHistoryService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->requestContext = $requestContext;
        $this->router = $router;
        $this->customerHistoryService = $customerHistoryService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            KernelEvents::CONTROLLER    =>  ['onKernelController'],
            KernelEvents::RESPONSE      =>  ['onKernelResponse'],
            MallDevelEvents::FRONT_SHOP_DETAIL_COMPLETE => 'onFrontShopDetailComplete',
            EccubeEvents::FRONT_PRODUCT_DETAIL_INITIALIZE => 'onFrontProductDetailInitialize',
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
            '@admin/Order/edit.twig' => 'onAdminOrderEditTwig',
        ];
    }

    public function onKernelController(FilterControllerEvent $event) { // FilterControllerEvent $event) {
        if ( $this->requestContext->isAdmin() ) {
            $Member = $this->requestContext->getCurrentUser();
            if( $Member instanceof Member && $Member->getRole() === "ROLE_SHOP_OWNER") {
                if (!$Member->hasShop()) {
                    $request = $event->getRequest();
                    $this->tokenStorage->setToken(null);
                    $request->getSession()->migrate(true);
                    throw new NotFoundHttpException(trans('malldevel.admin.login.error.not_assigned_shop'));
                }
                // 自ショップの情報のみ取得するフィルター設定
                $this->enableShopFilter($Member->getShop());
            } else if ($Member instanceof Member && $Member->getRole() === "ROLE_ADMIN") {
                // case "ROLE_ADMIN"
                
                $request = $event->getRequest();
                $filter_shop = $this->getShopFilterFromRequest($request);
                if ($filter_shop ) {
                    $this->enableShopFilter($filter_shop);
                }
            } else if($Member instanceof Member && $Member->getRole() === "ROLE_APPLICANT") {
                $request = $event->getRequest();
                $route = $request->get('_route');
                if ($route != null && $route != "admin_homepage" && $route != "malldevel_applicant_stripe_apply" && $route != "malldevel_applicant_stripe_apply_request") {
                    die("Bad Request");
                }
            }
        }
    }
    public function onKernelResponse(FilterResponseEvent $event) {
        if ($this->requestContext->isAdmin()) {
            $Member = $this->requestContext->getCurrentUser();
            if ($Member instanceof Member && $Member->hasShop()) {
                // ショップメンバーは店舗設定の基本設定はアクセス不可、配送方法設定へリダイレクト
                if ($event->getRequest()->getRequestUri() === $this->router->generate('admin_setting_shop')) {
                    $event->setResponse(new RedirectResponse($this->router->generate('malldevel_admin_shop_edit', [ 'id' => $Member->getShop()->getId() ])));
                }
            } else if ($Member instanceof Member && $Member->getRole() == "ROLE_APPLICANT") {
                if ($event->getRequest()->getRequestUri() === $this->router->generate('admin_homepage')) {
                    $event->setResponse(new RedirectResponse($this->router->generate('malldevel_applicant_stripe_apply')));
                }
            }
        }
    }

    public function onFrontProductDetailInitialize(EventArgs $eventArgs)
    {
        /** @var Product $Product */
        $Product = $eventArgs->getArgument('Product');
        $this->customerHistoryService->saveProductHistory($Product);
    }

    public function onFrontShopDetailComplete(EventArgs $eventArgs)
    {
        /** @var Shop $Shop */
        $Shop = $eventArgs->getArgument('Shop');
        try {
            $this->customerHistoryService->saveShopHistory($Shop);
        } catch (\Exception $e) {
            log_error($e->getMessage());
        }
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof Customer) {
            try {
                $this->customerHistoryService->saveGuestHistoryToCustomerHistory();
            } catch (\Exception $e) {
                log_error($e->getMessage());
            }
        }
    }

    private function enableShopFilter($Shop) {
        $config = $this->entityManager->getConfiguration();
        $config->addFilter('own_shop_product', OwnShopFilter::class);
        /** @var OwnShopFilter $filter */
        $filter = $this->entityManager->getFilters()->enable('own_shop_product');
        $filter->setShopId($Shop);
    }

    private function getShopFilterFromRequest($request)
    {
        $route = $request->get('_route');
        // activate filter on product class page
        if ($route == "admin_product_product_class") {
            $id = $request->get('id');
            $Product = $this->entityManager->getRepository(Product::class)->find($id);
            if (empty($Product) || empty($Product->getShop())) {
                throw new NotFoundHttpException("Please select a shop at first");
            }
            // $Shop = $Product->getShop();
            // dump($Shop->getId()); die();
            return $Product->getShop();
        }
        // TODO consider ShopFilter cases

        return null;
    }

    /**
     * @param TemplateEvent $event
     */
    public function onAdminOrderEditTwig(TemplateEvent $event)
    {
        // 表示対象の受注情報を取得
        $Order = $event->getParameter('Order');

        if (!$Order)
        {
            return;
        }

        // EC-CUBE支払方法の取得
        $Payment = $Order->getPayment();

        if (!$Payment)
        {
            return;
        }

        if ($Order->getPayment()->getMethodClass() === StripeCredit::class) {

            $creditOrderRepository = $this->container->get(StripeCreditOrderRepository::class);

            $StripeCreditOrder = $creditOrderRepository->findOneBy(array('Order'=>$Order));

            if (!$StripeCreditOrder)
            {
                return;
            }
            // BOC check availability for stripe action
            $Member = $this->requestContext->getCurrentUser();
            if ($Member instanceof Member && $Member->hasShop()) {
                $account_id = $Member->getShop()->getStripeId();
                // if (empty($account_id)) throw new NotFoundHttpException();
            } else if ($Member->getRole() == "ROLE_ADMIN") {
                $account_id = null;
            } else {
                throw new NotFoundHttpException();
            }
            // EOC check availability for stripe action

            if($StripeCreditOrder->getIsChargeRefunded()==1 && $StripeCreditOrder->getSelectedRefundOption()==0 && $StripeCreditOrder->getRefundedAmount()==0) {
                $StripeCreditOrder->setSelectedRefundOption(1);
                $StripeCreditOrder->setRefundedAmount($Order->getPaymentTotal());
                $this->entityManager->persist($StripeCreditOrder);
                $this->entityManager->flush($StripeCreditOrder);
            }

            $StripeConfig = $this->container->get(StripeConfigRepository::class)->get();
            $publishableKey = $StripeConfig->getPublishableKey();
            if(strpos($publishableKey, 'live') !== false) {
                $isLive = true;
            } else {
                $isLive = false;
            }


            $event->setParameter('StripeConfig', $StripeConfig);
            $event->setParameter('StripeCreditOrder', $StripeCreditOrder);
            $event->setParameter('StripeChargeDashboardLink',$this->getStripeChargeDashboardLink($isLive, $account_id));
        }
    }

    public function getStripeChargeDashboardLink($is_live, $account_id = null)
    {
        if($is_live){
            $live_seg = "live";
        } else {
            $live_seg = "test";
        }
        $chargeDashboardLink='https://dashboard.stripe.com/';
        if ($account_id) {
            $chargeDashboardLink .= $account_id . '/';
        }
        
        return $chargeDashboardLink . $live_seg . "/payments/";
    }
}
