<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\Shop;
use Customize\Entity\ShopBlog;
use Customize\Repository\ShopRepository;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ShopBlogType extends AbstractType
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(
        ContainerInterface $container,
        ShopRepository $shopRepository,
        TokenStorageInterface $tokenStorage,
        EccubeConfig $eccubeConfig
    )
    {
        $this->container = $container;
        $this->shopRepository = $shopRepository;
        $this->tokenStorage = $tokenStorage;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_sltext_len']])
                ]
            ])
            ->add("content", TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_lltext_len']])
                ]
            ])
            ->add('publish_date', DateTimeType::class, [
                'date_widget' => 'choice',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd hh:mm',
                'years' => range($this->eccubeConfig['eccube_news_start_year'], date('Y') + 3),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => ['admin.content.news.display_status__show' => true, 'admin.content.news.display_status__hide' => false],
                'required' => true,
                'expanded' => false,
            ])
            ->add('Shop', ChoiceType::class, [
                'choice_label' => 'Name',
                'placeholder' => trans('malldevel.admin.content.shop_blog.shop_placeholder'),
                'multiple' => false,
                'expanded' => false,
                'mapped' => true,
                'required' => true,
                'choices' => $this->shopRepository->getAllShopsQueryBuilder()->getQuery()->getResult(),
                'choice_value' => function (Shop $Shop = null) {
                    return $Shop ? $Shop->getId() : null;
                },
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                ]
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'setShop'])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function setShop(FormEvent $event)
    {
        /** @var array $ShopBlog */
        $ShopBlog = $event->getData();
        /** @var \Eccube\Entity\Member $Member */
        $Member = $this->tokenStorage->getToken()->getUser();
        if ($Member->getRole() === 'ROLE_SHOP_OWNER') {
            $ShopBlog->setShop($Member->getShop());
        }
        $event->setData($ShopBlog);
    }

    public function onPreSubmit(FormEvent $event)
    {
        /** @var array $ShopBlog */
        $ShopBlog = $event->getData();
        /** @var \Eccube\Entity\Member $Member */
        $Member = $this->tokenStorage->getToken()->getUser();
        if ($Member->getRole() === 'ROLE_SHOP_OWNER') {
            $ShopBlog['Shop'] = $Member->getShop()->getId() ?? null;
        }
        $event->setData($ShopBlog);
    }

}
