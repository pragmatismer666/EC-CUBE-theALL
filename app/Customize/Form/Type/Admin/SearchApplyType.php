<?php
namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Eccube\Form\Type\Master\PrefType;
use Customize\Entity\Apply;

class SearchApplyType extends AbstractType
{
    public function __construct(
        EccubeConfig $eccubeConfig
    ) {
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class, [
                'label' =>  'お名前',
                'required'  =>  false,
            ])
            ->add('shop_name', TextType::class, [
                'label' =>  'malldevel.admin.shop.name',
                'required'  =>  false,
            ])
            ->add('order_mail', TextType::class, [
                'label' =>  '連絡帳メールアドレス',
                'required'  =>  false,
            ])
            ->add('login_id', TextType::class, [
                'label' =>  'ログインID',
                'required'=>    false,
            ])
            ->add('company_name', TextType::class, [
                'label' =>  '会社名',
                'required'=>    false,
            ])
            ->add('representative', TextType::class, [
                'label' =>  '代表者',
                'required'=>    false,
            ])
            ->add('Pref', PrefType::class, [
                'label' =>  '	住所',
                'required'=>    false,
            ])
            ->add('status', ChoiceType::class, [
                'expanded'  =>  true,
                'multiple'  =>  true,
                'choices'   =>  [
                    'malldevel.admin.apply.status.processig' => Apply::STATUS_PROCESSING, 
                    'malldevel.admin.apply.status.allowed' => Apply::STATUS_ALLOWED, 
                    'malldevel.admin.apply.status.on_hold' => Apply::STATUS_HOLD,
                    'malldevel.admin.apply.status.canceled' => Apply::STATUS_CANCELED
                ],
                'data'      =>  [Apply::STATUS_PROCESSING , Apply::STATUS_ALLOWED , Apply::STATUS_HOLD],
            ]);
    }
    
}