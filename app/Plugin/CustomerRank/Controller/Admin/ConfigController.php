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

use Eccube\Entity\Customer;
use Plugin\CustomerRank\Repository\ConfigRepository;
use Plugin\CustomerRank\Repository\ConfigStatusRepository;
use Plugin\CustomerRank\Entity\CustomerRank;
use Plugin\CustomerRank\Entity\CustomerRankConfig;
use Plugin\CustomerRank\Entity\ConfigStatus;
use Plugin\CustomerRank\Form\Type\Admin\ConfigType;
use Plugin\CustomerRank\Service\CustomerRankService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ConfigController extends \Eccube\Controller\AbstractController
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var ConfigStatusRepository
     */
    private $configStatusRepository;

    private $customerRankService;

    /**
     * ConfigController constructor.
     * @param ConfigRepository $configRepository
     */
    public function __construct(
            ConfigRepository $configRepository,
            ConfigStatusRepository $configStatusRepository,
            CustomerRankService $customerRankService
            )
    {
        $this->configRepository = $configRepository;
        $this->configStatusRepository = $configStatusRepository;
        $this->customerRankService = $customerRankService;
    }

    /**
     * @Route("/%eccube_admin_route%/setting/customer_rank", name="admin_setting_customerrank")
     * @Template("@CustomerRank/admin/Setting/config.twig")
     */
    public function index(Request $request)
    {
        $form = $this->formFactory
                ->createBuilder(ConfigType::class)
                ->getForm();

        $Configs = $this->configRepository->findAll();

        foreach($Configs as $config){
            if(is_null($config->getValue()) || is_array($config->getValue()))continue;
            $form[$config->getName()]->setData($config->getValue());
        }


        $ConfigStatus = $this->configStatusRepository->findAll();
        $Status = [];
        foreach($ConfigStatus as $configStatus){
            $Status[] = $configStatus->getOrderStatus();
        }

        $form['target_status']->setData($Status);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            switch($request->get('mode')){
                case 'regist':

                    if ($form->isValid()) {
                        //設定内容を一度クリア
                        foreach($ConfigStatus as $status){
                            $this->entityManager->remove($status);
                        }
                        foreach($Configs as $config){
                            $this->entityManager->remove($config);
                        }
                        $this->entityManager->flush();

                        //設定登録
                        $Values = $form->getData();
                        foreach($Values as $name => $value){
                            if($name == 'target_status'){
                                $TargetStatus = $Values[$name];
                                foreach($TargetStatus as $orderStatus){
                                    $order_status_id = $orderStatus->getId();
                                    if(is_null($order_status_id))continue;
                                    $ConfigStatus = new ConfigStatus();
                                    $ConfigStatus->setOrderStatus($orderStatus);
                                    $this->entityManager->persist($ConfigStatus);
                                }
                            }else{
                                $Config = new CustomerRankConfig();
                                $Config->setName($name);
                                $Config->setValue($value);
                                $this->entityManager->persist($Config);
                            }
                        }
                        $this->entityManager->flush();

                        $this->addSuccess('admin.setting.customerrank.save.complete', 'admin');
                    }
                    break;
                case 'rank_check':
                    $Customers = $this->entityManager->getRepository(Customer::class)->findAll();
                    $term = $this->customerRankService->getConfig('term');
                    $term_start = $this->customerRankService->getConfig('term_start');
                    $rank_down = $this->customerRankService->getConfig('rank_down');
                    $CustomerRanks = $this->entityManager->getRepository(CustomerRank::class)->findBy([],['priority' => 'DESC']);
                    $ToDay = new \DateTime();
                    if($term != 0){
                        foreach($Customers as $Customer){
                            $currentCustomerRank = null;
                            $currentCustomerRank = $Customer->getCustomerRank();
                            if(!is_null($currentCustomerRank)){
                                $customer_rank_id = $currentCustomerRank->getId();
                                if($currentCustomerRank->getFixedFlg() == true)continue;
                            }
                            if(!isset($customer_rank_id))$customer_rank_id = '';
                            $currentCondition = $this->customerRankService->getCurrentCondition($Customer);
                            
                            $checkCustomerRank = null;
                            foreach($CustomerRanks as $CustomerRank){
                                $condAmount = $CustomerRank->getCondAmount();
                                $condBuytimes = $CustomerRank->getCondBuytimes();

                                if((!is_null($condAmount) && $condAmount <= $currentCondition['total_amount']) ||
                                   (!is_null($condBuytimes) && $condBuytimes <= $currentCondition['total_buytimes'])){
                                    $checkCustomerRank = $CustomerRank;
                                    break;
                                }
                            }

                            if($rank_down == 1){
                                if(!is_null($checkCustomerRank) && !is_null($currentCustomerRank)){
                                    if($currentCustomerRank->getPriority() > $checkCustomerRank->getPriority()){
                                        $checkCustomerRank = $currentCustomerRank;
                                    }
                                }else{
                                    if(!is_null($currentCustomerRank))$checkCustomerRank = $currentCustomerRank;
                                }
                            }
                            $changeCustomerRank = $checkCustomerRank;

                            if(!is_null($changeCustomerRank)){
                                if(!isset($currentCustomerRank) || $changeCustomerRank != $currentCustomerRank){
                                    $Customer->setCustomerRank($changeCustomerRank);
                                }
                            }else{
                                $Customer->setCustomerRank(null);
                            }
                            $Customer->setCheckDate($ToDay);
                            $this->entityManager->persist($Customer);
                        }
                        $this->entityManager->flush();
                    }
                    $this->addSuccess('admin.setting.customerrank.rank_check.complete', 'admin');
                    break;
                default:
                    break;
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}