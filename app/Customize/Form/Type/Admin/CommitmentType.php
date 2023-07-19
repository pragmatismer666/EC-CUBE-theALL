<?php


namespace Customize\Form\Type\Admin;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Eccube\Common\EccubeConfig;

class CommitmentType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextareaType::class, [
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
                    new Assert\Length(['max'    =>  $this->eccubeConfig['malldevel_buyer_commit_text_limit']])
                ]
            ])
            ->add("image", HiddenType::class, [
                'required'  =>  false,
            ])
            ->add('delete_images', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'required'  =>  false,
            ]);

    }
    
}