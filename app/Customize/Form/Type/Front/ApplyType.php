<?php


namespace Customize\Form\Type\Front;

use Symfony\Component\Form\AbstractType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Eccube\Form\Type\PostalType;
use Eccube\Form\Type\AddressType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Customize\Repository\ApplyRepository;
use Customize\Repository\ShopRepository;
use Customize\Entity\Apply;
use Eccube\Form\Type\KanaType;
use Symfony\Component\Form\FormError;


class ApplyType extends AbstractType
{
    
    protected $eccubeConfig;
    protected $shopRepository;
    protected $applyRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        ApplyRepository $applyRepository,
        ShopRepository $shopRepository
    ) {
        $this->eccubeConfig = $eccubeConfig;
        $this->shopRepository = $shopRepository;
        $this->applyRepository = $applyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class, [
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('shop_name', TextType::class, [
                'required'      =>  true,
                'constraints'   =>  [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('shop_name_kana', TextType::class, [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[ァ-ヶｦ-ﾟー]+$/u',
                        'message' => 'form_error.kana_only',
                    ])
                ]
            ])
            ->add('login_id', TextType::class, [
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('order_mail', EmailType::class, [
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('company_name', TextType::class, [
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('postal_code', PostalType::class,[
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('address', AddressType::class, [
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('representative', TextType::class,[
                'required'      =>  true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('founded_at', TextType::class, [
                'required'  =>  false,
                'attr'  =>  [
                    'placeholder'   =>  '１９９６年１０月'
                ]
            ])
            ->add('capital', TextType::class, [])
            ->add('contact', TextType::class, [])
            ->add('exp_online_shop', ChoiceType::class, [
                'required'  =>  false,
                'multiple'  =>  false,
                'expanded'  =>  false,
                'placeholder'   =>  false,
                'choices'   =>  [
                    '無' => 0,
                    '有' => 1,
                ]
            ])
            ->add('open_schedule', DateType::class, [
                'required'  =>  false,
                'years'     =>  \range(2021, 2025)
            ])
            ->add('inquiry_content', TextareaType::class, [
                'required'  =>  false
            ])
            ->add('phone_number', TextType::class, [
                'required'  =>  false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $Apply = $event->getData();
            if (!$Apply->getId()) {
                $form = $event->getForm();

                $form->add('user_policy_check', CheckboxType::class, [
                    'required' => true,
                    'label' => null,
                    'mapped' => false,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]);
            }
        });

        $shopRepository = $this->shopRepository;
        $applyRepository = $this->applyRepository;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($shopRepository, $applyRepository ) {
            $form = $event->getForm();
            $Apply = $form->getData();

            $order_mail = $Apply->getOrderMail();
            $existing_apply = $applyRepository->findOneBy(['order_mail' => $order_mail, 'status' => Apply::STATUS_ALLOWED]);
            
            if ($existing_apply) {
                $form['order_mail']->addError(new FormError(trans('malldevel.front.shop_register.form_error.duplicate_email')));
                return;
            }
            $existing_shop = $shopRepository->findOneBy(["order_mail" => $order_mail]);
            if ($existing_shop) {
                $form['order_mail']->addError(new FormError(trans('malldevel.front.shop_register.form_error.duplicate_email')));
                return;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class'    =>  'Customize\Entity\Apply',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'apply';
    }
}