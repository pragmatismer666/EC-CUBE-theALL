<?php

namespace Customize\Controller;

use Customize\Repository\ShopBlogRepository;
use Eccube\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopBlogController extends AbstractController
{
    /** @var  ContainerInterface $container */
    protected $container;

    /** @var  ShopBlogRepository $shopBlogRepository */
    protected $shopBlogRepository;

    public function __construct(
        ContainerInterface $container,
        ShopBlogRepository $shopBlogRepository
    )
    {
        $this->container = $container;
        $this->shopBlogRepository = $shopBlogRepository;
    }

    /**
     * @Route("/shop-blog/{id}", requirements={"id" = "\d+"}, name="malldevel_front_shop_blog")
     * @Template("ShopBlog/view.twig")
     * @param Request $request
     * @param int $id
     * @return array
     */
    public function view(Request $request, $id)
    {
        /** @var \Customize\Entity\ShopBlog $ShopBlog */
        $ShopBlog = $this->shopBlogRepository->find($id);
        if (!$ShopBlog || !$ShopBlog->isVisible()) {
            throw new NotFoundHttpException();
        }
        if (!$ShopBlog->getShop()) {
            throw new NotFoundHttpException();
        }
        $nextPrevIds = $this->shopBlogRepository->getNextPrevId($ShopBlog);
        return [
            'ShopBlog' => $ShopBlog,
            'prev_id' => $nextPrevIds['prev_id'],
            'next_id' => $nextPrevIds['next_id']
        ];
    }
}
