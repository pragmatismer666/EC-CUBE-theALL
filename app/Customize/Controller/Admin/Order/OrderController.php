<?php

namespace Customize\Controller\Admin\Order;

use Eccube\Common\EccubeConfig;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Order;
use Eccube\Entity\Customer;
use Eccube\Entity\Member;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Master\CsvType;
use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Eccube\Form\Type\Admin\OrderPdfType;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\PaymentRepository;
use Eccube\Repository\Master\SexRepository;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\ProductStockRepository;
use Eccube\Repository\OrderPdfRepository;
use Eccube\Controller\Admin\Order\OrderController as ParentController;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Eccube\Service\CsvExportService;
use Eccube\Service\OrderStateMachine;
use Eccube\Service\MailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Customize\Services\StripeClient;
use Customize\Services\Payment\Method\StripeCredit;
use Customize\Repository\StripeConfigRepository;
use Customize\Repository\StripeCreditOrderRepository;
use Customize\Services\OrderPdfService;

class OrderController extends ParentController
{

    /**
     * @var StripeConfigRepository
     */
    private $stripeConfigRepository;

    /**
     * @var StripeCreditOrderRepository
     */
    private $stripeCreditOrderRepository;

    /**
     * @var \Customize\Services\OrderPdfService
     */
    protected $orderPdfService;

    public function __construct(
        PurchaseFlow $orderPurchaseFlow,
        CsvExportService $csvExportService,
        CustomerRepository $customerRepository,
        PaymentRepository $paymentRepository,
        SexRepository $sexRepository,
        OrderStatusRepository $orderStatusRepository,
        PageMaxRepository $pageMaxRepository,
        ProductStatusRepository $productStatusRepository,
        ProductStockRepository $productStockRepository,
        OrderRepository $orderRepository,
        OrderPdfRepository $orderPdfRepository,
        ValidatorInterface $validator,
        OrderStateMachine $orderStateMachine,
        MailService $mailService,
        StripeConfigRepository $stripeConfigRepository,
        StripeCreditOrderRepository $stripeCreditOrderRepository,
        OrderPdfService $orderPdfService
    )
    {
        $this->stripeConfigRepository = $stripeConfigRepository;
        $this->stripeCreditOrderRepository = $stripeCreditOrderRepository;
        $this->orderPdfService = $orderPdfService;
        parent::__construct(
            $orderPurchaseFlow,
            $csvExportService,
            $customerRepository,
            $paymentRepository,
            $sexRepository,
            $orderStatusRepository,
            $pageMaxRepository,
            $productStatusRepository,
            $productStockRepository,
            $orderRepository,
            $orderPdfRepository,
            $validator,
            $orderStateMachine,
            $mailService
        );
    }


