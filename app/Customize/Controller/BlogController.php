<?php

namespace Customize\Controller;

use Customize\Entity\Blog;
use Customize\Entity\Master\BlogType;
use Customize\Repository\BlogRepository;
use Customize\Repository\ContentCategoryRepository;
use Customize\Repository\ContentTagRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends AbstractController
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var  BlogRepository $blogRepository */
    protected $blogRepository;

    /** @var  BlogTypeRepository $blogTypeRepository */
    protected $blogTypeRepository;

    /** @var  ContentTagRepository $tagRepository */
    protected $tagRepository;

    /** @var  ContentCategoryRepository */
    protected $categoryRepository;

    public function __construct(
        ContainerInterface $container,
        BlogRepository $blogRepository,
        BlogTypeRepository $blogTypeRepository,
        ContentTagRepository $tagRepository,
        ContentCategoryRepository $categoryRepository
    )
    {
        $this->container = $container;
        $this->blogRepository = $blogRepository;
        $this->blogTypeRepository = $blogTypeRepository;
        $this->tagRepository = $tagRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/blog/blog-type/notice", name="malldevel_front_blog_notice")
     * @Route("/blog/blog-type/info-site", name="malldevel_front_blog_info_site")
     * @Route("/blog/blog-type/notice/category/{category_id}", requirements={"category_id" = "\d+"}, name="malldevel_front_blog_category_notice")
     * @Route("/blog/blog-type/info-site/category/{category_id}", requirements={"category_id" = "\d+"}, name="malldevel_front_blog_category_info_site")
     * @Route("/blog/blog-type/notice/tag/{tag_id}", requirements={"tag_id" = "\d+"}, name="malldevel_front_blog_tag_notice")
     * @Route("/blog/blog-type/info-site/tag/{tag_id}", requirements={"tag_id" = "\d+"}, name="malldevel_front_blog_tag_info_site")
     * @Template("Blog/list.twig")
     *
     * @param Request $request
     * @param int|null $category_id
     * @param int|null $tag_id
     * @return array
     */
    public function index(Request $request, $category_id = null, $tag_id = null)
    {
        $route = $request->get('_route');
        $blogType = $this->getBlogTypeFromRouteName($route);
        $BlogType = $this->blogTypeRepository->find($blogType);
        $Blogs = $this->blogRepository->getList([
            'blog_type_id' => $blogType,
            'tag_id' => $tag_id,
            'category_id' => $category_id
        ]);
        $Tag = null;
        $Category = null;
        if ($tag_id) {
            $Tag = $this->tagRepository->find($tag_id);
            if (!$Tag) {
                throw new NotFoundHttpException();
            }
        }
        if ($category_id) {
            $Category = $this->categoryRepository->find($category_id);
            if (!$Category) {
                throw new NotFoundHttpException();
            }
        }
        $TagList = $this->tagRepository->getList($blogType);
        $CategoryList = $this->categoryRepository->getList($blogType);
        return [
            'BlogType' => $BlogType,
            'Category' => $Category,
            'Tag' => $Tag,
            'TagList' => $TagList,
            'CategoryList' => $CategoryList,
            'Blogs' => $Blogs,
            'ns' => $blogType === BlogType::NOTICE ? 'notice' : 'info_site'
        ];
    }

    /**
     * @Route("/blog/{id}", requirements={"id" = "\d+"}, name="malldevel_front_blog_view")
     * @Template("Blog/view.twig")
     *
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function view(Request $request, $id)
    {
        /** @var Blog $Blog */
        $Blog = $this->blogRepository->find($id);
        if (!$Blog || !$Blog->isVisible()) {
            throw new NotFoundHttpException();
        }
        $nextPrevIds = $this->blogRepository->getNextPrevId($Blog);
        $BlogType = $Blog->getBlogType();
        if (!$BlogType) {
            $blogType = BlogType::NOTICE;
        } else {
            $blogType = $BlogType->getId();
        }
        $ns = $blogType === BlogType::NOTICE ? 'notice' : 'info_site';
        $parent_path = 'malldevel_front_blog_' . $ns;
        return [
            'Blog' => $Blog,
            'prev_id' => $nextPrevIds['prev_id'],
            'next_id' => $nextPrevIds['next_id'],
            'parent_path' => $parent_path
        ];
    }

    /**
     * @param string $route
     * @return int
     */
    public function getBlogTypeFromRouteName($route)
    {
        if (strcasecmp(substr($route, strlen($route) - strlen('notice')), 'notice') === 0) {
            return BlogType::NOTICE;
        }
        return BlogType::INFORMATION_SITE;
    }
}
