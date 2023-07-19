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

use Plugin\MailMagazine4\Form\Type\MailMagazineType;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class MailMagazineExtension extends AbstractTypeExtension
{
    /**
     * @var CustomerRankRepository
     */
    private $customerRankRepository;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(CustomerRankRepository $customerRankRepository)
    {
        $this->customerRankRepository = $customerRankRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $CustomerRanks = $this->customerRankRepository->getList();
        if(is_array($CustomerRanks)){
            $choices = [];
            foreach($CustomerRanks as $CustomerRank){
                $choices[$CustomerRank->getId()] = $CustomerRank->getName();
            }
            $builder
                ->add(
                    'customer_rank',
                    Type\ChoiceType::class,
                    [
                        'label' => trans('customerrank.common.rank'),
                        'required' => false,
                        'expanded' => true,
                        'multiple' => true,
                        'mapped' => true,
                        'choices' => array_flip($choices),
                    ]
                );
        }
    }

    public function getExtendedType()
    {
        return MailMagazineType::class;
    }

    public function getExtendedTypes(): iterable
    {
        return [MailMagazineType::class];
    }

}
