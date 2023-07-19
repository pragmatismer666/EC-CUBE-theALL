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

namespace Plugin\CustomerRank\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Eccube\Entity\ProductClass;
use Plugin\CustomerRank\Entity\CustomerRank;
use Plugin\CustomerRank\Entity\CustomerPrice;

class CustomerPriceEventSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        if ($entity instanceof ProductClass) {
            if(!$entity->isVisible())return;
            $customerRankRepository = $entityManager->getRepository(CustomerRank::class);
            $customerPriceRepository = $entityManager->getRepository(CustomerPrice::class);
            $CustomerRanks = $customerRankRepository->findAll();
            foreach($CustomerRanks as $CustomerRank){
                $entity->setCustomerRankPrices($customerPriceRepository->getCustomerPriceByProductClass($CustomerRank, $entity), $CustomerRank->getId());
                $entity->setCustomerRankPriceIncTaxes($customerPriceRepository->getCustomerPriceIncTaxByProductClass($CustomerRank, $entity), $CustomerRank->getId());
            }
        }
    }
}