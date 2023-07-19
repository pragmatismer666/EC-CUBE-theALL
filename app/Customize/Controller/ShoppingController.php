<?php

namespace Customize\Controller;

use Eccube\Controller\ShoppingController as ParentController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Entity\Order;
use Eccube\Form\Type\Shopping\OrderType;
use Customize\Entity\StripeConfig;
use Customize\Services\StripeClient;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Customize\Doctrine\Filter\OwnShopFilter;
use Customize\Doctrine\Filter\ShoppingDeliveryFilter;
use Customize\Services\Payment\Method\StripeCredit;
class ShoppingController extends ParentController 
{

    /**
     * 注文手続き画面を表示する
     *
     * 未ログインまたはRememberMeログインの場合はログイン画面に遷移させる.
     * ただし、非会員でお客様情報を入力済の場合は遷移させない.
     *
     * カート情報から受注データを生成し, `pre_order_id`でカートと受注の紐付けを行う.
     * 既に受注が生成されている場合(pre_order_idで取得できる場合)は, 受注の生成を行わずに画面を表示する.
     *
     * purchaseFlowの集計処理実行後, warningがある場合はカートど同期をとるため, カートのPurchaseFlowを実行する.
     *
     * @Route("/shopping", name="shopping")
     * @Template("Shopping/index.twig")
     */
    public function index(PurchaseFlow $cartPurchaseFlow)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文手続] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // カートチェック.
        $Cart = $this->cartService->getCart();
        if (!($Cart && $this->orderHelper->verifyCart($Cart))) {
            log_info('[注文手続] カートが購入フローへ遷移できない状態のため, カート画面に遷移します.');

            return $this->redirectToRoute('cart');
        }
        
        // BOC---enable delivery shop filter
        $CartItem = $Cart->getCartItems()[0];
        $Shop = $CartItem->getProductClass()->getProduct()->getShop();;
        
        if (!$Shop) {
            log_error("Shop is not specified, CartItem id : {$CartItem->getId()} ");
            return $this->redirectToRoute("shopping_error");
        }
        $this->enableDeliveryShopFilter($Shop);
        // EOC---enable delivery shop filter

        // 受注の初期化.
        log_info('[注文手続] 受注の初期化処理を開始します.');
        $Customer = $this->getUser() ? $this->getUser() : $this->orderHelper->getNonMember();
        $Order = $this->orderHelper->initializeOrder($Cart, $Customer);


        // 集計処理.
        log_info('[注文手続] 集計処理を開始します.', [$Order->getId()]);
        $flowResult = $this->executePurchaseFlow($Order, false);
        $this->entityManager->flush();

        if ($flowResult->hasError()) {
            log_info('[注文手続] Errorが発生したため購入エラー画面へ遷移します.', [$flowResult->getErrors()]);

            return $this->redirectToRoute('shopping_error');
        }

        if ($flowResult->hasWarning()) {
            log_info('[注文手続] Warningが発生しました.', [$flowResult->getWarning()]);

            // 受注明細と同期をとるため, CartPurchaseFlowを実行する
            $cartPurchaseFlow->validate($Cart, new PurchaseContext());
            $this->cartService->save();
        }

        // マイページで会員情報が更新されていれば, Orderの注文者情報も更新する.
        if ($Customer->getId()) {
            $this->orderHelper->updateCustomerInfo($Order, $Customer);
            $this->entityManager->flush();
        }

        $form = $this->createForm(OrderType::class, $Order);

        return [
            'form' => $form->createView(),
            'Order' => $Order,
        ];
    }

    /**
     * 注文確認画面を表示する.
     *
     * ここではPaymentMethod::verifyがコールされます.
     * PaymentMethod::verifyではクレジットカードの有効性チェック等, 注文手続きを進められるかどうかのチェック処理を行う事を想定しています.
     * PaymentMethod::verifyでエラーが発生した場合は, 注文手続き画面へリダイレクトします.
     *
     * @Route("/shopping/confirm", name="shopping_confirm", methods={"POST"})
     * @Template("Shopping/confirm.twig")
     */
    public function confirm(Request $request)
    {
        // ログイン状態のチェック.
        if ($this->orderHelper->isLoginRequired()) {
            log_info('[注文確認] 未ログインもしくはRememberMeログインのため, ログイン画面に遷移します.');

            return $this->redirectToRoute('shopping_login');
        }

        // 受注の存在チェック
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            log_info('[注文確認] 購入処理中の受注が存在しません.', [$preOrderId]);

            return $this->redirectToRoute('shopping_error');
        }

        // boc---add delivery filter
        $this->enableDeliveryShopFilter($Order->getShopFromItems());
        // eoc---add delivery filter

        $form = $this->createForm(OrderType::class, $Order);
        if ($Order->getPayment()->getMethodClass() == StripeCredit::class) {
            $StripeConfig = $this->entityManager->getRepository(StripeConfig::class)->get();
            $payment_fee = $StripeConfig->getStripeFeesPercent();
            if ($payment_fee) {
                $stripe_credit_fee = $Order->getPaymentTotal() * $payment_fee / 100;
            }
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            
            if ($form->isValid()) {

                log_info('[注文確認] 集計処理を開始します.', [$Order->getId()]);
                $response = $this->executePurchaseFlow($Order);
                $this->entityManager->flush();

                if ($response) {
                    return $response;
                }

                log_info('[注文確認] PaymentMethod::verifyを実行します.', [$Order->getPayment()->getMethodClass()]);
                $paymentMethod = $this->createPaymentMethod($Order, $form);
                $PaymentResult = $paymentMethod->verify();

                if ($PaymentResult) {
                    if (!$PaymentResult->isSuccess()) {
                        $this->entityManager->rollback();
                        foreach ($PaymentResult->getErrors() as $error) {
                            $this->addError($error);
                        }

                        log_info('[注文確認] PaymentMethod::verifyのエラーのため, 注文手続き画面へ遷移します.', [$PaymentResult->getErrors()]);

                        return $this->redirectToRoute('shopping');
                    }

                    $response = $PaymentResult->getResponse();
                    if ($response instanceof Response && ($response->isRedirection() || $response->isSuccessful())) {
                        $this->entityManager->flush();

                        log_info('[注文確認] PaymentMethod::verifyが指定したレスポンスを表示します.');

                        return $response;
                    }
                }
            

                $this->entityManager->flush();

                log_info('[注文確認] 注文確認画面を表示します.');
                $res = [
                    'form' => $form->createView(),
                    'Order' => $Order,
                ];

                if (isset($stripe_credit_fee)) {
                    $res['stripe_credit_fee'] = $stripe_credit_fee;
                }

                return $res;
            }

        }

        log_info('[注文確認] フォームエラーのため, 注文手続画面を表示します.', [$Order->getId()]);

        // FIXME @Templateの差し替え.
        $request->attributes->set('_template', new Template(['template' => 'Shopping/index.twig']));
        
        $res = [
            'form' => $form->createView(),
            'Order' => $Order,
        ];
        if (isset($stripe_credit_fee)) {
            $res['stripe_credit_fee'] = $stripe_credit_fee;
        }

        return $res;
    }

    /**
     * @Route("/shopping/payment/credit_card", name="shopping_credit_card")
     * @Template("Shopping/credit_card.twig")
     */
    public function creditCard(Request $request)
    {
        if ($this->orderHelper->isLoginRequired()) {
            return $this->redirectToRoute('shopping_login');
        }
        $form = $this->createForm(OrderType::class, new Order, ['skip_add_form' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pre_order_id = $this->cartService->getPreOrderId();
            $Order = $this->orderHelper->getPurchaseProcessingOrder($pre_order_id);
            $Customer = $Order->getCustomer();
            $StripeConfig = $this->entityManager->getRepository(StripeConfig::class)->get();

            $payment_method = false;
            $exp = "";
            $nonmem = false;

            if ($this->isGranted('ROLE_USER')) {
                $payment_method = $this->checkExistingMethod($Customer, $StripeConfig);

                if ($payment_method) {
                    $exp = \sprintf("%02d/%d", $payment_method->card->exp_month, $payment_method->card->exp_year);
                }
            }

            if (!$this->getUser() && $this->orderHelper->getNonMember()) {
                $nonmem = true;
            }
            $form = $form->createView();
            return compact('StripeConfig', 'Order', 'payment_method', 'exp', 'nonmem', 'form');
        }
        return $this->redirectToRoute('shopping');
    }

    /**
     * @Route("/shopping/payment/credit_card/intent", name="malldevel_credit_payment_intent")
     */
    public function createPaymentIntent(Request $request)
    {
        $preOrderId = $this->cartService->getPreOrderId();
        $Order = $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
        if (!$Order) {
            return $this->json(['error' => 'true', 'message' => trans('malldevel.stripe.credit.create_intent.invalid_request')]);
        }
        if ($Order->getPayment()->getMethodClass() != StripeCredit::class) {
            return $this->json(['error' => 'true', 'message' => trans('malldevel.stripe.credit.create_intent.invalid_request')]);
        }

        $StripeConfig = $this->entityManager->getRepository(StripeConfig::class)->get();
        
        $stripe_client = new StripeClient($StripeConfig->getSecretKey());
        $payment_method_id = $request->request->get('payment_method_id');
        $is_save_on = $request->request->get('is_save_on') === "true" ? true : false;

        $stripe_customer_id = $this->registerCustomerToStripe($stripe_client, $Order);
        if(is_array($stripe_customer_id)) { // エラー
            return $this->json($stripe_customer_id);
        }

        $amount = $Order->getPaymentTotal();
        $application_fee_percent = $StripeConfig->getApplicationFeesPercent();
        if ($application_fee_percent) {
            $application_fee = $amount * $application_fee_percent / 100;
        } else {
            $application_fee = null;
        }
        $paymentIntent = $stripe_client->createPaymentIntentWithCustomer(
                                $amount, 
                                $payment_method_id, 
                                $Order, 
                                $is_save_on, 
                                $stripe_customer_id,
                                $application_fee
                            );
        $Customer = $Order->getCustomer();
        if ($Customer) {
            $Customer->setStripeCustomerId($stripe_customer_id);
            $Customer->setCardSaved($is_save_on);
            $this->entityManager->persist($Customer);
            $this->entityManager->flush();
        }
        return $this->json($stripe_client->generateIntentResponse($paymentIntent, $this->eccubeConfig['locale']));
    }

    /**
     * @Route("/shopping/payment/credit_card/detach_method", name="malldevel_detach_method")
     */
    public function detachMethod(Request $request)
    {
        $method_id = $request->request->get('payment_method_id');
        $Order = $this->getOrder();

        if ($Order) {
            $Customer = $Order->getCustomer();
            $StripeConfig = $this->entityManager->getRepository(StripeConfig::class)->get();

            $existing_method = $this->checkExistingMethod($Customer, $StripeConfig);

            if ($existing_method && $existing_method->id == $method_id) {
                $stripe_client = new StripeClient($StripeConfig->getSecretKey());
                $stripe_client->detachMethod($method_id);
                $Customer->setCardSaved(true);
                $this->entityManager->persist($Customer);
                $this->entityManager->flush();

                return $this->json([
                    'success'   =>  true,
                ]);
            }
            return $this->json([
                'success'   =>  false,
                'error'     =>  "そのような済みはありません"
            ]);
        }

    }
    private function getOrder(){
        // BOC validation checking
        $preOrderId = $this->cartService->getPreOrderId();
        /** @var Order $Order */
        return $this->orderHelper->getPurchaseProcessingOrder($preOrderId);
   }
    protected function checkExistingMethod($Customer, $StripeConfig) 
    {
        $stripe_client = new StripeClient($StripeConfig->getSecretKey());

        if (!$Customer) {
            return false;
        }
        if ($Customer->isStripeRegistered()) {
            // check if registered in stripe
            $stripe_customer = $stripe_client->retrieveCustomer($Customer->getStripeCustomerId());
            if (is_array($stripe_customer) || isset($stripe_customer['error'])) {
                if (isset($stripe_customer['error']['code']) && $stripe_customer['error']['code'] == 'resource_missing') {
                    return false;
                }
            }

            // retrieve method
            if ($Customer->isCardSaved()) {
                $method = $stripe_client->retrieveLastPaymentMethodByCustomer($Customer->getStripeCustomerId());
                if (\is_array($method) || !$stripe_client->isPaymentMethodId($method->id)) {
                    return false;
                }
                return $method;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function registerCustomerToStripe(StripeClient $stripe_client, $Order)
    {
        $Customer = $Order->getCustomer();

        $stripe_customer_id = null;
        
        if ($Customer) {
            $stripe_customer_id = $Customer->getStripeCustomerId();

            // validation check for stripe customer
            if ($stripe_customer_id) {
                
                $is_stripe_registered = true;

                $stripe_customer = $stripe_client->retrieveCustomer($stripe_customer_id);
                if (\is_array($stripe_customer) || isset($stripe_customer['error'])) {
                    if(isset($stripe_customer['error']['code']) && $stripe_customer['error']['code'] == 'resource_missing') {
                        $stripe_customer_id = null;
                    }
                } 
            }
        }

        // register customer to stripe
        if (!$stripe_customer_id) {
            if ($Customer) {
                $stripe_customer_id = $stripe_client->createCustomerV2($Order->getEmail(), $Customer->getId(), $Order->getId());
            } else {
                $stripe_customer_id = $stripe_client->createCustomerV2($Order->getEmail(), 0, $Order->getId());
            }
        }
        if (\is_array($stripe_customer_id) && isset($stripe_customer_id['error'])) {
            $error_message = StripeClient::getErrorMessageFromCode($stripe_customer_id['error'], $this->eccubeConfig['locale']);
            return ['error' =>  true, 'message' =>  $error_message];
        }
        return $stripe_customer_id;
    }
    private function enableDeliveryShopFilter($Shop = null) {
        if (!$Shop) return;

        $config = $this->entityManager->getConfiguration();
        $config->addFilter('shopping_delivery_filter', ShoppingDeliveryFilter::class);
        /** @var ShoppingDeliveryFilter $filter */
        $filter = $this->entityManager->getFilters()->enable('shopping_delivery_filter');
        $filter->setShopId($Shop);
        
    }

    /**
     * PaymentMethodをコンテナから取得する.
     *
     * @param Order $Order
     * @param FormInterface $form
     *
     * @return PaymentMethodInterface
     */
    protected function createPaymentMethod(Order $Order, FormInterface $form)
    {
        $PaymentMethod = $this->container->get($Order->getPayment()->getMethodClass());
        $PaymentMethod->setOrder($Order);
        $PaymentMethod->setFormType($form);

        return $PaymentMethod;
    }
}
