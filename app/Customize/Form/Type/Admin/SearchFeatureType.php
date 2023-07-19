<?php

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Common\EccubeConfig;
use Customize\Repository\ShopRepository;

class SearchFeatureType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        ShopRepository $shopRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->shopRepository = $shopRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('multi', TextType::class, [
                'label' => 'admin.order.multi_search_label',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                ],
            ])
            ->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'malldevel.admin.content.feature.display_status__show' => true,
                    'malldevel.admin.content.feature.display_status__hide' => false
                ],
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('Shop', ChoiceType::class, [
                'choice_label'  =>  'name',
                'required'      =>  false,
                'multiple'      =>  false,
                'expanded'      =>  false,
                'placeholder'   =>  "malldevel.admin.shop.select.empty",
                'choices'       =>  $this->shopRepository->getShopsQueryBuilder()->getQuery()->getResult(),
                'choice_value'  =>  function($Shop ) {
                    return $Shop ? $Shop->getId() : null;
                },
                'attr'      =>  [
                    'class'     =>  'selectpicker',
                    'data-live-search' => 'true'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'blog_type_id' => null,
        ]);
    }
}
