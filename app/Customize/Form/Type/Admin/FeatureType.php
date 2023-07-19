<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\Feature;
use Customize\Repository\ShopRepository;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeatureType extends AbstractType
{
    protected $eccubeConfig;
    protected $shopRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        ShopRepository $shopRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->shopRepository = $shopRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class, [
                'required'  =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                ],
            ])
            ->add("content", TextareaType::class, [
                'required'  =>  true,
                'constraints'   =>  [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'malldevel.admin.content.feature.display_status__show' => true,
                    'malldevel.admin.content.feature.display_status__hide' => false
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('Shops', ChoiceType::class, [
                'choice_label'  =>  'name',
                'required'      =>  true,
                'multiple'      =>  true,
                'mapped'        =>  false,
                'expanded'      =>  false,
                'placeholder'   =>  "malldevel.admin.shop.select.empty",
                'choices'       =>  $this->shopRepository->getShopsQueryBuilder()->getQuery()->getResult(),
                'choice_value'  =>  function($Shop) {
                    return $Shop ? $Shop->getId() : null;
                },
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr'      =>  [
                    'class'     =>  'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('thumbnail', HiddenType::class, [
                'required'  =>  true,
                'mapped'    =>  true
            ])
            ->add('delete_images', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('products', CollectionType::class, [
                'entry_type'    =>  HiddenType::class,
                'allow_add'     =>  true,
                'allow_delete'  =>  true,
                'mapped'        =>  false
            ]);

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Feature::class,
        ]);
    }
}
