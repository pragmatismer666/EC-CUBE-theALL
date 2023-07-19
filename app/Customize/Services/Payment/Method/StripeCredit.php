<?php

namespace Customize\Services\Payment\Method;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Eccube\Entity\Order;
use Eccube\Service\Payment\PaymentMethodInterface;
use Eccube\Service\Payment\PaymentResult;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Payment;
use Eccube\Repository\Master\OrderStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Customize\Entity\StripeConfig;
use Customize\Entity\StripeCreditOrder;
use Customize\Services\StripeClient;

class StripeCredit implements PaymentMethodInterface 
{
    /**
     * @var Order
     */
    protected $Order;
    
    
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var PurchaseFlow
     */
    private $purchaseFlow;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderStatusRepository
     */
    private $orderStatusRepository;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;


    public function __construct(
        PurchaseFlow $shoppingPurchaseFlow,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig)
    {
        $this->purchaseFlow = $shoppingPurchaseFlow;
        $this->entityManager = $entityManager;
        $this->orderStatusRepository = $this->entityManager->getRepository(OrderStatus::class);
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     * 
     * @throws \Eccube\Service\PurchaseFlow\PurchaseException
     */
    public function checkout()
    {
        $result = new PaymentResult();
        $payment_intent_id = $this->form['payment_intent_id']->getData();
        if (empty($payment_intent_id)) {
            $result->setSuccess(false);
            $result->setErrors(['malldevel.stripe.credit.intent.unexpected_error']);
            return $result;
        }

        $this->purchaseFlow->commit($this->Order, new PurchaseContext());
        
        $result = new PaymentResult();
        $StripeConfig = $this->entityManager->getRepository(StripeConfig::class)->get();

        $stripe_client = new StripeClient($StripeConfig['secret_key']);

        $is_capture_on = $StripeConfig->isCaptureOn();

        // BOC capture payment
        if ($is_capture_on) {
            $payment_intent = $stripe_client->capturePaymentIntent($payment_intent_id, $this->Order->getPaymentTotal(), $this->Order->getCurrencyCode());
        } else {
            $payment_intent = $stripe_client->retrievePaymentIntent($payment_intent_id);
        }

        if (\is_array($payment_intent) && isset($payment_intent['error'])) {
            $this->purchaseFlow->rollback($this->Order, new PurchaseContext());
            $error = StripeClient::getErrorMessageFromCode($payment_intent['error'], $this->eccubeConfig['locale']);
            $result->setSuccess(false);
            $result->setErrors([$error]);
            $response = new RedirectResponse($this->generateUrl('shopping'));
            $result->setResponse($response);
            return $result;
        }
        // EOC capture payment
        if ($is_capture_on) {
            $OrderStatus = $this->orderStatusRepository->find(OrderStatus::PAID);
            $this->Order->setOrderStatus($OrderStatus);
            $this->entityManager->persist($this->Order);
            $this->entityManager->flush();
        }
        $this->purchaseFlow->commit($this->Order, new PurchaseContext());


        // Save as stripe order
        $stripe_order = new StripeCreditOrder();
        $stripe_order->setOrder($this->Order);
        if (!empty($payment_intent_id)) {
            $stripe_order->setStripePaymentIntentId($payment_intent_id);
        }
        if ($is_capture_on) {
            foreach($payment_intent->charges as $charge) {
                $stripe_order->setStripeChargeId($charge->id);
                break;
            }
        }
        $stripe_order->setIsChargeCaptured($is_capture_on);
        $Customer =$this->Order->getCustomer();

        if ($Customer instanceof Customer) {
            $stripe_order->setStripeCustomerIdForGuestCheckout($payment_intent->customer);
            $stripe_order->setStripeCustomerIdForGuestCheckout('');
        }

        $stripe_order->setCreatedAt(new \DateTime());
        $this->entityManager->persist($stripe_order);
        $this->entityManager->flush();
        
        $result->setSuccess(true);
        return $result;
    }

    /**
     * {@inheritdoc}
     * 
     * @throws \Eccube\Service\PurchaseFlow\PurchaseException
     */
    public function apply()
    {
        $OrderStatus = $this->orderStatusRepository->find(OrderStatus::PENDING);
        $this->Order->setOrderStatus($OrderStatus);
        $this->purchaseFlow->prepare($this->Order, new PurchaseContext());
    }

    /**
     * {@inheritdoc}
     */
    public function setFormType(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * {@inheritdoc}
     */
    public function verify()
    {
        $result = new PaymentResult();
        
        $OrderItems = $this->Order->getProductOrderItems();
        $Shop = $OrderItems[0]->getProductClass()->getProduct()->getShop();

        // checking shop identify
        foreach($OrderItems as $OrderItem) {
            $pc = $OrderItem->getProductClass(0);
            $temp_shop = $pc->getProduct()->getShop();
            if ($Shop != $temp_shop) {
                $result->setSuccess(false);
                $result->setErrors(['malldevel.stripe.credit.verify.error.shop_not_same']);
                return $result;
            }
        }

        $Payment = $this->entityManager->getRepository(Payment::class)->findOneBy(['method_class' => StripeCredit::class]);
        $min = $Payment->getRuleMin();
        $max = $Payment->getRuleMax();

        $total = $this->Order->getPaymentTotal();

        if($min !== null && $total < $min){
            $result->setSuccess(false);
            $result->setErrors(['malldevel.stripe.credit.verify.error.payment_total.too_small']);
            return $result;
        }
        if($max !== null && $total > $max){
            $result->setSuccess(false);
            $result->setErrors(['malldevel.stripe.credit.verify.error.payment_total.too_much']);
        }
        $result->setSuccess(true);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(Order $Order)
    {
        $this->Order = $Order;
    }
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }
}