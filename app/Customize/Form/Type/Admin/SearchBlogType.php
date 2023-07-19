<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\ContentCategory;
use Customize\Entity\Master\BlogType as MasterBlogType;
use Customize\Repository\ContentCategoryRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Customize\Form\Type\Admin\ContentCategoryType;
use Customize\Form\Type\Master\BlogType as MasterBlogFormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchBlogType extends AbstractType
{
    /**
     * @var ContentCategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var BlogTypeRepository
     */
    protected $blogTypeRepository;

    public function __construct(
        ContentCategoryRepository $categoryRepository,
        BlogTypeRepository $blogTypeRepository
    )
    {
        $this->categoryRepository = $categoryRepository;
        $this->blogTypeRepository = $blogTypeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'malldevel.admin.content.blog.multi_search_label',
                'required' => false,
            ])->add('category_id', ChoiceType::class, [
                'placeholder' => 'malldevel.admin.content.blog.filter.all',
                'choice_label' => 'Name',
                'label' => 'malldevel.admin.content.blog.category',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices' => $this->categoryRepository->getList($options['blog_type_id'] ?? null),
                'choice_value' => function (ContentCategory $Category = null) {
                    return $Category ? $Category->getId() : null;
                }
            ])->add('blog_type_id', ChoiceType::class, [
                'placeholder' => 'malldevel.admin.content.blog.filter.all',
                'choice_label' => 'Name',
                'label' => 'malldevel.admin.content.blog.type',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $this->blogTypeRepository->getList(),
                'choice_value' => function (MasterBlogType $BlogType = null) {
                    return $BlogType ? $BlogType->getId() : null;
                }
            ])->add('visible', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'malldevel.admin.content.shop_blog.display_status__show' => true,
                    'malldevel.admin.content.shop_blog.display_status__hide' => false
                ],
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'blog_type_id' => null,
        ]);
    }
}
