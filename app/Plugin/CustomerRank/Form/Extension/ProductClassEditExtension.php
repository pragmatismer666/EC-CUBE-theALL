<?php
/*
* Plugin Name : CustomerRank
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\CustomerRank\Form\Extension;

use Plugin\CustomerRank\Entity\CustomerPrice;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Plugin\CustomerRank\Repository\CustomerPriceRepository;
use Eccube\Form\Type\PriceType;
use Eccube\Form\Type\Admin\ProductClassEditType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;

class ProductClassEditExtension extends AbstractTypeExtension
{

    private $entityManager;

    private $customerRankRepository;

    private $customerPriceRepository;

    public function __construct(
            EntityManagerInterface $entityManager,
            CustomerRankRepository $customerRankRepository,
            CustomerPriceRepository $customerPriceRepository
            )
    {
        $this->entityManager = $entityManager;
        $this->customerRankRepository = $customerRankRepository;
        $this->customerPriceRepository = $customerPriceRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $CustomerRanks = $this->customerRankRepository->getList();
        if(is_array($CustomerRanks)){
            foreach($CustomerRanks as $CustomerRank){
                $tag_id = 'customer_price' . '_' . $CustomerRank->getId();
                $builder
                    ->add(
                        $tag_id,
                        PriceType::class,
                        [
                            'label' => $CustomerRank->getName() . trans('customerrank.common.customer_price'),
                            'required' => false,
                            'mapped' => false,
                        ]
                    );
            }
        }

        $rankRepo = $this->customerRankRepository;
        $priceRepo = $this->customerPriceRepository;
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use($rankRepo, $priceRepo) {
                /** @var \Eccube\Entity\ProductClass $data */
                $ProductClass = $event->getData();
                /** @var \Symfony\Component\Form\Form $form */
                $form = $event->getForm();
                if (is_null($ProductClass)) {
                    return;
                }

                $CustomerRanks = $rankRepo->getList();
                foreach($CustomerRanks as $CustomerRank){
                    $data = $priceRepo->findOneBy(['ProductClass' => $ProductClass, 'CustomerRank' => $CustomerRank]);
                    if($data){
                        $form['customer_price_'.$CustomerRank->getId()]->setData($data->getPrice());
                    }
                }
            });

        $builder
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $CustomerRanks = $this->customerRankRepository->getList();
                $ProductClass = $event->getData();
                $formData = $event->getForm();
                if ($formData->isValid()) {
                    if ($ProductClass->isVisible()) {
                        foreach($CustomerRanks as $CustomerRank){
                            if($formData->has('customer_price_'.$CustomerRank->getId())){
                                $CustomerPrice = $this->customerPriceRepository->findOneBy(['ProductClass' => $ProductClass, 'CustomerRank' => $CustomerRank]);
                                if(!$CustomerPrice){
                                    $CustomerPrice =  new CustomerPrice();
                                    $CustomerPrice->setProductClass($ProductClass);
                                    $CustomerPrice->setCustomerRank($CustomerRank);
                                }

                                $CustomerPrice->setPrice($formData->get('customer_price_'. $CustomerRank->getId())->getData());
                                $ProductClass->addCustomerPrice($CustomerPrice);
                                $this->entityManager->persist($CustomerPrice);
                                $this->entityManager->flush($CustomerPrice);
                            }
                        }
                    }
                }
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductClassEditType::class;
    }

    public function getExtendedTypes(): iterable
    {
        return [ProductClassEditType::class];
    }
}
