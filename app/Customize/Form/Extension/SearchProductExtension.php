<?php

namespace Customize\Form\Extension;

use Eccube\Form\Type\Admin\CategoryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Eccube\Form\Type\SearchProductType;
use Customize\Repository\Master\SeriesRepository;

class SearchProductExtension extends AbstractTypeExtension
{
    protected $seriesRepository;

    public function __construct(SeriesRepository $seriesRepository) {
        $this->seriesRepository = $seriesRepository;
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $SeriesList = $this->seriesRepository->findAll();

        $builder->add('series_id', EntityType::class, [
            'class' => 'Customize\Entity\Master\Series',
            'choice_label' => 'name',
            'choices' => $SeriesList,
            'placeholder' => 'common.select__all_products',
            'required' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return SearchProductType::class;
    }
}
