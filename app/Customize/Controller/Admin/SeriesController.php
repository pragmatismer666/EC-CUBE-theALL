<?php
namespace Customize\Controller\Admin;

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
     * @Route("/%eccube_admin_route%/series/list", name="malldevel_admin_series_list")
     * @Template("@admin/Series/list.twig")
     */
    public function list(Request $request){
        
        $Series = $this->entityManager->getRepository(Series::class)->getList();
        return [
            'Series'    =>  $Series
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/series/{id}/edit", name="malldevel_admin_series_edit")
     * @Template("@admin/Series/edit.twig")
     */
    public function edit(Request $request, $id){
        
        $Series = $this->seriesRepository->find($id);

        if (!$Series) {
            throw new NotFoundHttpException();
        }
        $builder = $this->formFactory->createBuilder(SeriesType::class, $Series);
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $Series = $form->getData();
            $thumbnail = $request->get('thumbnail_temp');
            if ($thumbnail) {
                $this->saveThumbnail($thumbnail, $Series);
                $Series->setThumbnail($thumbnail);
            }
            
            $this->entityManager->persist($Series);
            $this->entityManager->flush();

            
            $this->addSuccess('admin.common.save_complete', 'admin');
        }
        return [
            'Series'    =>  $Series,
            'form'      =>  $form->createView()
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/series/sort_no/move", name="malldevel_admin_series_move", methods={"POST"})
     */
    public function moveSortNo(Request $request, CacheUtil $cacheUtil)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        if ($this->isTokenValid()) {
            $sort_nos = $request->request->all();

            foreach ($sort_nos as $series_id => $sort_no) {
                $SeriesItem = $this->seriesRepository->find($series_id);
                $SeriesItem->setSortNo($sort_no);
                $this->entityManager->persist($SeriesItem);
            }
            $this->entityManager->flush();

            $cacheUtil->clearDoctrineCache();
            return new Response('Successful');
        }
    }

    private function saveThumbnail($thumbnail, $Series) 
    {        
        $temp_path = $this->eccubeConfig['eccube_temp_image_dir'] . '/' . $thumbnail;
        if ( \is_file($temp_path) ) {
            $thumbnail = new File( $temp_path );
            $folder = $this->eccubeConfig['eccube_save_image_dir'] . '/admin';
            if (!\is_dir($folder) ) {
                \mkdir($folder);
            }
            if( $Series->getThumbnail() && \is_file($folder . '/' . $Series->getThumbnail())) {
                unlink( $folder . '/' . $Series->getThumbnail());
            }
            $thumbnail->move( $folder );
        }
    }
}