    /**
     * @Route("/%eccube_admin_route%/order/{id}/stripe_capture", requirements={"id" = "\d+"}, name="malldevel_admin_stripe_capture")
     */
    public function charge(Request $request, $id = null, RouterInterface $router)
    {
        $Order = $this->orderRepository->find($id);
        if ($Order === null) {
            $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
            return $this->redirectToRoute('admin_order');
        }
        
        $Payment = $Order->getPayment();
        
        if ($Payment->getMethodClass() != StripeCredit::class) {
            return $this->redirectToRoute('admin_order_edit', ['id' =>  $Order->getId()]);
        }
        
        $StripeConfig = $this->stripeConfigRepository->get();
        
        $stripeOrder = $this->stripeCreditOrderRepository->findOneBy(['Order' =>  $Order]);
        if (!$stripeOrder) {
            $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
            return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
        }

        //BOC check if refunded
        if ($stripeOrder->getIsChargeRefunded()) {
            $this->addError('malldevel.admin.stripe.order.error.refunded', 'admin');
            return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
        }
        //EOC check if refunded

        //BOC check if already captured
        if ($stripeOrder->getIsChargeCaptured()) {
            $this->addError('malldevel.admin.stripe.order.error.already_captured', 'admin');
            return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
        }
        //EOC check if already captured

        //BOC retrieve and check if captured for order_id already
        $stripeClient = new StripeClient($StripeConfig['secret_key']);

        if($stripeClient->isPaymentIntentId($stripeOrder->getStripePaymentIntentId())) { // new version for 3ds2
            $paymentIntent = $stripeClient->retrievePaymentIntent($stripeOrder->getStripePaymentIntentId());
            if( is_array($paymentIntent) && isset($paymentIntent['error']) ) {
                $this->addError(StripeClient::getErrorMessageFromCode($paymentIntent['error'], $this->eccubeConfig['locale']), 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }

            if($paymentIntent->metadata->order==$Order->getId() && $paymentIntent->status=='succeeded'){
                //BOC update charge id and capture status
                foreach($paymentIntent->charges as $charge) {
                    $stripeOrder->setStripeChargeId($charge->id);
                    break;
                }
                $stripeOrder->setIsChargeCaptured(true);
                $this->entityManager->persist($stripeOrder);
                $this->entityManager->flush($stripeOrder);
                //EOC update charge id and capture status

                //BOC update payment status
                $stripeChargeID = $stripeOrder->getStripeChargeId();
                if (!empty($stripeChargeID)) {
                    $Today = new \DateTime();
                    $Order->setPaymentDate($Today);
                    $OrderStatus = $this->orderStatusRepository->find(OrderStatus::PAID);
                    $Order->setOrderStatus($OrderStatus);
                    $this->entityManager->persist($Order);
                    $this->entityManager->flush($Order);
                }
                //EOC update payment status

                $this->addError('malldevel.admin.stripe.order.error.already_captured', 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
            //EOC retrieve and check if captured for order_id already

            //BOC capture payment
            // $this->writeRequestLog($Order, 'capturePaymentIntent');
            $paymentIntent = $stripeClient->capturePaymentIntent($paymentIntent, $Order->getPaymentTotal(), $Order->getCurrencyCode());
            // $this->writeResponseLog($Order, 'capturePaymentIntent', $paymentIntent);
            //EOC capture payment

            //BOC check if error
            if (is_array($paymentIntent) && isset($paymentIntent['error'])) {
                $errorMessage = StripeClient::getErrorMessageFromCode($paymentIntent['error'], $this->eccubeConfig['locale']);

                $this->addError($errorMessage, 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            } //EOC check if error
            else {
                //BOC update charge id and capture status
                foreach($paymentIntent->charges as $charge) {
                    $stripeOrder->setStripeChargeId($charge->id);
                    break;
                }
                $stripeOrder->setIsChargeCaptured(true);
                $this->entityManager->persist($stripeOrder);
                $this->entityManager->flush($stripeOrder);
                //EOC update charge id and capture status

                //BOC update payment status
                $stripeChargeID = $stripeOrder->getStripeChargeId();
                if (!empty($stripeChargeID)) {
                    $Today = new \DateTime();
                    $Order->setPaymentDate($Today);
                    $OrderStatus = $this->orderStatusRepository->find(OrderStatus::PAID);
                    $Order->setOrderStatus($OrderStatus);
                    $this->entityManager->persist($Order);
                    $this->entityManager->flush($Order);
                }
                //EOC update payment status

                $this->addSuccess('malldevel.admin.stripe.order.success.capture', 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
        } else if ($stripeClient->isStripeToken($stripeOrder->getStripePaymentIntentId())) {
            //BOC check if Stripe Customer
            $Customer = $Order->getCustomer();
            if ($Customer instanceof Customer) {
                if ($Customer->isStripeRegistered()) {
                    $stripeCustomerId = $Customer->getStripeCustomerId();
                } else {
                    $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
                    return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
                }
            } else {
                $stripeCustomerId = $stripeOrder->getStripeCustomerIdForGuestCheckout();
            }
            
            //BOC capture payment
            $chargeResult = $stripeClient->createChargeWithCustomer($Order->getPaymentTotal(), $stripeCustomerId, $Order->getId(), true);
            //EOC capture payment

            //BOC check if error
            if (is_array($chargeResult) && isset($chargeResult['error'])) {
                $errorMessage = StripeClient::getErrorMessageFromCode($chargeResult['error'], $this->eccubeConfig['locale']);

                $this->addError($errorMessage, 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            } //EOC check if error
            else {

                //BOC update charge id and capture status
                $stripeOrder->setStripeChargeId($chargeResult->__get('id'));
                $stripeOrder->setIsChargeCaptured(true);
                $this->entityManager->persist($stripeOrder);
                $this->entityManager->flush($stripeOrder);
                //EOC update charge id and capture status

                //BOC update payment status
                $stripeChargeID = $stripeOrder->getStripeChargeId();
                if (!empty($stripeChargeID)) {
                    $Today = new \DateTime();
                    $Order->setPaymentDate($Today);
                    $OrderStatus = $this->orderStatusRepository->find(OrderStatus::PAID);
                    $Order->setOrderStatus($OrderStatus);
                    $this->entityManager->persist($Order);
                    $this->entityManager->flush($Order);
                }
                //EOC update payment status
                $this->addSuccess('malldevel.admin.stripe.order.success.capture', 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
        } else {
            $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
            return $this->redirectToRoute('admin_order');
        }
    }

    /**
     * @Route("/%eccube_admin_route%/order/{id}/refund_transaction", requirements={"id" = "\d+"}, name="malldevel_admin_stripe_refund")
     */
    public function refund(Request $request, $id = null, RouterInterface $router)
    {
        //BOC check if order exist
        $Order = $this->orderRepository->find($id);
        if (null === $Order) {
            $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
            return $this->redirectToRoute('admin_order');
        }
        //EOC check if order exist

		$StripeConfig = $this->stripeConfigRepository->get();

        if ($request->getMethod() == 'POST'){

            //BOC check if Stripe Order
            $stripeOrder = $this->stripeCreditOrderRepository->findOneBy(array('Order' => $Order));
            if (null === $stripeOrder) {
                $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
            //EOC check if Stripe Order

            //BOC check if refunded
            if ($stripeOrder->getIsChargeRefunded()) {
                $this->addError('malldevel.admin.stripe.order.error.refunded', 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
            //EOC check if refunded

            //BOC retrieve and check if valid charge id and not already refunded
            $stripeClient = new StripeClient($StripeConfig->getSecretKey());
            $chargeForOrder = $stripeClient->retrieveCharge($stripeOrder->getStripeChargeId());
            if (isset($chargeForOrder)) {
                if ($chargeForOrder->refunded) {

                    //BOC update charge id and capture status
                    $stripeOrder->setIsChargeRefunded(true);
                    $this->entityManager->persist($stripeOrder);
                    $this->entityManager->flush($stripeOrder);
                    //EOC update charge id and capture status

                    //BOC update Order Status
                    $OrderStatus = $this->orderStatusRepository->find(OrderStatus::CANCEL);
                    $Order->setOrderStatus($OrderStatus);
                    $this->entityManager->persist($Order);
                    $this->entityManager->flush($Order);
                    //EOC update Order Status

                    $this->addError('malldevel.admin.stripe.order.error.refunded', 'admin');
                    return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
                }
            } else {
                $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
            //EOC retrieve and check if valid charge id and not already refunded

            //BOC refund option and amount calculation
            $refund_option = $request->request->get('refund_option');
            $refund_amount = 0;
            //BOC partial refund
            if ($refund_option == 3) {
                $refund_amount = $request->request->get('refund_amount');
                if (empty($refund_amount) || !is_int($refund_amount+0)) {
                    $this->addError('malldevel.admin.stripe.order.refund_amount.error.invalid', 'admin');
                    return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
                } else if($refund_amount>$Order->getPaymentTotal()){
                    $this->addError('malldevel.admin.stripe.order.refund_amount.error.exceeded', 'admin');
                    return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
                }
            }
            //EOC partial refund
            //BOC calculate refund amount based on fees entered
            if($refund_option==2){
                if($StripeConfig->getStripeFeesPercent() == 0){
                    $this->addError('malldevel.admin.stripe.order.refund_option.error.invalid', 'admin');
                    return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
                }
                $refund_amount=floor($Order->getPaymentTotal()-($Order->getPaymentTotal()*($StripeConfig->getStripeFeesPercent()/100)));
            }
            //EOC calculate refund amount based on fees entered
            //BOC full refund option
            if($refund_option==1){
                $refund_amount=floor($Order->getPaymentTotal());
            }
            //EOC full refund option
            //BOC refund option and amount calculation

            //BOC refund payment
            $chargeResult = $stripeClient->createRefund($stripeOrder->getStripeChargeId(),$refund_amount,$Order->getCurrencyCode());
            //EOC refund payment

            //BOC check if error
            if (is_array($chargeResult) && isset($chargeResult['error'])) {
                $errorMessage = StripeClient::getErrorMessageFromCode($chargeResult['error'], $this->eccubeConfig['locale']);

                $this->addError($errorMessage, 'admin');
                return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
            }
            //EOC check if error

            //BOC update charge id and capture status
            $stripeOrder->setIsChargeRefunded(true);
            $stripeOrder->setSelectedRefundOption($refund_option);
            $stripeOrder->setRefundedAmount($refund_amount);
            $this->entityManager->persist($stripeOrder);
            $this->entityManager->flush($stripeOrder);
            //EOC update charge id and capture status

            //BOC update Order Status
            $OrderStatus = $this->orderStatusRepository->find(OrderStatus::CANCEL);
            $Order->setOrderStatus($OrderStatus);
            $this->entityManager->persist($Order);
            $this->entityManager->flush($Order);
            //EOC update Order Status

            $this->addSuccess('malldevel.admin.stripe.order.status.refund_success', 'admin');
            return $this->redirectToRoute('admin_order_edit', ['id' => $Order->getId()]);
        } else {
            $this->addError('malldevel.admin.stripe.order.invalid_request', 'admin');
            return $this->redirectToRoute('admin_order');
        }
    }

    /**
     * 受注CSVの出力.
     *
     * @Route("/%eccube_admin_route%/order/export/order", name="admin_order_export_order")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function exportOrder(Request $request)
    {
        $filename = 'order_'.(new \DateTime())->format('YmdHis').'.csv';
        $response = $this->exportCsv($request, CsvType::CSV_TYPE_ORDER, $filename);
        log_info('受注CSV出力ファイル名', [$filename]);

        return $response;
    }

    /**
     * 配送CSVの出力.
     *
     * @Route("/%eccube_admin_route%/order/export/shipping", name="admin_order_export_shipping")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function exportShipping(Request $request)
    {
        $filename = 'shipping_'.(new \DateTime())->format('YmdHis').'.csv';
        $response = $this->exportCsv($request, CsvType::CSV_TYPE_SHIPPING, $filename);
        log_info('配送CSV出力ファイル名', [$filename]);

        return $response;
    }

    /**
     * @param Request $request
     * @param $csvTypeId
     * @param string $fileName
     *
     * @return StreamedResponse
     */
    protected function exportCsv(Request $request, $csvTypeId, $fileName)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request, $csvTypeId) {
            // CSV種別を元に初期化.
            $this->csvExportService->initCsvType($csvTypeId);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 受注データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getOrderQueryBuilder($request);

            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function ($entity, $csvService) use ($request) {
                $Csvs = $csvService->getCsvs();

                $Order = $entity;
                $OrderItems = $Order->getOrderItems();

                foreach ($OrderItems as $OrderItem) {
                    $ExportCsvRow = new ExportCsvRow();

                    // CSV出力項目と合致するデータを取得.
                    foreach ($Csvs as $Csv) {
                        // 受注データを検索.
                        $ExportCsvRow->setData($csvService->getData($Csv, $Order));
                        if ($ExportCsvRow->isDataNull()) {
                            // 受注データにない場合は, 受注明細を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $OrderItem));
                        }
                        if ($ExportCsvRow->isDataNull() && $Shipping = $OrderItem->getShipping()) {
                            // 受注明細データにない場合は, 出荷を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $Shipping));
                        }
                        if ($ExportCsvRow->isDataNull() && $Shop = $Order->getShop()) {
                            // 受注明細データにない場合は, 出荷を検索.
                            $ExportCsvRow->setData($csvService->getData($Csv, $Shop));
                        }
                        if ($Csv->getEntityName() === "Eccube\\\\Entity\\\\Member") {
                            $Members = $Shop->getMembers();
                            $member_names = [];
                            foreach ($Members as $Member) {
                                $member_names[] = $Member->getName();
                            }
                            $ExportCsvRow->setData(\implode(',', $member_names));
                        }

                        $event = new EventArgs(
                            [
                                'csvService' => $csvService,
                                'Csv' => $Csv,
                                'OrderItem' => $OrderItem,
                                'ExportCsvRow' => $ExportCsvRow,
                            ],
                            $request
                        );
                        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_CSV_EXPORT_ORDER, $event);

                        $ExportCsvRow->pushData();
                    }

                    //$row[] = number_format(memory_get_usage(true));
                    // 出力.
                    $csvService->fputcsv($ExportCsvRow->getRow());
                }
            });
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName);
        $response->send();

        return $response;
    }

    /**
     * @Route("/%eccube_admin_route%/order/export/pdf/download", name="admin_order_pdf_download")
     * @Template("@admin/Order/order_pdf.twig")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportPdfDownload(Request $request, \Eccube\Service\OrderPdfService $orderPdfService)
    {
        return parent::exportPdfDownload($request, $this->orderPdfService); // TODO: Change the autogenerated stub
    }
}
