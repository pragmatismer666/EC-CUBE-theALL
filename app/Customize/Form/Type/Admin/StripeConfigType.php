<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Eccube\Form\Type\ToggleSwitchType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Customize\Entity\StripeConfig;

class StripeConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publishable_key', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(array(
                            'message' => trans('malldevel.admin.stripe.config.publishable_key.error.blank')
                        )
                    ),
                    new Assert\Regex(array(
                            'pattern' => '/^\w+$/',
                            'match' => true,
                            'message' => trans('malldevel.admin.stripe.config.publishable_key.error.regex')
                        )
                    )
                ],
            ])
            ->add('secret_key', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(array(
                        'message' => trans('malldevel.admin.stripe.config.secret_key.error.blank')
                    )),
                    new Assert\Regex(array(
                            'pattern' => '/^\w+$/',
                            'match' => true,
                            'message' => trans('malldevel.admin.stripe.config.secret_key.error.regex')
                        )
                    )
                ],
            ])
            ->add('signature', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Regex(array(
                            'pattern' => '/^\w+$/',
                            'match' => true,
                            'message' => trans('malldevel.admin.stripe.config.signature.error.regex')
                        )
                    )
                ],
            ])
            ->add('is_capture_on', ChoiceType::class,[
                'choices' => [
                    'malldevel.admin.stripe.config.authorize' => false, 
                    'malldevel.admin.stripe.config.authorize_and_capture' => true
                ]
            ])
            ->add('stripe_fees_percent', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Regex(array(
                            'pattern' => '/^100$|^\d{0,2}(\.\d{1,2})? *%?$/',
                            'match' => true,
                            'message' => trans('malldevel.admin.stripe.config.stripe_fees_percent.error.regex')
                        )
                    )
                ],
            ])
            ->add('application_fees_percent', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Regex(array(
                            'pattern' => '/^100$|^\d{0,2}(\.\d{1,2})? *%?$/',
                            'match' => true,
                            'message' => trans('malldevel.admin.stripe.config.application_fees_percent.error.regex')
                        )
                    )
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StripeConfig::class,
        ]);
    }
}