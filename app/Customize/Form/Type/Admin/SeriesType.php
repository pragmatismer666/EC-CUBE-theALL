<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Customize\Entity\Master\Series;
use Eccube\Common\EccubeConfig;

class SeriesType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(
        EccubeConfig $eccubeConfig
    ) 
    {
        $this->eccubeConfig = $eccubeConfig;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required'  => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                ],
            ])
            ->add('thumbnail_temp', TextType::class, [
                'required' => false,
                'mapped' => false
            ])
            ->add('description', TextareaType::class, [
                'required'  =>  false,
            ])
            ->add('thumbnail_image', FileType::class, [
                'required'  =>  false,
                'mapped'    =>  false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Series::class,
        ]);
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'malldevel_series';
    }
}