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

use Eccube\Repository\Master\CsvTypeRepository;
use Eccube\Repository\CsvRepository;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Plugin\CustomerRank\Entity\CustomerRank;
use Plugin\CustomerRank\Form\Type\Admin\CustomerRankType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CustomerRankEditController extends \Eccube\Controller\AbstractController
{
    /**
     * @var CustomerRepository
     */
    private $customerRankRepository;

    private $csvTypeRepository;

    private $csvRepository;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(
            CustomerRankRepository $customerRankRepository,
            CsvTypeRepository $csvTypeRepository,
            CsvRepository $csvRepository
            )
    {
        $this->customerRankRepository = $customerRankRepository;
        $this->csvTypeRepository = $csvTypeRepository;
        $this->csvRepository = $csvRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/customer/rank/edit", name="admin_customer_rank_new")
     * @Route("/%eccube_admin_route%/customer/rank/edit/{id}", requirements={"id" = "\d+"}, name="admin_customer_rank_edit")
     * @Template("@CustomerRank/admin/Customer/edit.twig")
     */
    public function index(Request $request, $id = null)
    {
        // 編集
        if ($id) {
            $CustomerRank = $this->customerRankRepository->find($id);

            if (is_null($CustomerRank)) {
                throw new NotFoundHttpException();
            }
        // 新規登録
        } else {
            $CustomerRank = new CustomerRank();
            $CustomerRank->setInitialFlg(false);
            $CustomerRank->setFixedFlg(false);
        }

        $form = $this->formFactory
                ->createBuilder(CustomerRankType::class, $CustomerRank)
                ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if($this->customerRankRepository->save($CustomerRank)){
                    //CSV項目追加
                    $now = new \DateTime();

                    $CsvType = $this->csvTypeRepository->find(\Eccube\Entity\Master\CsvType::CSV_TYPE_PRODUCT);
                    $sort_no = 0;
                    try {
                        $sort_no = $this->entityManager->createQueryBuilder()
                            ->select('MAX(c.sort_no)')
                            ->from('Eccube\Entity\Csv','c')
                            ->where('c.CsvType = :csvType')
                            ->setParameter(':csvType',$CsvType)
                            ->getQuery()
                            ->getSingleScalarResult();
                    } catch (\Exception $exception) {
                    }
                    if (!$sort_no) {
                        $sort_no = 0;
                    }

                    $Csv = $this->csvRepository->findOneBy(['field_name' => 'customerrank_price_' . $CustomerRank->getId()]);
                    if(is_null($Csv)){
                        $Csv = new \Eccube\Entity\Csv();
                        $Csv->setCsvType($CsvType);
                        $Csv->setEntityName('Plugin\\CustomerRank\\Entity\\CustomerPrice');
                        $Csv->setFieldName('customerrank_price_'.$CustomerRank->getId());
                        $Csv->setEnabled(true);
                        $Csv->setSortNo($sort_no + 1);
                        $Csv->setCreateDate($now);
                    }
                    $Csv->setDispName($CustomerRank->getName().trans('customerrank.common.customer_price'));
                    $Csv->setUpdateDate($now);
                    $this->entityManager->persist($Csv);

                    $this->entityManager->flush();

                    $this->addSuccess('admin.customerrank.save.complete', 'admin');
                    return $this->redirectToRoute('admin_customer_rank');
                }
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}