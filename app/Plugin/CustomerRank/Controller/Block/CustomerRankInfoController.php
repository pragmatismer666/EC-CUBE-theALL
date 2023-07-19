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

namespace Plugin\CustomerRank\Controller\Block;

use Eccube\Controller\AbstractController;
use Plugin\CustomerRank\Service\CustomerRankService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CustomerRankInfoController extends AbstractController
{

    private $customerRankService;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(
            CustomerRankService $customerRankService
            )
    {
        $this->customerRankService = $customerRankService;
    }

    /**
     * @Route("/block/customer_rank_info", name="block_customer_rank_info")
     * @Template("Block/customer_rank_info.twig")
     */
    public function index(Request $request)
    {

        $Customer = $this->customerRankService->getLoginCustomer();

        if($Customer){
            $checkCustomerRank = $this->customerRankService->getCheckRank($Customer, true);
            if($checkCustomerRank)$nextCustomerRank = $this->customerRankService->getNextRank($checkCustomerRank);
            $currentCondition = $this->customerRankService->getCurrentCondition($Customer, true);

            $currentCustomerRank = $Customer->getCustomerRank();
            if(!isset($nextCustomerRank) && $currentCustomerRank)$nextCustomerRank = $this->customerRankService->getNextRank($currentCustomerRank);
            if($currentCustomerRank){
                if($currentCustomerRank->getFixedFlg()){
                    $checkCustomerRank = [];
                    $nextCustomerRank = [];
                }
            }
            if($currentCustomerRank && $checkCustomerRank){
                if($currentCustomerRank->getId() == $checkCustomerRank->getId()){
                    $checkCustomerRank = [];
                }
            }
        }

        if(!isset($checkCustomerRank))$checkCustomerRank = [];
        if(!isset($currentCustomerRank))$currentCustomerRank = [];
        if(!isset($nextCustomerRank))$nextCustomerRank = [];
        if(!isset($currentCondition))$currentCondition = [];

        return [
                    'CurrentCustomerRank' => $currentCustomerRank,
                    'NextCustomerRank' => $nextCustomerRank,
                    'CheckCustomerRank' => $checkCustomerRank,
                    'CurrentCondition' => $currentCondition,
        ];
    }
}
