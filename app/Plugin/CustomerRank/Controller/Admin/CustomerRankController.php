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

use Eccube\Repository\CsvRepository;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerRankController extends \Eccube\Controller\AbstractController
{
    /**
     * @var CustomerRepository
     */
    private $customerRankRepository;

    private $csvRepository;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(
            CustomerRankRepository $customerRankRepository,
            CsvRepository $csvRepository
            )
    {
        $this->customerRankRepository = $customerRankRepository;
        $this->csvRepository = $csvRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/customer/rank", name="admin_customer_rank")
     * @Template("@CustomerRank/admin/Customer/index.twig")
     */
    public function index(Request $request)
    {
        $CustomerRanks = $this->customerRankRepository->getList();

        return [
            'CustomerRanks' => $CustomerRanks,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/customer/rank/{id}/delete", requirements={"id" = "\d+"}, name="admin_customer_rank_delete",methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        $this->isTokenValid();

        $TargetCustomerRank = $this->customerRankRepository->find($id);
        if (!$TargetCustomerRank) {
            throw new NotFoundHttpException();
        }

        $status = false;
        $customer_rank_id = $TargetCustomerRank->getId();

        $Csv = $this->csvRepository->findOneBy(['field_name' => 'customerrank_price_' . $customer_rank_id]);
        if(!is_null($Csv)){
            $this->entityManager->remove($Csv);
        }

        $status = $this->customerRankRepository->delete($TargetCustomerRank);

        if ($status === true) {
            $this->addSuccess('admin.customer.rank.delete.complete', 'admin');
        } else {
            $this->addError('admin.customer.rank.delete.error', 'admin');
        }

        return $this->redirectToRoute('admin_customer_rank');
    }

    /**
     * @Route("/%eccube_admin_route%/customer/rank/priority/move", name="admin_customer_rank_move",methods={"POST","GET"})
     */
    public function movePriority(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $priorities = $request->request->all();
            foreach ($priorities as $customerRankId => $priority) {
                $CustomerRank = $this->customerRankRepository
                    ->find($customerRankId);
                $CustomerRank->setPriority($priority);
                $this->entityManager->persist($CustomerRank);
            }
            $this->entityManager->flush();
        }
        return new Response();
    }
}