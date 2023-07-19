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

namespace Plugin\CustomerRank\Service;

use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Plugin\CustomerRank\Repository\CustomerPriceRepository;
use Plugin\CustomerRank\Repository\ConfigRepository;
use Plugin\CustomerRank\Repository\ConfigStatusRepository;
use Plugin\CustomerRank\Entity\CustomerPrice;
use Plugin\CustomerRank\Entity\CustomerRankConfig;
use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Eccube\Event\RenderEvent;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\ProductClassRepository;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CustomerRankService
{

    private $authorizationChecker;

    private $tokenStorage;

    private $entityManager;

    private $productClassRepository;

    private $customerPriceRepository;

    private $configRepository;

    private $configStatusRepository;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(
            AuthorizationCheckerInterface $authorizationChecker,
            TokenStorageInterface $tokenStorage,
            EntityManagerInterface $entityManager,
            ProductClassRepository $productClassRepository,
            CustomerRankRepository $customerRankRepository,
            CustomerPriceRepository $customerPriceRepository,
            ConfigRepository $configRepository,
            ConfigStatusRepository $configStatusRepository
            )
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->productClassRepository = $productClassRepository;
        $this->customerRankRepository = $customerRankRepository;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->configRepository = $configRepository;
        $this->configStatusRepository = $configStatusRepository;
    }

    public function getLoginCustomer(){
        if ($this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->tokenStorage->getToken()->getUser();
        }
        return null;
    }

    public function getCustomerRank($initial = false){
        $CustomerRank = null;
        $Customer = $this->getLoginCustomer();
        if (!is_null($Customer)) {
            $CustomerRank = $Customer->getCustomerRank();
        }elseif($this->getConfig('login_disp') != CustomerRankConfig::ENABLED && $initial){
            $InitialRank = $this->customerRankRepository->findOneBy(['initial_flg' => true]);
            if($InitialRank){
                $CustomerRank = $InitialRank;
            }
        }

        return $CustomerRank;
    }

    public function getCurrentCondition(\Eccube\Entity\Customer $Customer, $check = false)
    {

        if($check){
            list($start_date,$end_date) = $this->getCheckTerm();
        }else{
            list($start_date,$end_date) = $this->getTerm();
        }
        $includes = $this->getConfig('target_status');
        if(empty($includes))return ['total_amount' => 0, 'total_buytimes' => 0];

        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->select('SUM(o.subtotal) as total_amount,COUNT(o.id) as total_buytimes')
            ->from('Eccube\Entity\Order', 'o')
            ->andWhere('o.OrderStatus IN (:includes)')
            ->andWhere($qb->expr()->eq('o.Customer', ':Customer'))
            ->setParameter(':includes', $includes)
            ->setParameter(':Customer', $Customer)
            ->setMaxResults(1);

        $qb
            ->andWhere('o.create_date >= :start_date')
            ->andWhere('o.create_date <= :end_date')
            ->setParameter(':start_date', $start_date)
            ->setParameter(':end_date',$end_date);

        $result = [];
        try {
            $result = $qb->getQuery()->getResult();
            $ret =  $result[0];
            if(is_null($ret['total_amount']))$ret['total_amount']=0;

            return $ret;
        } catch (NoResultException $e) {
            return [];
        }
    }


    public function checkRank(\Eccube\Entity\Customer $Customer, $force_flg = false){

        //更新期間の設定がない場合はパス
        $term = $this->getConfig('term');
        $term_start = $this->getConfig('term_start');
        if($term == 0){
            return;
        }
        $CheckDate = $Customer->getCheckDate();
        $ToDay = new \DateTime();

        if(!$force_flg && $term != CustomerRankConfig::UPDATE_ALL && $term_start == CustomerRankConfig::DISABLED){
            list($check_date,$check_end_date) = $this->getCheckTerm();

            $checkDate = $Customer->getCheckDate();
            if(strlen($check_date) > 0){
                $targetDate = new \DateTime($check_date);
                if($checkDate >= $targetDate)return;
            }
        }

        $currentCustomerRank = $Customer->getCustomerRank();
        if($currentCustomerRank){
            $customer_rank_id = $currentCustomerRank->getId();
            if($currentCustomerRank->getFixedFlg() == CustomerRankConfig::ENABLED)return;
        }
        if(!isset($customer_rank_id))$customer_rank_id = '';
        $changeCustomerRank = $this->getCheckRank($Customer);

        if(!is_null($changeCustomerRank)){
            if(!isset($currentCustomerRank) || $changeCustomerRank != $currentCustomerRank){
                $Customer->setCustomerRank($changeCustomerRank);
            }
        }else{
            $Customer->setCustomerRank(null);
        }
        $Customer->setCheckDate($ToDay);
        $this->entityManager->persist($Customer);
        $this->entityManager->flush($Customer);
    }

    function getCheckRank(\Eccube\Entity\Customer $Customer, $check = false)
    {
        $rank_down = $this->getConfig('rank_down');

        $currentRank = $Customer->getCustomerRank();

        $CustomerRanks = $this->customerRankRepository->findBy([],['priority' => 'DESC']);

        $currentCondition = $this->getCurrentCondition($Customer, $check);
        
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

        if($rank_down == CustomerRankConfig::ENABLED){
            if(!is_null($checkCustomerRank) && !is_null($currentRank)){
                if($currentRank->getPriority() > $checkCustomerRank->getPriority()){
                    $checkCustomerRank = $currentRank;
                }
            }else{
                if(!is_null($currentRank))$checkCustomerRank = $currentRank;
            }
        }

        return $checkCustomerRank;
    }

    function getNextRank(\Plugin\CustomerRank\Entity\CustomerRank $TargetCustomerRank){

        $term = $this->getConfig('term');
        if($term == CustomerRankConfig::UPDATE_OFF)return [];

        $CustomerRanks = $this->entityManager->createQueryBuilder()
                ->select('cr')
                ->from('Plugin\CustomerRank\Entity\CustomerRank', 'cr')
                ->orderBy('cr.priority','ASC')
                ->getQuery()
                ->getResult();

        for($i = 0;$i < count($CustomerRanks); $i++){
            $CustomerRank = pos($CustomerRanks);
            if($CustomerRank == $TargetCustomerRank){
                return next($CustomerRanks);
            }
            next($CustomerRanks);
        }
        return false;
    }


    public function getCustomerRankRate($rate,$initial = false)
    {
        $CustomerRank = $this->getCustomerRank($initial);
        if($CustomerRank){
            $rank_rate = $CustomerRank->getPointRate();
            if(is_numeric($rank_rate)){
                $rate += $rank_rate;
                if($rate < 0)$rate = 0;
            }
        }
        return $rate;
    }

    function getTerm(){
        $term = $this->getConfig('term');
        $start = $this->getConfig('term_start');
        if($start == CustomerRankConfig::DISABLED){
            if($term == CustomerRankConfig::UPDATE_1MONTH){
                $start_date = date('Y-m-01 00:00:00',strtotime(date('Y-m-01')."-1 month"));
                $end_date = date('Y-m-t 23:59:59',strtotime(date('Y-m-01')."-1 month"));
            }elseif($term == CustomerRankConfig::UPDATE_3MONTH){
                $month = intval(date('m'));
                if($month > 9){
                    $start_date = date('Y-07-01 00:00:00');
                    $end_date = date('Y-09-t 23:59:59',strtotime(date('Y-09-30')));
                }elseif($month > 6){
                    $start_date = date('Y-04-01 00:00:00');
                    $end_date = date('Y-06-t 23:59:59',strtotime(date('Y-06-30')));
                }elseif($month > 3){
                    $start_date = date('Y-01-01 00:00:00');
                    $end_date = date('Y-03-t 23:59:59',strtotime(date('Y-03-31')));
                }else{
                    $start_date = date('Y-10-01 00:00:00',strtotime("-1 year"));
                    $end_date = date('Y-12-t 23:59:59',strtotime(date('Y-12-15')."-1 year"));
                }
            }elseif($term == CustomerRankConfig::UPDATE_6MONTH){
                $month = intval(date('m'));
                if($month > 6){
                    $start_date = date('Y-01-01 00:00:00');
                    $end_date = date('Y-06-t 23:59:59',strtotime(date('Y-06-30')));
                }else{
                    $start_date = date('Y-07-01 00:00:00',strtotime("-1 year"));
                    $end_date = date('Y-12-t 23:59:59',strtotime(date('Y-12-15')."-1 year"));
                }
            }elseif($term == CustomerRankConfig::UPDATE_12MONTH){
                $start_date = date('Y-01-01 00:00:00',strtotime("-1 year"));
                $end_date = date('Y-12-t 23:59:59',strtotime(date('Y-12-15')."-1 year"));
            }elseif($term == CustomerRankConfig::UPDATE_24MONTH){
                $start_date = date('Y-01-01 00:00:00',strtotime("-2 year"));
                $end_date = date('Y-12-t 23:59:59',strtotime(date('Y-12-15')."-1 year"));
            }else{
                $start_date = date('Y-m-01 00:00:00',strtotime("1900-01-01"));
                $end_date = date('Y-m-t 23:59:59');
            }
        }elseif($start == CustomerRankConfig::ENABLED){
            if($term != CustomerRankConfig::UPDATE_ALL){
                $start_date = date('Y-m-d 00:00:00',strtotime("-" . $term ." month"));
                $end_date = date('Y-m-d H:i:s');
            }else{
                $start_date = date('Y-m-01 00:00:00',strtotime("1900-01-01"));
                $end_date = date('Y-m-t 23:59:59');
            }
        }
        return [$start_date,$end_date];
    }

    function getCheckTerm(){
        $term = $this->getConfig('term');
        $start = $this->getConfig('term_start');
        if($start == CustomerRankConfig::DISABLED){
            if ($term == CustomerRankConfig::UPDATE_1MONTH) {
                $start_date = date('Y-m-01 00:00:00');
                $end_date = date('Y-m-t 23:59:59');
            } elseif ($term == CustomerRankConfig::UPDATE_3MONTH) {
                $month = intval(date('m'));
                if ($month > 9) {
                    $start_date = date('Y-10-01 00:00:00');
                    $end_date = date('Y-12-t 23:59:59', strtotime(date('Y-12-31')));
                } elseif ($month > 6) {
                    $start_date = date('Y-07-01 00:00:00');
                    $end_date = date('Y-09-t 23:59:59', strtotime(date('Y-09-30')));
                } elseif ($month > 3) {
                    $start_date = date('Y-04-01 00:00:00');
                    $end_date = date('Y-06-t 23:59:59', strtotime(date('Y-06-30')));
                } else {
                    $start_date = date('Y-01-01 00:00:00');
                    $end_date = date('Y-03-t 23:59:59', strtotime(date('Y-03-31')));
                }
            } elseif ($term == CustomerRankConfig::UPDATE_6MONTH) {
                $month = intval(date('m'));
                if ($month > 6) {
                    $start_date = date('Y-07-01 00:00:00');
                    $end_date = date('Y-12-t 23:59:59', strtotime(date('Y-12-31')));
                } else {
                    $start_date = date('Y-01-01 00:00:00');
                    $end_date = date('Y-06-t 23:59:59', strtotime(date('Y-06-30')));
                }
            } elseif ($term == CustomerRankConfig::UPDATE_12MONTH) {
                $start_date = date('Y-01-01 00:00:00');
                $end_date = date('Y-12-t 23:59:59', strtotime(date('Y-12-31')));
            } elseif ($term == CustomerRankConfig::UPDATE_24MONTH) {
                $start_date = date('Y-01-01 00:00:00', strtotime("-1 year"));
                $end_date = date('Y-12-t 23:59:59', strtotime(date('Y-12-31')));
            } else {
                $start_date = date('Y-m-01 00:00:00', strtotime("1900-01-01"));
                $end_date = date('Y-m-t 23:59:59');
            }
        }elseif($start == CustomerRankConfig::ENABLED){
            if($term != CustomerRankConfig::UPDATE_ALL){
                $start_date = date('Y-m-d 00:00:00',strtotime("-" . $term ." month"));
                $end_date = date('Y-m-d H:i:s');
            }else{
                $start_date = date('Y-m-01 00:00:00',strtotime("1900-01-01"));
                $end_date = date('Y-m-t 23:59:59');
            }
        }
        return [$start_date, $end_date];
    }

    public function getConfig($name){
        if($name == 'target_status'){
            $results = $this->configStatusRepository->findAll();
            $ret= [];
            foreach($results as $result){
                if($result->getOrderStatus())
                $ret[] = $result->getOrderStatus()->getId();
            }
            return $ret;
        }else{
            $ret = $this->configRepository->findOneBy(['name' => $name]);
            if($ret)return $ret->getValue();
        }
        return '';
    }
}
