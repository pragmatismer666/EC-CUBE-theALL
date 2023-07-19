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

namespace Plugin\CustomerRank\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerRankConfig
 *
 * @ORM\Table(name="plg_customerrank_dtb_config")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Plugin\CustomerRank\Repository\ConfigRepository")
 */
class CustomerRankConfig extends \Eccube\Entity\AbstractEntity
{
    const ENABLED = 1;
    const DISABLED = 0;

    const UPDATE_OFF = 0;
    const UPDATE_1MONTH = 1;
    const UPDATE_3MONTH = 3;
    const UPDATE_6MONTH = 6;
    const UPDATE_12MONTH = 12;
    const UPDATE_24MONTH = 24;
    const UPDATE_ALL = 99;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false, length=255)
     * @ORM\Id
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", nullable=true, length=255)
     */
    private $value;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }
}
