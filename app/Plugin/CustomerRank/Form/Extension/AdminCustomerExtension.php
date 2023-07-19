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

namespace Plugin\CustomerRank\Form\Extension;

use Eccube\Form\Type\Admin\CustomerType;
use Plugin\CustomerRank\Form\Type\Admin\CustomerRankMasterType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AdminCustomerExtension extends AbstractTypeExtension
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'CustomerRank',
                EntityType::class,
                [
                    'label' => trans('customerrank.common.rank'),
                    'class' => 'Plugin\CustomerRank\Entity\CustomerRank',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'placeholder' => trans('customerrank.admin.common.nothing'),
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('cr')
                            ->orderBy('cr.priority', 'DESC');
                    },
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CustomerType::class;
    }

    public function getExtendedTypes(): iterable
    {
        return [CustomerType::class];
    }
}
