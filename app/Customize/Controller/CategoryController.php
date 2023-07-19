<?php

namespace Customize\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{

    /** @var  ContainerInterface $container */
    protected $container;

    /** @var CategoryRepository $categoryRepository */
    protected $categoryRepository;

    public function __construct(
        ContainerInterface $container,
        CategoryRepository $categoryRepository
    ) {
        $this->container = $container;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/category", name="malldevel_front_category")
     * @Template("Category/list.twig")
     * @return array
     */
    public function index()
    {
        return [
            'Categories' => $this->categoryRepository->getList()
        ];
    }

    /**
     * @Route("/category/{id}", name="malldevel_front_category_detail")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function detail(Request $request, $id)
    {
        return $this->redirectToRoute("product_list", ['category_id' => $id]);
    }
}
