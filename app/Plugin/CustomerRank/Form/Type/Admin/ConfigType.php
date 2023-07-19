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

namespace Plugin\CustomerRank\Form\Type\Admin;

use Eccube\Form\Type\Master\OrderStatusType;
use Plugin\CustomerRank\Entity\CustomerRankConfig;
use Plugin\CustomerRank\Entity\ConfigStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $arrTerm[CustomerRankConfig::UPDATE_OFF] = trans('customerrank.admin.setting.rank.choice.update.off');
        $arrTerm[CustomerRankConfig::UPDATE_1MONTH] = trans('customerrank.admin.setting.rank.choice.update.1month');
        $arrTerm[CustomerRankConfig::UPDATE_3MONTH] = trans('customerrank.admin.setting.rank.choice.update.3month');
        $arrTerm[CustomerRankConfig::UPDATE_6MONTH] = trans('customerrank.admin.setting.rank.choice.update.6month');
        $arrTerm[CustomerRankConfig::UPDATE_12MONTH] = trans('customerrank.admin.setting.rank.choice.update.12month');
        $arrTerm[CustomerRankConfig::UPDATE_24MONTH] = trans('customerrank.admin.setting.rank.choice.update.24month');
        $arrTerm[CustomerRankConfig::UPDATE_ALL] = trans('customerrank.admin.setting.rank.choice.update.all');

        $arrDisp[CustomerRankConfig::DISABLED] = trans('customerrank.admin.setting.rank.choice.disp.all');
        $arrDisp[CustomerRankConfig::ENABLED] = trans('customerrank.admin.setting.rank.choice.disp.login');

        $arrStart[CustomerRankConfig::DISABLED] = trans('customerrank.admin.setting.rank.choice.start.interval');
        $arrStart[CustomerRankConfig::ENABLED] = trans('customerrank.admin.setting.rank.choice.start.anytime');

        $arrDown[CustomerRankConfig::DISABLED] = trans('customerrank.admin.setting.rank.choice.rank_down.on');
        $arrDown[CustomerRankConfig::ENABLED] = trans('customerrank.admin.setting.rank.choice.rank_down.off');

        $builder
            ->add('login_disp', Type\ChoiceType::class, [
                'label' => trans('customerrank.admin.setting.rank.label.login_disp'),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => array_flip($arrDisp),
            ])
            ->add('term', Type\ChoiceType::class, [
                'label' => trans('customerrank.admin.setting.rank.label.term'),
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
                'choices'  => array_flip($arrTerm),
            ])
            ->add('term_start', Type\ChoiceType::class, [
                'label' => trans('customerrank.admin.setting.rank.label.term_start'),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => array_flip($arrStart),
            ])
            ->add('rank_down', Type\ChoiceType::class, [
                'label' => trans('customerrank.admin.setting.rank.label.rank_down'),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => array_flip($arrDown),
            ])
            ->add('target_status', OrderStatusType::class, [
                'label' => trans('customerrank.admin.setting.rank.label.target_status'),
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
        ;

    }


}
