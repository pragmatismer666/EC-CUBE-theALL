<?php

namespace Customize\Controller\Admin;

use Customize\Entity\ContentCategory;
use Customize\Entity\Master\BlogType;
use Customize\Form\Type\Admin\ContentCategoryType;
use Customize\Repository\ContentCategoryRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContentCategoryController extends AbstractController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ContentCategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var BlogTypeRepository
     */
    protected $blogTypeRepository;

    public function __construct(
        ContainerInterface $container,
        ContentCategoryRepository $categoryRepository,
        BlogTypeRepository $blogTypeRepository
    )
    {
        $this->container = $container;
        $this->categoryRepository = $categoryRepository;
        $this->blogTypeRepository = $blogTypeRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog-type/notice/category", name="malldevel_admin_content_category_notice")
     * @Route("/%eccube_admin_route%/content/blog-type/notice/category/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_content_category_edit_notice")
     * @Template("@admin/Content/category.twig")
     *
     * @param Request $request
     * @param int|null $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexNotice(Request $request, $id = null)
    {
        return $this->index($request, $id, BlogType::NOTICE);
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog-type/info-site/category", name="malldevel_admin_content_category_info_site")
     * @Route("/%eccube_admin_route%/content/blog-type/info-site/category/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_content_category_edit_info_site")
     * @Template("@admin/Content/category.twig")
     *
     * @param Request $request
     * @param int|null $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexInfoSite(Request $request, $id = null)
    {
        return $this->index($request, $id, BlogType::INFORMATION_SITE);
    }

    /**
     * @param Request $request
     * @param int|null $id
     * @param int $blogType
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index(Request $request, $id, $blogType)
    {
        $Categories = $this->categoryRepository->getList($blogType);

        if ($id) {
            $Category = $this->categoryRepository->find($id);
            if (!$Category) {
                throw new NotFoundHttpException();
            }
        } else {
            $Category = new ContentCategory();
        }

        $builder = $this->formFactory->createBuilder(ContentCategoryType::class, $Category);

        $form = $builder->getForm();
        $forms = [];

        foreach($Categories as $EditCategory) {
            $id = $EditCategory->getId();
            $forms[$id] = $this->formFactory->createNamed('category_'.$id, ContentCategoryType::class, $EditCategory);
        }

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var ContentCategory $Model */
                $Model = $form->getData();
                $Model->setBlogType($this->blogTypeRepository->find($blogType));
                if (empty($Model->getSortNo())) {
                    $Model->setSortNo($this->categoryRepository->getNewSortNo());
                }
                $this->categoryRepository->save($Model);
                $this->entityManager->flush();
                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute(
                    'malldevel_admin_content_category'
                    . ($blogType === BlogType::NOTICE ? '_notice' : '_info_site')
                );
            }
        }

        foreach($forms as $editForm) {
            $editForm->handleRequest($request);
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $this->categoryRepository->save($editForm->getData());
                $this->entityManager->flush();
                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute(
                    'malldevel_admin_content_category'
                    . ($blogType === BlogType::NOTICE ? '_notice' : '_info_site')
                );
            }
        }

        $formViews = [];

        foreach($forms as $key => $value) {
            $formViews[$key] = $value->createView();
        }

        return [
            'form' => $form->createView(),
            'TargetCategory' => $Category,
            'forms' => $formViews,
            'Categories' => $Categories,
            'blogType' => $blogType,
            'ns' => $blogType === BlogType::NOTICE ? 'notice' : 'info_site',
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/category/sort_no/move", name="malldevel_admin_content_category_move_sort_no", methods={"POST"})
     * @param Request $request
     */
    public function moveSortNo(Request $request)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            $sortNos = $request->request->all();
            foreach($sortNos as $categoryId => $sortNo) {
                /** @var ContentCategory $Category */
                $Category = $this->categoryRepository->find($categoryId);
                if ($Category) {
                    $Category->setSortNo($sortNo);
                    $this->entityManager->persist($Category);
                }
            }
            $this->entityManager->flush();
        }

        return new Response();
    }

    /**
     * @Route("/%eccube_admin_route%/content/category/{id}/delete", requirements={"id" = "\d+"}, name="malldevel_admin_content_category_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param int $id
     * @param CacheUtil $cacheUtil
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, $id, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();
        /** @var ContentCategory $TargetCategory */
        $TargetCategory = $this->categoryRepository->find($id);
        if (!$TargetCategory) {
            $this->deleteMessage();

            return $this->redirectToRoute("malldevel_admin_content_category_notice");
        }
        log_info('ブログカテゴリ削除開始', [$id]);
        try {
            $blogType = $TargetCategory->getBlogType()->getId();
        } catch (\Exception $e) {
            $blogType = BlogType::NOTICE;
        }
        try {
            $this->categoryRepository->delete($TargetCategory);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.delete_complete', 'admin');

            log_info('ブログカテゴリ削除完了', [$id]);

            $cacheUtil->clearDoctrineCache();

        } catch (\Exception $e) {
            log_info('ブログカテゴリ削除エラー', [$id, $e]);

            $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $TargetCategory->getName()]);
            $this->addError($message, 'admin');
        }
        return $this->redirectToRoute(
            'malldevel_admin_content_category'
            . ($blogType === BlogType::NOTICE ? '_notice' : '_info_site')
        );
    }
}
