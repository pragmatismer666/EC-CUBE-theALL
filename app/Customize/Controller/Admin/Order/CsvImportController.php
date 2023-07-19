<?php

namespace Customize\Controller\Admin\Order;
use Eccube\Controller\Admin\Order\CsvImportController as ParentController;
use Eccube\Repository\ShippingRepository;
use Eccube\Service\OrderStateMachine;
use Eccube\Service\CsvImportService;
use Eccube\Form\Type\Admin\CsvImportType;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Member;
use Eccube\Entity\Shipping;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Customize\Entity\Shop;

class CsvImportController extends ParentController {

    /**
     * @var ShippingRepository
     */
    protected $shippingRepository;

    public function __construct(
        ShippingRepository $shippingRepository,
        OrderStateMachine $orderStateMachine
    ) {
        $this->shippingRepository = $shippingRepository;
        parent::__construct($shippingRepository, $orderStateMachine);
    }
    /**
     * 出荷CSVアップロード
     *
     * @Route("/%eccube_admin_route%/order/shipping_csv_upload", name="admin_shipping_csv_import")
     * @Template("@admin/Order/csv_shipping.twig")
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function csvShipping(Request $request)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();
        $columnConfig = $this->getColumnConfig();
        $errors = [];

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();

                if (!empty($formFile)) {
                    $csv = $this->getImportData($formFile);

                    try {
                        $this->entityManager->getConfiguration()->setSQLLogger(null);
                        $this->entityManager->getConnection()->beginTransaction();

                        $this->loadCsv($csv, $errors);
                        
                        if ($errors) {
                            $this->entityManager->getConnection()->rollBack();
                        } else {
                            $this->entityManager->flush();
                            $this->entityManager->getConnection()->commit();

                            $this->addInfo('admin.common.csv_upload_complete', 'admin');
                        }
                    } finally {
                        $this->removeUploadedFile();
                    }
                }
            }
        }

        return [
            'form' => $form->createView(),
            'headers' => $columnConfig,
            'errors' => $errors,
        ];
    }

    protected function loadCsv(CsvImportService $csv, &$errors) {
        [$Shop, $Member] = $this->getShopAndMember();

        $columnConfig = $this->getColumnConfig();

        if ($csv === false) {
            $errors[] = trans('admin.common.csv_invalid_format');
        }

        // 必須カラムの確認
        $requiredColumns = array_map(function ($value) {
            return $value['name'];
        }, array_filter($columnConfig, function ($value) {
            return $value['required'];
        }));
        $csvColumns = $csv->getColumnHeaders();
        if (count(array_diff($requiredColumns, $csvColumns)) > 0) {
            $errors[] = trans('admin.common.csv_invalid_format');

            return;
        }

        // 行数の確認
        $size = count($csv);
        if ($size < 1) {
            $errors[] = trans('admin.common.csv_invalid_format');

            return;
        }

        $columnNames = array_combine(array_keys($columnConfig), array_column($columnConfig, 'name'));
        
        $headers = $csv->getColumnHeaders();
        
        $columnIndexes = \array_map(function($columnName) use ($headers) {
            return \array_search($columnName, $headers);
        }, $columnNames);
        
        foreach ($csv as $line => $row) {
            
            // 出荷IDがなければエラー
            if (empty($row[$columnIndexes['id']])) {
                // $errors[] = trans('admin.common.csv_invalid_required', ['%line%' => $line + 1, '%name%' => $columnIndexes['id']]);
                continue;
            }

            /* @var Shipping $Shipping */
            $Shipping = is_numeric($row[$columnIndexes['id']]) ? $this->shippingRepository->find($row[$columnIndexes['id']]) : null;
            
            // 存在しない出荷IDはエラー
            if (is_null($Shipping)) {
                $errors[] = trans('admin.common.csv_invalid_not_found', ['%line%' => $line + 1, '%name%' => $columnIndexes['id']]);
                continue;
            }

            $Order = $Shipping->getOrder();
            if (!$Order || ($Shop && $Order->getShop()->getId() != $Shop->getId())) {
                $errors[] = trans('malldevel.admin.order.shipping_csv.upload_error', ['%line%' => $line + 1, '%name%' => $columnIndexes['id']]);
            }

            if (isset($row[$columnIndexes['tracking_number']])) {
                $Shipping->setTrackingNumber($row[$columnIndexes['tracking_number']]);
            }

            if (isset($row[$columnIndexes['shipping_date']])) {
                if ($row[$columnIndexes['shipping_date']]) {
                    // 日付フォーマットが異なる場合はエラー
                    $shippingDate = \DateTime::createFromFormat('Y-m-d', $row[$columnIndexes['shipping_date']]);
                    if ($shippingDate === false) {
                        $errors[] = trans('admin.common.csv_invalid_date_format', ['%line%' => $line + 1, '%name%' => $columnIndexes['shipping_date']]);
                        continue;
                    }
                    $shippingDate->setTime(0, 0, 0);
                } else {
                    $shippingDate = null;
                }
                $Shipping->setShippingDate($shippingDate);

            }

            $Order = $Shipping->getOrder();
            $RelateShippings = $Order->getShippings();
            $allShipped = true;
            foreach ($RelateShippings as $RelateShipping) {
                if (!$RelateShipping->getShippingDate()) {
                    $allShipped = false;
                    break;
                }
            }
            $OrderStatus = $this->entityManager->find(OrderStatus::class, OrderStatus::DELIVERED);
            if ($allShipped) {
                if ($this->orderStateMachine->can($Order, $OrderStatus)) {
                    $this->orderStateMachine->apply($Order, $OrderStatus);
                } else {
                    $from = $Order->getOrderStatus()->getName();
                    $to = $OrderStatus->getName();
                    $errors[] = trans('admin.order.failed_to_change_status', [
                        '%name%' => $Shipping->getId(),
                        '%from%' => $from,
                        '%to%' => $to,
                    ]);
                }
            }
        }
    }

    protected function getShopAndMember()
    {
        $Member = $this->getUser();

        if (!($Member instanceof Member)) {
            throw new NotFoundHttpException("Bad request");
        }
        if ($Member->getRole() !== 'ROLE_ADMIN' && empty($Member->getShop())) {
            throw new NotFoundHttpException("Invalid request ");
        }
        return [$Member->getShop(), $Member];
        
    }
}