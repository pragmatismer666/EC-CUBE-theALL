<?php

namespace Customize\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Eccube\Controller\AbstractController;
use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Eccube\Form\Type\Master\ProductListMaxType;
use Eccube\Form\Type\Master\ProductListOrderByType;
use Eccube\Repository\BaseInfoRepository;
use Customize\Repository\FeatureRepository;
use Customize\Repository\FeatureProductRepository;
use Customize\Event\MallDevelEvents;

class FeatureController extends AbstractController
{

    protected $container;
    protected $featureProductRepository;
    protected $featureRepository;
    protected $BaseInfo;
    
    public function __construct(
        ContainerInterface $container,
        FeatureRepository $featureRepository,
        FeatureProductRepository $featureProductRepository,
        BaseInfoRepository $baseInfoRepository
    )
    {
        $this->container = $container;
        $this->featureProductRepository = $featureProductRepository;
        $this->featureRepository = $featureRepository;
        $this->BaseInfo = $baseInfoRepository->get();
    }

    /**
     * @Route("/feature/detail/{id}", name="malldevel_front_feature_detail")
     * @Template("Feature/detail.twig")
     */
    public function detail(Request $request, $id, Paginator $paginator)
    {
        $Feature = $this->featureRepository->find($id);

        if ($this->BaseInfo->isOptionNostockHidden()) {
            $this->entityManager->getFilters()->enable('option_nostock_hidden');
        }

        $pageno = $request->get('pageno', 1);
        
        // 表示件数
        $builder = $this->formFactory->createNamedBuilder(
            'disp_number',
            ProductListMaxType::class,
            null,
            [
                'required' => false,
                'allow_extra_fields' => true,
            ]
        );
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_DISP, $event);

        $dispNumberForm = $builder->getForm();

        $dispNumberForm->handleRequest($request);

        $per_page = $dispNumberForm->getData();
        if (empty($per_page)) {
            $per_page = 10;
        } else {
            $per_page = $per_page->getId();
        }

        // ソート順
        $builder = $this->formFactory->createNamedBuilder(
            'orderby',
            ProductListOrderByType::class,
            null,
            [
                'required' => false,
                'allow_extra_fields' => true,
            ]
        );
        if ($request->getMethod() === 'GET') {
            $builder->setMethod('GET');
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_PRODUCT_INDEX_ORDER, $event);

        $orderByForm = $builder->getForm();

        $orderByForm->handleRequest($request);


        $qb = $this->featureProductRepository->getByFeature($Feature);

        $query = $qb->getQuery();

        /** @var SlidingPagination $pagination */
        $pagination = $paginator->paginate(
            $query,
            $pageno,
            $per_page
        );

        $pageData = compact(
            'Feature',
            'pagination',
            'pageno'
        );
        $pageData['disp_number_form'] = $dispNumberForm->createView();
        $pageData['order_by_form'] = $orderByForm->createView();
        
        return $pageData;
    }
}