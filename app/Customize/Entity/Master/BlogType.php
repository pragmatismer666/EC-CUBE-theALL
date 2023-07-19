<?php

namespace Customize\Entity\Master;

use Eccube\Entity\Master\AbstractMasterEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BlogType
 *
 * @ORM\Table("cme_blog_type")
 * @ORM\Entity(repositoryClass="Customize\Repository\Master\BlogTypeRepository")
 */
class BlogType extends AbstractMasterEntity
{
    /**
     * @var integer
     */
    const NOTICE = 1;
    /**
     * @var integer
     */
    const INFORMATION_SITE = 2;
}
