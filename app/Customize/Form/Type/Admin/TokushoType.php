<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Customize\Entity\Tokusho;

class TokushoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delivery', TextareaType::class, [
                'required'  => false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.delivery.placeholder')
                ]
            ])
            ->add('delivery_time', TextareaType::class, [
                'required' => false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.delivery_time.placeholder')
                ]
            ])
            ->add('receipt', TextareaType::class, [
                'required'  =>  false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.receipt.placeholder')
                ]
            ])
            ->add('rcx_overview', TextareaType::class, [
                'required'  =>  false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.rcx_overview.placeholder')
                ]
            ])
            ->add('cancel', TextareaType::class, [
                'required'  =>  false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.cancel.placeholder')
                ]
            ])
            ->add('refund', TextareaType::class, [
                'required'  =>  false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.refund.placeholder')
                ]
            ])
            ->add('exchange', TextareaType::class, [
                'required'  =>  false,
                'attr'      =>  [
                    'placeholder'   =>  trans('malldevel.admin.shop.tokusho.exchange.placeholder')
                ]
            ])
            ->add('payment_method', TextareaType::class, [
                'required'  =>  false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tokusho::class,
        ]);
    }
}