<?php

namespace Customize\Controller\Admin;

use Customize\Entity\ShopBlog;
use Customize\Form\Type\Admin\ShopBlogType;
use Customize\Repository\ShopBlogRepository;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eccube\Util\CacheUtil;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Eccube\Common\Constant;

class ShopBlogController extends AbstractController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var PageMaxRepository
     */
    protected $pageMaxRepository;

    /**
     * @var ShopBlogRepository
     */
    protected $shopBlogRepository;

    public function __construct(
        ContainerInterface $container,
        PageMaxRepository $pageMaxRepository,
        ShopBlogRepository $shopBlogRepository
    )
    {
        $this->container = $container;
        $this->pageMaxRepository = $pageMaxRepository;
        $this->shopBlogRepository = $shopBlogRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/content/shop_blog", name="malldevel_admin_shop_blog")
     * @Template("@admin/Content/shop_blog.twig")
     * @param Request $request
     * @param Paginator $paginator
     *
     * @return array
     */
    public function index(Request $request, Paginator $paginator)
    {
        $member = $this->getUser();
        $shop_id = null;
        $pageMaxis = $this->pageMaxRepository->findAll();
        if ($member->getRole() !== "ROLE_ADMIN") {
            $shop_id = $member->getShop()->getId() ?? -1;
        }
        $qb = $this->shopBlogRepository->getQueryBuilderAll($shop_id);
        $page_no = (int)$request->get('page_no');
        $pageCount = $this->session->get(
            'malldevel.admin.content.shop_blog.search.page_count',
            $this->eccubeConfig->get('eccube_default_page_count')
        );
        $pageCountParam = $request->get('page_count');
        if ($pageCountParam && is_numeric($pageCountParam)) {
            foreach ($pageMaxis as $pageMax) {
                if ($pageCountParam == $pageMax->getName()) {
                    $pageCount = $pageMax->getName();
                    $this->session->set('eccube.admin.customer.search.page_count', $pageCount);
                    break;
                }
            }
        }
        if ($page_no !== null || $request->get('resume')) {
            if ($page_no) {
                $this->session->set('malldevel.admin.content.shop_blog.search.page_no', (int)$page_no);
            } else {
                $page_no = $this->session->get('malldevel.admin.content.shop_blog.search.page_no', 1);
            }
        } else {
            $page_no = 1;
            $this->session->set('malldevel.admin.content.shop_blog.search.page_no', (int)$page_no);
        }

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $pageCount
        );

        return [
            'pagination' => $pagination,
            'page_count' => $pageCount,
            'pageMaxis' => $pageMaxis,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/shop_blog/new", name="malldevel_admin_shop_blog_new")
     * @Route("/%eccube_admin_route%/content/shop_blog/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_shop_blog_edit")
     * @Template("@admin/Content/shop_blog_edit.twig")
     *
     * @param Request $request
     * @param int|null $id
     * @param CacheUtil $cacheUtil
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function edit(Request $request, $id = null, CacheUtil $cacheUtil)
    {
        /** @var \Eccube\Entity\Member $Member */
        $Member = $this->getUser();
        if ($id) {
            $ShopBlog = $this->shopBlogRepository->find($id);
            if (!$ShopBlog) {
                throw new NotFoundHttpException();
            }
            /** @var \Eccube\Entity\Member $Member */
            $Member = $this->getUser();
            if ($Member->getRole() !== 'ROLE_ADMIN') {
                if (($Member->getShop()->getId() ?? null) !== ($ShopBlog->getShop()->getId() ?? null)) {
                    throw new NotFoundHttpException();
                }
            }
        } else {
            $ShopBlog = new ShopBlog();
            $ShopBlog->setPublishDate(new \DateTime());
        }

        $builder = $this->formFactory->createBuilder(ShopBlogType::class, $ShopBlog);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($ShopBlog);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            $cacheUtil->clearDoctrineCache();

            return $this->redirectToRoute('malldevel_admin_shop_blog_edit', ['id' => $ShopBlog->getId()]);
        }

        return [
            'form' => $form->createView(),
            'ShopBlog' => $ShopBlog,
            'Shop' => $Member->getShop(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/shop_blog/{id}/delete", requirements={"id" = "\d+"}, name="malldevel_admin_shop_blog_delete")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param int|null $id
     */
    public function delete(Request $request, $id, TranslatorInterface $translator)
    {
        $this->isTokenValid();

        log_info('ショップブログ削除', [$id]);

        $page_no = intval($this->session->get('malldevel.admin.content.shop_blog.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        $ShopBlog = $this->shopBlogRepository->find($id);

        if (!$ShopBlog) {
            $this->deleteMessage();

            return $this->redirect($this->generateUrl('malldevel_admin_shop_blog', ['page_no' => $page_no]) . '?resume='
                . Constant::ENABLED);
        }

        $Member = $this->getUser();
        if ($Member->getRole() !== 'ROLE_ADMIN') {
            if (($Member->getShop()->getId() ?? null) !== ($ShopBlog->getShop()->getId() ?? null)) {
                throw new NotFoundHttpException();
            }
        }

        try {
            $this->entityManager->remove($ShopBlog);
            $this->entityManager->flush();
            $this->addSuccess('admin.common.delete_complete', 'admin');
        } catch (\Exception $e) {
            log_error('ショップブログ削除失敗', [$e], 'admin');

            $message = trans('admin.common.delete_error');
            $this->addError($message, 'admin');
        }

        log_info('ショップブログ削除完了', [$id]);

        return $this->redirect($this->generateUrl('malldevel_admin_shop_blog', ['page_no' => $page_no]) . '?resume='
            . Constant::ENABLED);
    }

}
