<?php
/*
* Plugin Name : CustomerRank
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\CustomerRank\Controller\Admin;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Eccube\Common\Constant;
use Eccube\Controller\Admin\AbstractCsvImportController;
use Eccube\Entity\BaseInfo;
use Eccube\Exception\CsvImportException;
use Eccube\Form\Type\Admin\CsvImportType;
use Eccube\Repository\CustomerRepository;
use Eccube\Service\CsvImportService;
use Eccube\Util\StringUtil;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CsvImportController extends AbstractCsvImportController
{
    private $customerRepository;

    private $customerRankRepository;

    private $errors = [];

    public function __construct(
            CustomerRepository $customerRepository,
            CustomerRankRepository $customerRankRepository
            )
    {
        $this->customerRepository = $customerRepository;
        $this->customerRankRepository = $customerRankRepository;
    }

    /**
     *
     * @Route("/%eccube_admin_route%/customer/customer_rank_csv_upload", name="admin_customer_rank_csv_import")
     * @Template("@CustomerRank/admin/Customer/import_csv.twig")
     */
    public function import(Request $request)
    {
        $builder = $this->formFactory->createBuilder(CsvImportType::class);
        $builder->remove('Shop');
        $form = $builder->getForm();
        $headers = $this->getCsvHeader();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {

                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }
                    $getId = function ($item) {
                        return $item['id'];
                    };
                    $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
                        return $value['required'];
                    })));
                    $columnHeaders = $data->getColumnHeaders();

                    if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    $size = count($data);
                    if ($size < 1) {
                        $this->addErrors(trans('admin.common.csv_invalid_no_data'));
                        return $this->renderWithError($form, $headers, false);
                    }

                    $headerSize = count($columnHeaders);
                    $headerByKey = array_flip(array_map($getId, $headers));

                    $this->entityManager->getConfiguration()->setSQLLogger(null);
                    $this->entityManager->getConnection()->beginTransaction();
                    // CSVファイルの登録処理
                    foreach ($data as $line => $row) {
                        if ($headerSize != count($row)) {
                            $message = trans('admin.common.csv_invalid_format_line', ['%line%' => $line]);
                            $this->addErrors($message);
                            return $this->renderWithError($form, $headers);
                        }

                        if($row[$headerByKey['customer_id']] == '') {
                            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['customer_id']]);
                            $this->addErrors($message);

                            return $this->renderWithError($form, $headers);
                        }

                        $customer_id = $row[$headerByKey['customer_id']];
                        if(!is_numeric($customer_id)){
                            $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['customer_id']]);
                            $this->addErrors($message);

                            return $this->renderWithError($form, $headers);
                        }

                        $Customer = $this->customerRepository->find($customer_id);

                        if(strlen($row[$headerByKey['customer_rank_name']]) > 0){
                            $CustomerRank = $this->customerRankRepository->findOneBy(['name' => $row[$headerByKey['customer_rank_name']]]);
                            if(!is_null($Customer)){
                                $Customer->setCustomerRank($CustomerRank);
                                $this->entityManager->persist($Customer);
                            }
                        }
                    }

                    $this->entityManager->flush();
                    $this->entityManager->getConnection()->commit();
                    $this->entityManager->close();

                    $this->addSuccess('admin.customer.import.save.complete', 'admin');
                }

            }

        }

        return $this->renderWithError($form, $headers);
    }
    /**
     *
     * @Route("/%eccube_admin_route%/customer/customer_rank_csv_template", name="admin_customer_rank_csv_template")
     */
    public function csvTemplate(Request $request)
    {

        $headers = $this->getCsvHeader();
        $filename = 'customer_rank.csv';

        return $this->sendTemplateResponse($request, array_keys($headers), $filename);
    }

    protected function renderWithError($form, $headers, $rollback = true)
    {
        if ($this->hasErrors()) {
            if ($rollback) {
                $this->entityManager->getConnection()->rollback();
            }
        }

        $this->removeUploadedFile();

        return [
            'form' => $form->createView(),
            'headers' => $headers,
            'errors' => $this->errors,
        ];
    }


    /**
     * 登録、更新時のエラー画面表示
     *
     */
    protected function addErrors($message)
    {
        $e = new CsvImportException($message);
        $this->errors[] = $e;
    }

    /**
     * @return array
     */
    protected function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     * @return boolean
     */
    protected function hasErrors()
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * ヘッダー定義
     */
    protected function getCsvHeader()
    {
        return [
            trans('customerrank.admin.rank.import_csv.column.id') => [
                'id' => 'customer_id',
                'description' => '',
                'required' => true,
            ],
            trans('customerrank.admin.rank.import_csv.column.second_name') => [
                'id' => '',
                'description' => '',
                'required' => false,
            ],
            trans('customerrank.admin.rank.import_csv.column.first_name') => [
                'id' => '',
                'description' => '',
                'required' => false,
            ],
            trans('customerrank.admin.rank.import_csv.column.rank_name') => [
                'id' => 'customer_rank_name',
                'description' => '',
                'required' => true,
            ],
        ];
    }
}
