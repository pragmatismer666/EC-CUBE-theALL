<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\ContentCategory;
use Customize\Repository\ContentCategoryRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Customize\Form\Type\Master\BlogType as MasterBlogFormType;
use Customize\Entity\Master\BlogType as MasterBlogType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    /**
     * @var BlogTypeRepository
     */
    protected $blogTypeRepository;

    /**
     * @var ContentCategoryRepository
     */
    protected $contentCategoryRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(
        BlogTypeRepository $blogTypeRepository,
        ContentCategoryRepository $contentCategoryRepository,
        EccubeConfig $eccubeConfig
    )
    {
        $this->blogTypeRepository = $blogTypeRepository;
        $this->contentCategoryRepository = $contentCategoryRepository;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('BlogType', MasterBlogFormType::class, [
                'choice_label' => 'Name',
                'required' => false,
                'multiple' => false,
                'mapped' => false,
                'expanded' => false,
                'choices' => $this->blogTypeRepository->getList(),
                'choice_value' => function (MasterBlogType $MasterBlogType = null) {
                    return $MasterBlogType ? $MasterBlogType->getId() : null;
                }
            ])
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_sltext_len']])
                ]
            ])
            ->add('upload', FileType::class, [
                'required' => false,
                'mapped' => false,
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => $this->eccubeConfig['eccube_lltext_len']])
                ]
            ])
            ->add('publish_date', DateTimeType::class, [
                'required' => true,
                'date_widget' => 'choice',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd hh:mm',
                'years' => range($this->eccubeConfig['eccube_news_start_year'], date('Y') + 3),
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'malldevel.admin.content.shop_blog.display_status__show' => true,
                    'malldevel.admin.content.shop_blog.display_status__hide' => false
                ],
                'required' => true,
                'expanded' => false,
            ])
            ->add('Category', ChoiceType::class, [
                'choice_label' => 'Name',
                'multiple' => false,
                'mapped' => true,
                'required' => true,
                'expanded' => true,
                'choices' => $this->contentCategoryRepository->getList($options['blog_type_id'] ?? null),
                'choice_value' => function(ContentCategory $ContentCategory = null) {
                    return $ContentCategory ? $ContentCategory->getId() : null;
                },
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('Tag', EntityType::class, [
                'class' => 'Customize\Entity\ContentTag',
                'query_builder' => function ($er) use ($options) {
                    $qb = $er->createQueryBuilder('t');
                    $qb->addSelect('bt')
                        ->innerJoin('t.BlogType', 'bt');
                    if (!empty($options['blog_type_id'])) {
                        $qb->andWhere('bt.id = :blog_type_id')
                            ->setParameter('blog_type_id', $options['blog_type_id']);
                    }
                    $qb->orderBy('t.sort_no', 'DESC')
                        ->addOrderBy('t.id', 'DESC');
                    return $qb;
                },
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'mapped' => false
            ])
            ->add('tags', CollectionType::class, [
                'entry_type' => HiddenType::class,
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'blog_type_id' => null,
        ]);
    }
}
