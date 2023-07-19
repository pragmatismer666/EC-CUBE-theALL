<?php

namespace Customize\Controller\Admin;

use Customize\Entity\ContentTag;
use Customize\Entity\Master\BlogType;
use Customize\Form\Type\Admin\ContentTagType;
use Customize\Repository\ContentTagRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentTagController extends AbstractController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ContentTagRepository
     */
    protected $tagRepository;

    /**
     * @var BlogTypeRepository
     */
    protected $blogTypeRepository;

    public function __construct(
        ContainerInterface $container,
        ContentTagRepository $tagRepository,
        BlogTypeRepository $blogTypeRepository
    )
    {
        $this->container = $container;
        $this->tagRepository = $tagRepository;
        $this->blogTypeRepository = $blogTypeRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/content/blog-type/notice/tag", name="malldevel_admin_content_tag_notice")
     * @Route("/%eccube_admin_route%/content/blog-type/notice/tag/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_content_tag_edit_notice")
     * @Template("@admin/Content/tag.twig")
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
     * @Route("/%eccube_admin_route%/content/blog-type/info-site/tag", name="malldevel_admin_content_tag_info_site")
     * @Route("/%eccube_admin_route%/content/blog-type/info-site/tag/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_content_tag_edit_info_site")
     * @Template("@admin/Content/tag.twig")
     *
     * @param Request $request
     * @param int|null $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexSiteInfo(Request $request, $id = null)
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
        $Tags = $this->tagRepository->getList($blogType);
        $ns = $blogType === BlogType::NOTICE ? 'notice' : 'info_site';

        if ($id) {
            $TargetTag = $this->tagRepository->find($id);
            if (!$TargetTag) {
                throw new NotFoundHttpException();
            }
        } else {
            $TargetTag = new ContentTag();
        }

        $builder = $this->formFactory->createBuilder(ContentTagType::class, $TargetTag);

        $form = $builder->getForm();
        $forms = [];

        foreach($Tags as $EditTag) {
            $id = $EditTag->getId();
            $forms[$id] = $this->formFactory->createNamed('tag_'.$id, ContentTagType::class, $EditTag);
        }

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var ContentTag $FormData */
                $FormData = $form->getData();
                $FormData->setBlogType($this->blogTypeRepository->find($blogType));
                if (empty($FormData->getSortNo())) {
                    $FormData->setSortNo($this->tagRepository->getNewSortNo());
                }
                $this->tagRepository->save($FormData);
                $this->entityManager->flush();$this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('malldevel_admin_content_tag_' . $ns);
            }
        }

        foreach($forms as $editForm) {
            $editForm->handleRequest($request);
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $this->tagRepository->save($editForm->getData());
                $this->entityManager->flush();
                $this->addSuccess('admin.common.save_complete', 'admin');

                return $this->redirectToRoute('malldevel_admin_content_tag_' . $ns);
            }
        }

        $formViews = [];
        foreach($forms as $key => $value) {
            $formViews[$key] = $value->createView();
        }

        return [
            'form' => $form->createView(),
            'TargetTag' => $TargetTag,
            'forms' => $formViews,
            'Tags' => $Tags,'blogType' => $blogType,
            'ns' => $blogType === BlogType::NOTICE ? 'notice' : 'info_site',
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/tag/sort_no/move", name="malldevel_admin_content_tag_move_sort_no", methods={"POST"})
     * @param Request $request
     */
    public function moveSortNo(Request $request)
    {
        if ($request->isXmlHttpRequest() && $this->isTokenValid()) {
            $sortNos = $request->request->all();
            foreach($sortNos as $tagId => $sortNo) {
                /** @var \Customize\Entity\ContentTag $Tag */
                $Tag = $this->tagRepository->find($tagId);
                if ($Tag) {
                    $Tag->setSortNo($sortNo);
                    $this->entityManager->persist($Tag);
                }
            }
            $this->entityManager->flush();
        }
        return new Response();
    }

    /**
     * @Route("/%eccube_admin_route%/content/tag/{id}/delete", requirements={"id" = "\d+"}, name="malldevel_admin_content_tag_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param int $id
     * @param CacheUtil $cacheUtil
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, $id, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();

        $TargetTag = $this->tagRepository->find($id);
        if (!$TargetTag) {
            $this->deleteMessage();

            return $this->redirectToRoute('malldevel_admin_content_tag_notice');
        }

        log_info('ブログタグ削除開始', [$id]);

        try {
            $blogType = $TargetTag->getBlogType()->getId();
        } catch (\Exception $e) {
            $blogType = BlogType::NOTICE;
        }

        $ns = $blogType === BlogType::NOTICE ? 'notice' : 'info_site';

        try {
            $this->tagRepository->delete($TargetTag);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.delete_complete', 'admin');

            log_info('ブログタグ削除完了', [$id]);
        } catch (\Exception $e) {
            log_info('ブログタグ削除エラー', [$id, $e]);

            $message = trans('admin.common.delete_error.foreign_key', ['%name%' => $TargetTag->getName()]);

            $this->addError($message, 'admin');
        }

        return $this->redirectToRoute('malldevel_admin_content_tag_' . $ns);
    }
}
