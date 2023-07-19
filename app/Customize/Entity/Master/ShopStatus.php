<?php

namespace Customize\Entity\Master;

use Eccube\Entity\Master\AbstractMasterEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ShopStatus
 *
 * @ORM\Table("cme_shop_status")
 * @ORM\Entity(repositoryClass="Customize\Repository\Master\ShopStatusRepository")
 */
class ShopStatus extends AbstractMasterEntity
{
    /**
     * 公開
     *
     * @var integer
     */
    const DISPLAY_SHOW = 1;

    /**
     * 非公開
     *
     * @var integer
     */
    const DISPLAY_HIDE = 2;

    /**
     * 廃止
     *
     * @var integer
     */
    const DISPLAY_ABOLISHED = 3;
}
