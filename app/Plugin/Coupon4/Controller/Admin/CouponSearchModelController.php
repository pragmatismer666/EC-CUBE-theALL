<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\Coupon4\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Category;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Customize\Repository\ShopRepository;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CouponSearchModelController.
 */
class CouponSearchModelController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var \Customize\Repository\ShopRepository
     */
    private $shopRepository;

    /**
     * CouponSearchModelController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param ShopRepository $shopRepository
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ShopRepository $shopRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->shopRepository = $shopRepository;
    }

    /**
     * search product modal.
     *
     * @param Request $request
     * @param int $page_no
     * @param Paginator $paginator
     *
     * @return array
     * @Route("/%eccube_admin_route%/coupon/search/product", name="plugin_coupon_search_product")
     * @Route("/%eccube_admin_route%/coupon/search/product/page/{page_no}", requirements={"page_no" = "\d+"}, name="plugin_coupon_search_product_page")
     * @Template("@Coupon4/admin/search_product.twig")
     */
    public function searchProduct(Request $request, $page_no = null, Paginator $paginator)
    {
        if (! $request->isXmlHttpRequest()) {
            return null;
        }

        $pageCount = $this->eccubeConfig['eccube_default_page_count'];
        $session = $this->session;
        if ('POST' === $request->getMethod()) {
            log_info(
                'get search data with parameters ',
                ['id' => $request->get('id'), 'category_id' => $request->get('category_id')]
            );
            $page_no = 1;
            $searchData = [
                'id' => $request->get('id'),
            ];
            if ($categoryId = $request->get('category_id')) {
                $searchData['category_id'] = $categoryId;
            }
            /** @var \Eccube\Entity\Member $Member */
            $Member = $this->getUser();
            if ($Member->getRole() === 'ROLE_ADMIN') {
                if ($shopId = $request->get('shop_id')) {
                    $searchData['shop_id'] = $shopId;
                }
            }
            if ($Member->getRole() === 'ROLE_SHOP_OWNER') {
                $searchData['shop_id'] = $Member->getShop()->getId();
            }
            $session->set('eccube.plugin.coupon.product.search', $searchData);
            $session->set('eccube.plugin.coupon.product.search.page_no', $page_no);
        } else {
            $searchData = (array) $session->get('eccube.plugin.coupon.product.search');
            if (is_null($page_no)) {
                $page_no = intval($session->get('eccube.plugin.coupon.product.search.page_no'));
            } else {
                $session->set('eccube.plugin.coupon.product.search.page_no', $page_no);
            }
        }

        if (! empty($searchData['category_id'])) {
            $searchData['category_id'] = $this->categoryRepository->find($searchData['category_id']);
        }

        $qb = $this->productRepository->getQueryBuilderBySearchDataForAdmin($searchData);
        // 除外するproduct_idを設定する
        $existProductId = $request->get('exist_product_id');
        if (strlen($existProductId > 0)) {
            $qb->andWhere($qb->expr()->notin('p.id', ':existProductId'))->setParameter(
                'existProductId',
                explode(',', $existProductId)
            );
        }
        if ($searchData['shop_id']) {
            $qb->andWhere('p.Shop = :Shop')->setParameter(
                'Shop',
                $this->shopRepository->find($searchData['shop_id'])
            );
        }

        /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $pageCount,
            ['wrap-queries' => true]
        );

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * カテゴリ検索画面を表示する.
     *
     * @param Request $request
     *
     * @return array
     * @Route("/%eccube_admin_route%/coupon/search/category", name="plugin_coupon_search_category")
     * @Template("@Coupon4/admin/search_category.twig")
     */
    public function searchCategory(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $categoryId = $request->get('category_id');
            $existCategoryId = $request->get('exist_category_id');

            $existCategoryIds = [0];
            if (strlen($existCategoryId > 0)) {
                $existCategoryIds = explode(',', $existCategoryId);
            }

            if (empty($categoryId)) {
                $categoryId = 0;
            }

            $Category = $this->categoryRepository->find($categoryId);
            $Categories = $this->categoryRepository->getList($Category);

            if (empty($Categories)) {
                log_info('search category not found.');
            }

            // カテゴリーの一覧を作成する
            $list = [];
            if ($categoryId != 0 && ! in_array($categoryId, $existCategoryIds)) {
                $name = $Category->getName();
                $list += [$Category->getId() => $name];
            }
            $list += $this->getCategoryList($Categories, $existCategoryIds);

            return [
                'Categories' => $list,
            ];
        }

        return [];
    }

    /**
     * カテゴリーの一覧を作成する.
     *
     * @param Category $Categories
     * @param int $existCategoryIds
     *
     * @return array
     */
    protected function getCategoryList($Categories, $existCategoryIds)
    {
        $result = [];
        foreach ($Categories as $Category) {
            // 除外IDがない場合は配列に値を追加する
            if (count($existCategoryIds) == 0 || ! in_array($Category->getId(), $existCategoryIds)) {
                $name = $this->getCategoryFullName($Category);
                $result += [$Category->getId() => $name];
            }
            // 子カテゴリがあれば更に一覧を作成する
            if (count(($Category->getChildren())) > 0) {
                $childResult = $this->getCategoryList($Category->getChildren(), $existCategoryIds);
                $result += $childResult;
            }
        }

        return $result;
    }

    /**
     * 親カテゴリ名を含むカテゴリ名を取得する.
     *
     * @param Category $Category
     *
     * @return string
     */
    protected function getCategoryFullName(Category $Category)
    {
        if (is_null($Category)) {
            return '';
        }
        $fulName = $Category->getName();
        // 親カテゴリがない場合はカテゴリ名を返す.
        if (is_null($Category->getParent())) {
            return $fulName;
        }
        // 親カテゴリ名を結合する
        $ParentCategory = $Category->getParent();
        while (! is_null($ParentCategory)) {
            $fulName = $ParentCategory->getName().'　＞　'.$fulName;
            $ParentCategory = $ParentCategory->getParent();
        }

        return $fulName;
    }
}
