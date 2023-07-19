<?php

namespace Customize\Form\Extension\Admin;

use Eccube\Entity\Delivery;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Eccube\Form\Type\Admin\ShippingType;
use Eccube\Repository\DeliveryRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ShippingTypeExtension extends AbstractTypeExtension
{
    protected $deliveryRepository;

    public function __construct(DeliveryRepository $deliveryRepository)
    {
        $this->deliveryRepository = $deliveryRepository;   
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'setDelivery']);
    }

    public function setDelivery(FormEvent $formEvent)
    {
        $shipping = $formEvent->getData();
        if (!$shipping) return;
        $order = $shipping->getOrder();
        if (!$order) return;
        $shop = $order->getShop();
        if (!$shop) return;

        $form = $formEvent->getForm();
        $delivery = $form['Delivery']->getData();

        $form->remove('Delivery');
        $form->add('Delivery', EntityType::class, [
            'required' => false,
            'class' => 'Eccube\Entity\Delivery',
            'choice_label' => function (Delivery $Delivery) {
                return $Delivery->isVisible()
                    ? $Delivery->getServiceName()
                    : $Delivery->getServiceName().trans('admin.common.hidden_label');
            },
            'query_builder' => function ($er) use ($shop) {
                return $er->createQueryBuilder('d')
                    ->where('d.Shop = :Shop')
                    ->setParameter('Shop', $shop)
                    ->orderBy('d.visible', 'DESC') // 非表示は下に配置
                    ->addOrderBy('d.sort_no', 'ASC');
            },
            'placeholder' => false,
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);
        $form['Delivery']->setData($delivery);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ShippingType::class;
    }
}