<?php

namespace Customize\Form\Extension\Admin;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Entity\Order;
use Eccube\Form\Type\Admin\OrderType;
use Customize\Repository\ShopRepository;
use Customize\Entity\Shop;

class OrderExtension extends AbstractTypeExtension
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var ShopRepository
     */
    protected $shopRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ShopRepository $shopRepository
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->shopRepository = $shopRepository;
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Member = $this->tokenStorage->getToken()->getUser();

        if ($Member->getRole() === "ROLE_ADMIN") {
            $builder->add('Shop', ChoiceType::class, [
                'choice_label'  =>  'name',
                'required'      =>  true,
                'multiple'      =>  false,
                'expanded'      =>  false,
                'choices'       =>  $this->shopRepository->getShopsQueryBuilder()->getQuery()->getResult(),
                'choice_value'  =>  function($Shop ) {
                    return $Shop ? $Shop->getId() : null;
                },
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr'      =>  [
                    'class'     =>  'selectpicker',
                    'data-live-search' => 'true'
                ]
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return OrderType::class;
    }
}
