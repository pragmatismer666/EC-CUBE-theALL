<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductReview4\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Master\AbstractMasterEntity;

/**
 * Class ProductPurchasedStatus
 *
 * @ORM\Table(name="plg_product_review_purchased_status")
 * @ORM\Entity(repositoryClass="Plugin\ProductReview4\Repository\ProductPurchasedStatusRepository")
 */
class ProductPurchasedStatus extends AbstractMasterEntity
{
    const NOT_PURCHASED = 1;

    const PURCHASED = 2;
}
