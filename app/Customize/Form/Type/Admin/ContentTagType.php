<?php

namespace Customize\Form\Type\Admin;

use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class ContentTagType extends AbstractType
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(
        ContainerInterface $container,
        EccubeConfig $eccubeConfig
    )
    {
        $this->container = $container;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("name", TextType::class, [
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']])
            ]
        ]);
    }
}
