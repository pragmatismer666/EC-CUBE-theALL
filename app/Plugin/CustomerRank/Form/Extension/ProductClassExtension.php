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

use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Plugin\CustomerRank\Repository\CustomerPriceRepository;
use Eccube\Form\Type\PriceType;
use Eccube\Form\Type\Admin\ProductClassType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductClassExtension extends AbstractTypeExtension
{

    /**
     * @var CustomerRankRepository
     */
    private $customerRankRepository;

    /**
     * @var CustomerPriceRepository
     */
    private $customerPriceRepository;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(CustomerRankRepository $customerRankRepository, CustomerPriceRepository $customerPriceRepository)
    {
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

    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductClassType::class;
    }

    public function getExtendedTypes(): iterable
    {
        return [ProductClassType::class];
    }
}
