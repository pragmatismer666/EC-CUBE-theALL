<?php

namespace Customize\Form\Type\Admin;

use Customize\Form\Type\Master\ShopStatusType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Customize\Repository\ShopRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Eccube\Form\Type\PostalType;
use Eccube\Form\Type\AddressType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Eccube\Form\Validator\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Eccube\Repository\MemberRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Entity\Master\Authority;
use Eccube\Form\Type\ToggleSwitchType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Customize\Entity\EAuthority;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Entity\Category;
use Customize\Form\Type\Admin\TokushoType;
use Customize\Repository\Master\SeriesRepository;
use Customize\Entity\Master\Series;
use Customize\Entity\Master\ShopStatus;
use Eccube\Form\Type\KanaType;
use Eccube\Form\Type\PriceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ShopType extends AbstractType {

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
     * @var MemberRepository
     */
    protected $memberRepository;
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var SeriesRepository
     */
    protected $seriesRepository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    
    protected $entityManager;

    protected $error;

    public function __construct(
        ContainerInterface $container,
        ShopRepository $shopRepository,
        EccubeConfig $eccubeConfig,
        MemberRepository $memberRepository,
        CategoryRepository $categoryRepository,
        SeriesRepository $seriesRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->container = $container;
        $this->shopRepository = $shopRepository;
        $this->eccubeConfig = $eccubeConfig;
        $this->memberRepository = $memberRepository;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->categoryRepository = $categoryRepository;
        $this->seriesRepository = $seriesRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $auth_repo = $this->entityManager->getRepository(Authority::class);
        $shop_owner_role = $auth_repo->findOneBy(['id' => EAuthority::SHOP_OWNER]);
        $builder
            ->add("name", TextType::class, [
                'required'  =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                ],
            ])
            ->add("kana", TextType::class, [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[ァ-ヶｦ-ﾟー]+$/u',
                        'message' => 'form_error.kana_only',
                    ])
                ]
            ])
            ->add("company_name", TextType::class, [])
            ->add('logo_img', FileType::class, [
                'required'  =>  false,
                'mapped'    =>  false,
            ])
            ->add('founded_at', TextType::class, [
                'required'  =>  false,
                'attr'  =>  [
                    'placeholder'   =>  '１９９６年１０月'
                ]
            ])
            ->add('postal_code', PostalType::class, [
                'required'  =>  false,
            ])
            ->add('address', AddressType::class, [
                'required'  =>  false,
            ])
            ->add('add_images', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('gmap_enabled', ToggleSwitchType::class)
            ->add('order_mail', EmailType::class, [
                'required'  =>  true,
                'constraints'    =>  [
                    new Assert\NotBlank(),
                    new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
                ],
                'attr' => [
                    'placeholder' => 'common.mail_address_sample',
                ],
            ])
            ->add('Serieses', ChoiceType::class, [
                'choice_label'  =>  'name',
                'required'  =>  false,
                'mapped'    =>  false,
                'multiple'  =>  true,
                'expanded'  =>  true,
                'choices'   =>  $this->seriesRepository->getList(),
                'choice_value'  =>  function(Series $Series = null) {
                    return $Series ? $Series->getId() : null;
                }
            ])
            ->add('Category', ChoiceType::class, [
                'choice_label'  =>  'name',
                'multiple'      =>  true,
                'mapped'        =>  false,
                'expanded'      =>  true,
                'choices'       =>  $this->categoryRepository->getList(null, true),
                'choice_value'  =>  function( Category $Category ) {
                    return $Category ? $Category->getId() : null;
                }
            ])
            ->add('hp', TextareaType::class, [
                'required'      =>  false,
            ])
            ->add('Tokusho', TokushoType::class, [
                'required'      =>  false,
            ])
            ->add('identity_docs', FileType::class, [
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ])
            ->add('shop_photos', FileType::class, [
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ])
            ->add('delete_images', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('add_iddocs', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            
            ->add('add_shop_photos', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            // 送料設定
            ->add('delivery_free_amount', PriceType::class, [
                'required' => false,
            ])
            ->add('phone_number', TextType::class, [
                'required'  =>  false,
            ])
            ->add('intro', TextareaType::class, [
                'required'  =>  false,
            ])
            ->add('delivery_free_quantity', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => "/^\d+$/u",
                        'message' => 'form_error.numeric_only',
                    ]),
                ],
            ])
            ->add('Status', ShopStatusType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);

            $currentMember = $this->tokenStorage->getToken()->getUser();

            if ($currentMember->getRole() === "ROLE_ADMIN") {
                $builder->add("Members", ChoiceType::class, [
                    'choice_label'  =>  'username',
                    'required'      =>  true,
                    'multiple'      =>  true,
                    'expanded'      =>  false,
                    // 'mapped'        =>  false,
                    'choices'   =>  $this->memberRepository->findBy(['Authority' => $shop_owner_role]),
                    'choice_value'=> function($Member) {
                        return $Member ? $Member->getId() : null;
                    },
                    'constraints'   =>  [
                        new Assert\NotBlank(),
                    ],
                    'attr'          =>  [
                        'class'     =>  'selectpicker',
                        'data-live-search'  =>  'true',
                        'multiple'  =>  true
                    ]
                ]);
                $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event ) {
                    $form = $event->getForm();
                    $Shop = $form->getData();
                    $Members = $form->get('Members')->getData();
                    if (count($Members)) {
                        foreach($Members as $Member) {
                            if(!$this->validateMember($Member, $Shop)) {
                                $form['Members']->addError( new FormError($this->getError()));
                            }
                        }
                    } else {
                        $form["Members"]->addError(new FormError(trans('malldevel.admin.shop.error.owner_required')));
                    }
                });
            }
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event ) {
                $form = $event->getForm();
                $Shop = $form->getData();
                if (empty($Shop->getCapital())) {
                    $status = $Shop->getStatus();
                    if ($status && $status ->getId() === ShopStatus::DISPLAY_SHOW) 
                    {
                        $form['capital']->addError(new FormError('資本金がないため公開にできません'));
                    }
                }

            });

    }
    private function validateMember($Member, $Shop) {
        if( !$Member ) {
            $this->error = trans("malldevel.admin.member.not_exist");
            return false;
        };
        if ($Member->getRole() != 'ROLE_SHOP_OWNER') {
            $this->error = trans('malldevel.admin.shop.error.not_shop_owner_role', [
                '%owner%'   =>  $Member->getName()
            ]);
            return false;
        }
        if ($Member->hasShop() && $Member->getShop()->getId() != $Shop->getId()) {
            $this->error = trans('malldevel.admin.shop.member.already_has_shop', [
                '%owner%'   =>  $Member->getName()
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class'    =>  'Customize\Entity\Shop',
            'allow_extra_fields' => true
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'malldevel_shop';
    }

    public function getError() {
        return $this->error;
    }
}