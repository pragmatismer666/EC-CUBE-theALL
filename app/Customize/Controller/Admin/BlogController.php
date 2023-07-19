<?php

namespace Customize\Controller\Admin;

use Customize\Entity\Blog;
use Customize\Entity\BlogTag;
use Customize\Entity\ContentTag;
use Customize\Entity\Master\BlogType as MasterBlogType;
use Customize\Form\Type\Admin\BlogType;
use Customize\Form\Type\Admin\SearchBlogType;
use Customize\Repository\BlogRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Customize\Repository\ContentTagRepository;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Util\CacheUtil;
use Eccube\Util\FormUtil;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\Paginator;

class BlogController extends AbstractController
{
    /**
     * @var BlogRepository
     */
    protected $blogRepository;

    /**
     * @var BlogTypeRepository
     */
    protected $blogTypeRepository;

    /**
     * @var ContentTagRepository
     */
    protected $tagRepository;

    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    public function __construct(
        BlogRepository $blogRepository,
        ContentTagRepository $tagRepository,
        PageMaxRepository $pageMaxRepository,
        BlogTypeRepository $blogTypeRepository
    )
    {
        $this->blogRepository = $blogRepository;
        $this->tagRepository = $tagRepository;
        $this->pageMaxRepository = $pageMaxRepository;
        $this->blogTypeRepository = $blogTypeRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog-type/notice/blog", name="malldevel_admin_content_blog_notice")
     * @Template("@admin/Content/blog.twig")
     *
     * @param Request $request
     * @param Paginator $paginator
     * @return array
     */
    public function indexNotice(Request $request, Paginator $paginator)
    {
        return $this->index($request, $paginator, MasterBlogType::NOTICE);
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog-type/info-site/blog", name="malldevel_admin_content_blog_info_site")
     * @Template("@admin/Content/blog.twig")
     *
     * @param Request $request
     * @param Paginator $paginator
     * @return array
     */
    public function indexSiteInfo(Request $request, Paginator $paginator)
    {
        return $this->index($request, $paginator, MasterBlogType::INFORMATION_SITE);
    }

    /**
     *
     * @param Request $request
     * @param Paginator $paginator
     * @param int $blogType
     * @return array
     */
    public function index(Request $request, Paginator $paginator, $blogType)
    {
        $builder = $this->formFactory->createBuilder(SearchBlogType::class, null, ['blog_type_id' => $blogType]);

        $ns = $blogType === MasterBlogType::NOTICE ? 'notice' : 'info_site';

        $searchForm = $builder->getForm();

        $page_count = $this->session->get('malldevel.admin.content.blog.search.page_count' . $ns,
            $this->eccubeConfig->get('eccube_default_page_count'));

        $page_no = $request->get('page_no', 1);

        $page_count_param = (int) $request->get('page_count');
        $pageMaxis = $this->pageMaxRepository->findAll();

        if ($page_count_param) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    $this->session->set('malldevel.admin.content.blog.search.page_count' . $ns, $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $page_no = 1;
                $searchData = $searchForm->getData();

                $this->session->set('malldevel.admin.content.blog.search' . $ns, FormUtil::getViewData($searchForm));
                $this->session->set('malldevel.admin.content.blog.search.page_no' . $ns, $page_no);
            } else {
                return [
                    'searchForm' => $searchForm,
                    'pagination' => [],
                    'pageMaxis' => $pageMaxis,
                    'page_no' => $page_no,
                    'page_count' => $page_count,
                    'has_errors' => true
                ];
            }
        } else {
            if (null !== $page_no || $request->get('resume')) {
                if ($page_no) {
                    $this->session->set('malldevel.admin.content.blog.search.page_no' . $ns, $page_no);
                } else {
                    $page_no = $this->session->get('malldevel.admin.content.blog.search.page_no' . $ns, 1);
                }
                $viewData = $this->session->get('malldevel.admin.content.blog.search' . $ns, []);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
            } else {
                $page_no = 1;
                $viewData = FormUtil::getViewData($searchForm);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
                $this->session->set('malldevel.admin.content.blog.search' . $ns, $viewData);
                $this->session->set('malldevel.admin.content.blog.search.page_no' . $ns, $page_no);
            }
        }

        $searchData['blog_type_id'] = $blogType;

        $qb = $this->blogRepository->getQueryBuilderBySearchDataForAdmin($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );

        return [
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'has_errors' => false,
            'blogType' => $blogType,
            'ns' => $ns
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog/new", name="malldevel_admin_content_blog_new")
     * @Route("/%eccube_admin_route%/content/blog-type/notice/blog/new", name="malldevel_admin_content_blog_new_notice")
     * @Route("/%eccube_admin_route%/content/blog-type/info-site/blog/new", name="malldevel_admin_content_blog_new_info_site")
     * @Route("/%eccube_admin_route%/content/blog/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_content_blog_edit")
     *
     * @param Request $request
     * @param int|null $id
     * @param CacheUtil $cacheUtil
     * @Template("@admin/Content/blog_edit.twig")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function edit(Request $request, $id = null, CacheUtil $cacheUtil)
    {
        $route = $request->get('_route');
        $blogType = null;
        if ($route === 'malldevel_admin_content_blog_new_notice') {
            $blogType = MasterBlogType::NOTICE;
        }
        if ($route === 'malldevel_admin_content_blog_new_info_site') {
            $blogType = MasterBlogType::INFORMATION_SITE;
        }
        if (!$id) {

            $Blog = new Blog();
            $Blog->setPublishDate(new \DateTime());
            $BlogType = $this->blogTypeRepository->find($blogType ? $blogType : MasterBlogType::NOTICE);
            $Blog->setBlogType($BlogType);
        } else {
            /** @var Blog $Blog */
            $Blog = $this->blogRepository->find($id);
            if (!$Blog) {
                throw new NotFoundHttpException();
            }
            try {
                $blogType = $Blog->getBlogType()->getId();
            } catch (\Exception $e) {

            }
        }

        $builder = $this->formFactory->createBuilder(BlogType::class, $Blog, ['blog_type_id' => $blogType ? $blogType : MasterBlogType::NOTICE]);
        $form = $builder->getForm();

        $ns = $blogType === MasterBlogType::NOTICE ? 'notice' : 'info_site';

        $BlogTags = $Blog->getBlogTags();
        $tags = [];
        /** @var BlogTag $BlogTag */
        foreach($BlogTags as $BlogTag) {
            $tags[] = $BlogTag->getTag();
        }
        $form['Tag']->setData($tags);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var Blog $Blog */
                $Blog = $form->getData();
                $this->entityManager->persist($Blog);
                $this->entityManager->flush();
                foreach($Blog->getBlogTags() as $BlogTag) {
                    $Blog->removeBlogTag($BlogTag);
                    $this->entityManager->remove($BlogTag);
                }
                $this->entityManager->persist($Blog);
                $this->entityManager->flush();

                $Tags = $form->get('Tag')->getData();
                $tagIdList = [];
                /** @var ContentTag $Tag */
                foreach($Tags as $Tag) {
                    if (!isset($tagIdList[$Tag->getId()])) {
                        $BlogTag = new BlogTag();
                        $BlogTag->setBlog($Blog);
                        $BlogTag->setBlogId($Blog->getId());
                        $BlogTag->setTag($Tag);
                        $BlogTag->setTagId($Tag->getId());

                        $Blog->addBlogTag($BlogTag);
                        $this->entityManager->persist($BlogTag);
                        $tagIdList[$Tag->getId()] = true;
                    }
                }
                $Blog->setUpdateDate(new \DateTime());
                $this->entityManager->persist($Blog);
                $this->entityManager->flush();

                $this->addSuccess('admin.common.save_complete', 'admin');
                $cacheUtil->clearDoctrineCache();

                return $this->redirectToRoute('malldevel_admin_content_blog_edit', ['id' => $Blog->getId()]);
            }
        }

        return [
            'Blog' => $Blog,
            'Tags' => $tags,
            'TagList' => $this->tagRepository->getList($blogType),
            'form' => $form->createView(),
            'blogType' => $blogType,
            'ns' => $ns
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog/{id}/delete", requirements={"id" = "\d+"}, name="malldevel_admin_content_blog_delete")
     *
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, $id)
    {
        $this->isTokenValid();

        log_info('ブログ削除', [$id]);



        $Blog = $this->blogRepository->find($id);

        $blogType = MasterBlogType::NOTICE;

        try {
            $blogType = $Blog->getBlogType()->getId();
        } catch (\Exception $e) {

        }

        $ns = $blogType === MasterBlogType::NOTICE ? 'notice' : 'info_site';

        $page_no = $this->session->get('malldevel.admin.content.blog.search.page_no' . $ns, 1);

        if(!$Blog) {
            $this->deleteMessage();

            return $this->redirect($this->generateUrl('malldevel_admin_content_blog_' . $ns, ['page_no' => $page_no]) . '&resume=1');
        }

        try {
            $this->entityManager->remove($Blog);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.delete_complete', 'admin');
        } catch (\Exception $e) {
            log_error('ブログ削除失敗', [$id, $e]);

            $message = trans('admin.common.delete_error');
            $this->addError($message, 'admin');
        }

        log_info('ブログ削除完了', [$id]);

        return $this->redirect($this->generateUrl('malldevel_admin_content_blog_' . $ns, ['page_no' => $page_no]) . '&resume=1');
    }

}
