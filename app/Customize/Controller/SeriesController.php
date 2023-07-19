<?php
namespace Customize\Controller;

use Customize\Entity\Master\ShopStatus;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Repository\MemberRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Entity\Member;
use Eccube\Util\CacheUtil;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Repository\ShopRepository;
use Customize\Entity\Shop;
use Customize\Entity\Master\Series;
use Customize\Form\Type\Admin\ShopType;
use Customize\Services\ShopService;
use Symfony\Component\Form\FormError;
use Customize\Repository\Master\SeriesRepository;
use Symfony\Component\HttpFoundation\Response;
use Customize\Form\Type\Admin\SeriesType;
use Customize\Repository\ProductRepository;
class SeriesController extends AbstractController {

    protected $container;
    protected $seriesRepository;

    public function __construct(
            ContainerInterface $container,
            SeriesRepository $seriesRepository
        ){
        $this->container = $container;
        $this->seriesRepository = $seriesRepository;
    }

    /**
     * @Route("/series", name="malldevel_front_series_list")
     * @Template("Series/list.twig")
     */
    public function list(Request $request){
        
        $SeriesList = $this->seriesRepository->getList();
        return [
            'SeriesList'    =>  $SeriesList
        ];
    }

    /**
     * @Route("/series/{id}", name="malldevel_front_series_detail")
     * @Template("Series/detail.twig")
     */
    public function detail(Request $request, $id, ProductRepository $productRepository)
    {
        $Series = $this->seriesRepository->find($id);

        if (!$Series) {
            throw new NotFoundHttpException();
        }

        $pageno = $request->get('pageno', 1);
        $per_page = $request->get('disp_number', 12);

        $qb = $productRepository->getBySeries($Series);
        $qb->addSelect('ss')
            ->innerJoin('ps.Status', 'ss')
            ->andWhere('ps.is_deleted = :deleted')
            ->setParameter('deleted', Constant::DISABLED)
            ->andWhere('ss = :shopStatus')
            ->setParameter('shopStatus', ShopStatus::DISPLAY_SHOW);
        $Products = $qb->setMaxResults($this->eccubeConfig->get('malldevel.series.detail.product_count'))->getQuery()->getResult();

        return compact('Products', 'Series');
    }
    
}