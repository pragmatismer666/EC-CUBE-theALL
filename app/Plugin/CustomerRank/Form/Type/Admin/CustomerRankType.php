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

use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerRankType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.name'),
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('discount_rate', Type\IntegerType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.discount_rate'),
                'required' => false,
                'constraints' => [
                    new Assert\Range(
                        [
                            'max' => 100,
                        ]
                    ),
                ],
            ])
            ->add('discount_value', PriceType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.discount_value'),
                'required' => false,
            ])
            ->add('point_rate', Type\IntegerType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.point_rate'),
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => "/^\-?\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    ]),
                ],
            ])
            ->add('delivery_free_condition', PriceType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.delivery_free_condition'),
                'required' => false,
            ])
            ->add('cond_amount', PriceType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.cond_amount'),
                'required' => false,
            ])
            ->add('cond_buytimes', Type\IntegerType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.cond_buytimes'),
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid'
                    ]),
                ],
            ])
            ->add('initial_flg', Type\CheckboxType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.initial_flg'),
                'required' => false,
            ])
            ->add('fixed_flg', Type\CheckboxType::class, [
                'label' => trans('customerrank.admin.rank.edit.label.fixed_flg'),
                'required' => false,
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Plugin\CustomerRank\Entity\CustomerRank',
        ]);
    }
}
