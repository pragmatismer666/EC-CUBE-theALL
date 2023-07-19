<?php

namespace Customize\Controller\Admin;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Paginator;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Util\CacheUtil;
use Eccube\Util\FormUtil;
use Eccube\Common\Constant;
use Customize\Entity\ShopBlog;
use Customize\Form\Type\Admin\ShopBlogType;
use Customize\Repository\FeatureRepository;
use Customize\Repository\ShopRepository;
use Customize\Repository\ProductRepository;
use Customize\Form\Type\Admin\SearchFeatureType;
use Customize\Form\Type\Admin\FeatureType;
use Customize\Entity\Feature;
use Customize\Entity\FeatureProduct;
use Customize\Entity\FeatureShop;
use Customize\Repository\FeatureShopRepository;

class FeatureController extends AbstractController
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
     * @var FeatureRepository
     */
    protected $featureRepository;

    public function __construct(
        ContainerInterface $container,
        PageMaxRepository $pageMaxRepository,
        FeatureRepository $featureRepository
    )
    {
        $this->container = $container;
        $this->pageMaxRepository = $pageMaxRepository;
        $this->featureRepository = $featureRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/content/feature", name="malldevel_admin_feature")
     * @Route("/%eccube_admin_route%/content/feature/page/{page_no}", requirements={"page_no" = "\d+"}, name="malldevel_admin_feature_page")
     * @Template("@admin/Content/feature.twig")
     * @param Request $request
     * @param Paginator $paginator
     *
     * @return array
     */
    public function index(Request $request, $page_no = null, Paginator $paginator)
    {
        $builder = $this->formFactory->createBuilder(SearchFeatureType::class);

        $searchForm = $builder->getForm();

        $page_count = $this->session->get('malldevel.admin.feature.search.page_count',
            $this->eccubeConfig->get('eccube_default_page_count'));

        $page_count_param = (int) $request->get('page_count');
        $pageMaxis = $this->pageMaxRepository->findAll();

        if ($page_count_param) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    $this->session->set('malldevel.admin.feature.search.page_count', $page_count);
                    break;
                }
            }
        }
        if ($request->getMethod() === "POST") {
            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $page_no = 1;
                $searchData = $searchForm->getData();

                $this->session->set('malldevel.admin.feature.search', FormUtil::getViewData($searchForm));
                $this->session->set('malldevel.admin.feature.search.page_no', $page_no);
            } else {
                return [
                    'searchForm'    =>  $searchForm->createView(),
                    'pagination'    =>  [],
                    'pageMaxis'     =>  $pageMaxis,
                    'page_no'       =>  $page_no,
                    'page_count'    =>  $page_count,
                    'has_errors'    =>  true
                ];
            }
        } else {
            if ($page_no !== null || $request->get('resume')) {
                if ($page_no) {
                    $this->session->set('malldevel.admin.feature.search.page_no', (int)$page_no);
                } else {
                    $page_no = $this->session->get('malldevel.admin.feature.search.page_no', 1);
                }
                $viewData = $this->session->get('malldevel.admin.feature.search', []);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
            } else {
                $page_no = 1;
                $viewData = [];

                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

                $this->session->set('malldevel.admin.feature.search', $viewData);
                $this->session->set('malldevel.admin.feature.search.page_no', $page_no);
            }
        }
        $qb = $this->featureRepository->getBySearchQuery($searchData);
        $pagination = $paginator->paginate(
            $qb, $page_no, $page_count
        );

        return [
            'searchForm'    =>  $searchForm->createView(),
            'pagination'    =>  $pagination,
            'pageMaxis'     =>  $pageMaxis,
            'page_no'       =>  $page_no,
            'page_count'    =>  $page_count,
            'has_errors'    =>  false,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/feature/new", name="malldevel_admin_feature_new")
     * @Route("/%eccube_admin_route%/content/feature/{id}/edit", requirements={"id" = "\d+"}, name="malldevel_admin_feature_edit")
     * @Template("@admin/Content/feature_edit.twig")
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
        if ($id) {
            $Feature = $this->featureRepository->find($id);
            if (!$Feature) {
                throw new NotFoundHttpException();
            }
            /** @var \Eccube\Entity\Member $Member */
        } else {
            $Feature = new Feature();
            $Feature->setCreateDate(new \DateTime());
            $Feature->setUpdateDate(new \DateTime());
        }

        $builder = $this->formFactory->createBuilder(FeatureType::class, $Feature);
        
        $form = $builder->getForm();
        $Shops = $Feature->getShops();
        $form['Shops']->setData($Shops);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnail = $Feature->getThumbnail();
            if ($thumbnail) {
                $this->saveThumbnail($thumbnail, $Feature);
            }

            $Shops = $form->get('Shops')->getData();
            if (!$this->checkCoincidShops($Feature, $Shops)) {
                $FeatureShops = $Feature->getFeatureShops();
                $featureShopRepository = $this->container->get(FeatureShopRepository::class);
                $featureShopRepository->createQueryBuilder('fs')
                    ->delete()
                    ->where('fs In(:FeatureShops)')
                    ->setParameter('FeatureShops', $FeatureShops)
                    ->getQuery()
                    ->execute();
                if (count($Shops)) {
                    foreach($Shops as $Shop) {
                        $FeatureShop = new FeatureShop;
                        $FeatureShop->setFeature($Feature);
                        $FeatureShop->setShop($Shop);
                        $this->entityManager->persist($FeatureShop);
                        $Feature->addFeatureShop($FeatureShop);
                    }
                }
            }

            $ids = $form->get('products')->getData();
            $productRepository = $this->container->get(ProductRepository::class);

            $Products = $productRepository->getProductsByIds($ids);

            if (!$this->checkCoincidProducts($Feature, $Products)) {
                $FeatureProducts = $Feature->getFeatureProducts();
                foreach($FeatureProducts as $FeatureProduct) {
                    $this->entityManager->remove($FeatureProduct);
                    $Feature->removeFeatureProduct($FeatureProduct);
                }
                $this->entityManager->flush();
                if (count($Products) > 0) {
                    foreach($Products as $Product) {
                        $FeatureProduct = new FeatureProduct;
                        $FeatureProduct->setFeature($Feature);
                        $FeatureProduct->setProduct($Product);
                        $this->entityManager->persist($FeatureProduct);
                        $Feature->addFeatureProduct($FeatureProduct);
                    }
                }
            }
            
            $this->entityManager->persist($Feature);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            $cacheUtil->clearDoctrineCache();

            return $this->redirectToRoute('malldevel_admin_feature_edit', ['id' => $Feature->getId()]);
        }

        return [
            'form' => $form->createView(),
            'Feature' => $Feature,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/feature/{id}/delete", requirements={"id" = "\d+"}, name="malldevel_admin_feature_delete")
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param int|null $id
     */
    public function delete(Request $request, $id)
    {
        $Feature = $this->featureRepository->find($id);
        if (!$Feature) {
            throw new NotFoundHttpException("Feature not found");
        }
        
        $this->entityManager->remove($Feature);
        $this->entityManager->flush();

        return $this->json([
            'success'   =>  true
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/content/feature/{id}/show", requirements={"id" = "\d+"}, name="malldevel_admin_feature_show")
     *
     * @param Request $request
     * @param int|null $id
     */
    public function setVisible(Request $request, $id)
    {
        $action = $request->request->get("action");
        $Feature = $this->featureRepository->find($id);
        if (!$Feature) {
            throw new NotFoundHttpException("Feature not found");
        }

        if ($action == "show") {
            $Feature->setVisible(true);
        } else if ($action == "hide") {
            $Feature->setVisible(false);
        } else {
            throw new NotFoundHttpException("Action not found");
        }
        $this->entityManager->persist($Feature);
        $this->entityManager->flush();

        return $this->json([
            'success'   =>  true
        ]);
    }

    /**
     * @Route("/%eccube_admin_route%/content/feature/get_products", name="malldevel_admin_api_get_products")
     *
     * @param Request $request
     * @param int|null $id
     */
    public function getProducts(Request $request)
    {
        $productRepository = $this->container->get(ProductRepository::class);
        $shop_ids = $request->request->get('shop_ids')??null;
        if (!$shop_ids) {
            $ids = $request->request->get('ids')??[];
            if (empty($ids)) {
                throw new NotFoundHttpException();
            }
            $Products = $productRepository->getProductsByIds($ids);
        } else {
            $start = $request->request->get("start")??0;
            $limit = $request->request->get('limit')??10;
    
            // $shopRepository = $this->container->get(ShopRepository::class);
    
            $Products = $productRepository->getProductsByShopIds($shop_ids, $start, $limit);
        }

        if (count($Products) > 0) {
            $res = [];
            foreach($Products as $Product) {
                $arr = $Product->getAsArray();
                $arr['price'] = $this->currencyFormat($arr['price']);
                $res[] = $arr;
            }
            return $this->json($res);
        } else {
            return $this->json(['reachedMax' => true]);
        }

    }


    private function saveThumbnail($thumbnail, $Feature) 
    {        
        $temp_path = $this->eccubeConfig['eccube_temp_image_dir'] . '/' . $thumbnail;
        if ( \is_file($temp_path) ) {
            $thumbnail = new File( $temp_path );
            $folder = $this->eccubeConfig['eccube_save_image_dir'] . '/admin';
            if (!\is_dir($folder) ) {
                \mkdir($folder);
            }
            if( $Feature->getThumbnail() && \is_file($folder . '/' . $Feature->getThumbnail())) {
                unlink( $folder . '/' . $Feature->getThumbnail());
            }
            $thumbnail->move( $folder );
        }
    }
    private function currencyFormat($price)
    {
        $prices = explode(trans('admin.common.separator__range'), $price);

        $locale = $this->eccubeConfig['locale'];
        $currency = $this->eccubeConfig['currency'];
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        if (count($prices) > 1) {
            $res = array_map(function($price) use($formatter, $currency) {
                return $formatter->formatCurrency($price, $currency);
            }, $prices);
            return implode(trans('admin.common.separator__range'), $res);
        } else {
            return $formatter->formatCurrency($price, $currency);
        }
    }

    private function checkCoincidProducts($Feature, $Products) {
        $FeatureProducts = $Feature->getFeatureProducts();
        if (count($FeatureProducts) != count($Products)) {
            return false;
        }

        foreach($FeatureProducts as $FeatureProduct) {
            $fProduct = $FeatureProduct->getProduct();

            if (!\in_array($fProduct, $Products)) return false;
        }
        return true;
    }
    private function checkCoincidShops($Feature, $Shops)
    {
        $OldShops = $Feature->getShops();
        if (count($Shops) != count($OldShops)) {
            return false;
        }

        foreach($OldShops as $Shop) {
            if (!\in_array($Shop, $Shops)) return false;
        }
        return true;
    }
}
