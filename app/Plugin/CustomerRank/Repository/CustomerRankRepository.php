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

namespace Plugin\CustomerRank\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\CustomerRank\Entity\CustomerRank;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CustomerRankRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry, string $entityClass = CustomerRank::class)
    {
        parent::__construct($registry, $entityClass);
    }

    public function getList()
    {
        $qb = $this->createQueryBuilder('cr')
            ->orderBy('cr.priority', 'DESC');
        $CustomerRanks = $qb->getQuery()
            ->getResult();

        return $CustomerRanks;
    }

    public function save($TargetCustomerRank)
    {
        $em = $this->getEntityManager();
        try {
            if (!$TargetCustomerRank->getId()) {
                $priority = $this->createQueryBuilder('cr')
                    ->select('MAX(cr.priority)')
                    ->getQuery()
                    ->getSingleScalarResult();
                if (!$priority) {
                    $priority = 0;
                }
                $TargetCustomerRank->setPriority($priority + 1);
            }

            $em->persist($TargetCustomerRank);

            if($TargetCustomerRank->getInitialFlg() == CustomerRank::ENABLED){
                $CustomerRanks = $this->createQueryBuilder('cr')
                    ->select('cr')
                    ->getQuery()
                    ->getResult();
                foreach($CustomerRanks as $CustomerRank){
                    if($TargetCustomerRank->getId() != $CustomerRank->getId()){
                        $CustomerRank->setInitialFlg(CustomerRank::DISABLED);
                        $em->persist($CustomerRank);
                    }
                }
            }

            $em->flush();

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function delete($CustomerRank)
    {
        $em = $this->getEntityManager();
        try {
            $CustomerPrices = $em->createQueryBuilder()
                                 ->from('Plugin\CustomerRank\Entity\CustomerPrice','cp')
                                 ->select('cp')
                                 ->where('cp.CustomerRank = :CustomerRank')
                                 ->setParameter('CustomerRank',$CustomerRank)
                                 ->getQuery()
                                 ->getResult();
            if(count($CustomerPrices) > 0){
                foreach($CustomerPrices as $CustomerPrice){
                    $ProductClass = $CustomerPrice->getProductClass();
                    $ProductClass->removeCustomerPrice($CustomerPrice);
                    $em->remove($CustomerPrice);
                    $em->persist($ProductClass);
                }
            }

            $priority = $CustomerRank->getPriority();
            $em->createQueryBuilder()
                ->update('Plugin\CustomerRank\Entity\CustomerRank', 'cr')
                ->set('cr.priority', 'cr.priority - 1')
                ->where('cr.priority > :priority')->setParameter('priority', $priority)
                ->getQuery()
                ->execute();
            $em->remove($CustomerRank);
            $em->flush();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}