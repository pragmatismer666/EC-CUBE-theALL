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
use Plugin\CustomerRank\Entity\ConfigStatus;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ConfigStatusRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry, string $entityClass = ConfigStatus::class)
    {
        parent::__construct($registry, $entityClass);
    }
}