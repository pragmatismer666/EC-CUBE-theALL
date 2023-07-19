<?php


namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Eccube\Form\Type\PostalType;
use Eccube\Form\Type\AddressType;
use Eccube\Repository\MemberRepository;
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
use Customize\Services\ShopService;
use Symfony\Component\Form\FormError;

class ApplyType extends AbstractType
{
    
    protected $eccubeConfig;
    protected $shopRepository;
    protected $applyRepository;
    protected $memberRepository;
    protected $shopService;

    public function __construct(
        EccubeConfig $eccubeConfig,
        ApplyRepository $applyRepository,
        ShopRepository $shopRepository,
        MemberRepository $memberRepository,
        ShopService $shopService
    ) {
        $this->eccubeConfig = $eccubeConfig;
        $this->shopRepository = $shopRepository;
        $this->applyRepository = $applyRepository;
        $this->memberRepository = $memberRepository;
        $this->shopService = $shopService;
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
            ->add("shop_name_kana", TextType::class, [
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
            ->add('status', ChoiceType::class, [
                'expanded'  =>  false,
                'choices'   =>  ['malldevel.admin.apply.status.processig' => Apply::STATUS_PROCESSING, 'malldevel.admin.apply.status.allowed' => Apply::STATUS_ALLOWED, 'malldevel.admin.apply.status.on_hold' => Apply::STATUS_HOLD]
            ])
            ->add('exp_online_shop', ChoiceType::class, [
                'required'  =>  false,
                'multiple'  =>  false,
                'expanded'  =>  true,
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

        $shopRepository = $this->shopRepository;
        $applyRepository = $this->applyRepository;
        $memberRepository = $this->memberRepository;

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($shopRepository, $applyRepository, $memberRepository) {
            $form = $event->getForm();
            $Apply = $form->getData();

            if ($Apply->getStatus() == Apply::STATUS_HOLD) return;
            // prevent order mail from overlap
            $order_mail = $Apply->getOrderMail();
            $existing_apply = $applyRepository->findOneBy(['order_mail' => $order_mail, 'status' => Apply::STATUS_ALLOWED]);
            
            if ($existing_apply && $Apply->getId() != $existing_apply->getId()) {
                $form['order_mail']->addError(new FormError(trans('malldevel.front.shop_register.form_error.duplicate_email')));
                return;
            }
            $existing_shop = $shopRepository->findOneBy(["order_mail" => $order_mail]);
            if ($existing_shop && $existing_shop->getApplyId() !== $Apply->getId()) {
                $form['order_mail']->addError(new FormError(trans('malldevel.front.shop_register.form_error.duplicate_email')));
                return;
            }

            // prevent login id from overlap
            $login_id = $Apply->getLoginId();

            $existing_apply = $applyRepository->findOneBy(['login_id' => $login_id, 'status' => Apply::STATUS_ALLOWED]);
            
            if ($existing_apply && $Apply->getId() != $existing_apply->getId()) {
                $newLoginId = $this->shopService->getUniqLoginId($login_id);
                $form['login_id']->addError(new FormError(trans('malldevel.front.shop_register.form_error.duplicate_login', ['%suggestion%' => $newLoginId])));
                return;
            }
            
            $existing_member = $memberRepository->findOneBy(['login_id' => $login_id]);
            if ($existing_member && $existing_member->getApplyId() !== $Apply->getId()) {
                $newLoginId = $this->shopService->getUniqLoginId($login_id);
                $form['login_id']->addError(new FormError(trans("malldevel.front.shop_register.form_error.duplicate_login", ['%suggestion%' => $newLoginId])));
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