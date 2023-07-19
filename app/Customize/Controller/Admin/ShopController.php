<?php
namespace Customize\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Repository\Master\PageMaxRepository;
use Eccube\Repository\MemberRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Entity\Member;
use Eccube\Util\CacheUtil;
use Eccube\Util\FormUtil;
use Eccube\Entity\Master\Authority;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpFoundation\File\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Knp\Component\Pager\PaginatorInterface;
use Customize\Form\Type\Admin\SearchApplyType;
use Customize\Repository\ShopRepository;
use Customize\Repository\ApplyRepository;
use Customize\Repository\ShopIdentityDocRepository;
use Customize\Entity\Shop;
use Customize\Entity\Apply;
use Customize\Entity\EAuthority;
use Customize\Entity\Master\ShopStatus;
use Customize\Form\Type\Admin\ShopType;
use Customize\Form\Type\Admin\ApplyType;
use Customize\Services\ShopService;
use Customize\Entity\ShopIdentityDoc;
use Customize\Services\ApplyService;
use Customize\Services\StripeService;
use Symfony\Component\Form\FormError;
use Symfony\Component\Asset\Packages as AssetManager;
use Symfony\Component\Filesystem\Filesystem;
use Customize\Entity\ShopPhoto;

class ShopController extends AbstractController {

    protected $container;
    protected $pageMaxRepository;
    protected $shopRepository;
    protected $shopService;
    protected $memberRepository;
    protected $categoryRepository;
    protected $applyRepository;

    public function __construct(
            ContainerInterface $container,
            PageMaxRepository $pageMaxRepository,
            ShopRepository $shopRepository,
            ShopService $shopService,
            MemberRepository $memberRepository,
            CategoryRepository $categoryRepository,
            ApplyRepository $applyRepository
        ){
        $this->container = $container;
        $this->pageMaxRepository = $pageMaxRepository;
        $this->shopRepository = $shopRepository;
        $this->shopService = $shopService;
        $this->memberRepository = $memberRepository;
        $this->categoryRepository = $categoryRepository;
        $this->applyRepository = $applyRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/shop/list", name="malldevel_admin_shop_list")
     * @Route("/%eccube_admin_route%/shop/list/page/{page_no}", requirements={"page_no" = "\d+"}, name="malldevel_admin_shop_list_page")
     * @Template("@admin/Shop/list.twig")
     */
    public function list(Request $request, $page_no = null, PaginatorInterface $paginator){
        
        $page_count = $this->session->get("malldevel.admin.shop.pagecount",
            $this->eccubeConfig->get("eccube_default_page_count"));

            $page_count_param = (int) $request->get('page_count');
            $pageMaxis = $this->pageMaxRepository->findAll();

            if ( $page_count_param ) {
                foreach( $pageMaxis as $pageMax ) {
                    if ( $pageMax->getName() ) {
                        $page_count = $pageMax->getName();
                        $this->session->set('malldevel.admin.shop.pagecount', $page_count);
                        break;
                    }
                }
            }
            if ( 'POST' === $request->getMethod() ){

            }else {
                if ( $page_no !== null || $request->get('resume') ) {
                    if ( $page_no ) {
                        $this->session->set('malldevel.admin.shop.page_no', ( int ) $page_no );
                    } else {
                        $page_no = $this->session->get('malldevel.admin.shop.page_no', 1);
                    }
                    $viewData = $this->session->get('malldevel.admin.shop.search', []);
                    
                    // $searchData = FormUtil::submitAndGetData( $searchForm, $viewData );
                } else {
                    $page_no = 1;
                    $viewData = [];

                    // TODO search form data
                    
                }
            }
            $qb = $this->shopRepository->getAdminShopsQueryBuilder(null);
            $pagination = $paginator->paginate(
                $qb,
                $page_no,
                $page_count
            );

        return [
            'pagination'    =>  $pagination,
            'pageMaxis'     =>  $pageMaxis,
            'page_no'       =>  $page_no,
            'page_count'    =>  $page_count,
            'has_errors'    =>  false
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/shop/new", name="malldevel_admin_shop_new")
     * @Template("@admin/Shop/create.twig")
     */

    public function create(Request $request, CacheUtil $cacheUtil) {
        $member = $this->getUser();
        if ( $member->hasShop() ) {
            return $this->redirectToRoute('malldevel_admin_shop_edit', ['id' => $member->getShop()->getId()]);
        }
        
        $Shop = new Shop();
        $builder = $this->formFactory->createBuilder(ShopType::class, $Shop);
        $form = $builder->getForm();
        
        $form->handleRequest($request);

        $error = false;

        if( $form->isSubmitted() ) {
            if( $form->isValid() ) {
                $Shop = $form->getData();
                $Kata = $this->shopService->getKata($Shop);
                $Shop->setKatakana($Kata);
                
                if (empty($Shop->getCapital())) {
                    $HiddenStatus = $this->entityManager->getRepository(ShopStatus::class)->find(ShopStatus::DISPLAY_HIDE);
                    $Shop->setStatus($HiddenStatus);
                }

                $this->entityManager->persist( $Shop );
                $this->entityManager->flush();
                $cacheUtil->clearDoctrineCache();
    
                // $member_id = $form->get('member_id')->getData();
                
                $NewMembers = $Shop->getMembers();
                foreach($NewMembers as $Member) {
                    $Member->setShop($Shop);
                    $this->entityManager->persist($Member);
                    $Shop->addMember($Member);
                }
                $this->entityManager->flush();
                

                // $Kata = $this->shopService->getKata($Shop);
                // $Shop->setKatakana($Kata);

                // $this->entityManager->persist($owner);
                // $this->entityManager->flush();
                
                // TODO delete image

                $logo = $form->get('logo')->getData();
                $this->shopService->saveLogo( $logo, $Shop );

                $Tokusho = $form->get('Tokusho')->getData();
                if ($Tokusho) {
                    $Tokusho->setShop($Shop);
                    $this->entityManager->persist($Tokusho);
                    $this->entityManager->flush();
                }

                // TODO check storage limit for user

                // =========================
                // Save categories
                $Categories = $form->get('Category')->getData();
                $this->shopService->saveShopCategories($Shop, $Categories);

                $Serieses = $form->get('Serieses')->getData();
                $this->shopService->saveShopSeries($Shop, $Serieses);
                
                $CurrMember = $this->getUser();
                if ($CurrMember->getRole() == "ROLE_ADMIN") {
                    $this->saveIdentityDocs($Shop, $form);
                }
                $add_shop_photos =  $form->get('add_shop_photos')->getData(); //$this->shopService->saveAssetFromTemp()

                $ShopPhotos = $Shop->getShopPhotos();
                if (!$ShopPhotos || count($ShopPhotos) < 3) {
                    if ($add_shop_photos) {
                        foreach($add_shop_photos as $shop_photo) {
                            $this->shopService->saveAssetFromTemp($shop_photo, $Shop);
                            $ShopPhoto = new ShopPhoto;
                            $ShopPhoto->setFileName($shop_photo)
                                    ->setShop($Shop)
                                    ->setSortNo(1);
                            $this->entityManager->persist($ShopPhoto);
                        }
                        $this->entityManager->flush();
                    }
                }
                $this->addSuccess('admin.common.save_complete', 'admin');
    
                return $this->redirectToRoute( 'malldevel_admin_shop_edit', ['id' => $Shop->getId()] );
            
            }
        }

        $TopCategories = $this->categoryRepository->getList(null);
        return [
            'form'  =>  $form->createView(),
            'TopCategories' =>  $TopCategories,
            'SelectedIds'   =>  []
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/shop/edit/{id}", name="malldevel_admin_shop_edit")
     * @Template("@admin/Shop/create.twig")
     */
    public function edit(Request $request, $id = null, CacheUtil $cacheUtil) {
        
        if( !$id ) {
            $CurrMember = $this->getUser();
            $Shop = $CurrMember->getShop();
            if(!$Shop) {
                throw new NotFoundHttpException();
            }
        }else {
            $Shop = $this->shopRepository->find($id);
        }

        if( !$Shop || $Shop->isDeleted() ) {
            throw new NotFoundHttpException();
        }

        // prepare shop categories
        $categories = [];
        $ShopCategories = $Shop->getShopCategories();
        foreach ( $ShopCategories as $ShopCategory ) {
            $categories[] = $ShopCategory->getCategory();
        }
        $HiddenStatus = $this->entityManager->getRepository(ShopStatus::class)->find(ShopStatus::DISPLAY_HIDE);
        
        $builder = $this->formFactory->createBuilder(ShopType::class, $Shop);
        $form = $builder->getForm();
        
        $Serieses = $Shop->getSerieses();
        $form['Category']->setData($categories);
        $form['Serieses']->setData($Serieses);
        
        $OldMembers = $Shop->getMembers();
        $form->handleRequest($request);
        $error = false;


        if( $form->isSubmitted() ) {
            if( $form->isValid() ) {
                $Shop = $form->getData();
                
                if( $this->getUser()->getRole() === "ROLE_ADMIN" ) {
                    
                    foreach($OldMembers as $Member) {
                        $Member->setShop(null);
                        $this->entityManager->persist($Member);
                    }
                    $NewMembers = $Shop->getMembers();
                    foreach($NewMembers as $Member) {
                        $Member->setShop($Shop);
                        $this->entityManager->persist($Member);
                    }
                    $this->entityManager->flush();
                }
            
                
                $Kata = $this->shopService->getKata($Shop);
                
                $Shop->setKatakana($Kata);
                
                if (empty($Shop->getCapital())) {
                    $Shop->setStatus($HiddenStatus);
                }
                
                $Tokusho = $form->get('Tokusho')->getData();
                
                $Serieses = $form->get("Serieses")->getData();
                $this->shopService->saveShopSeries($Shop, $Serieses);

                $this->entityManager->persist($Shop);
                $this->entityManager->flush();

                if ($Tokusho) {
                    $Tokusho->setShop($Shop);
                    $this->entityManager->persist($Tokusho);
                    $this->entityManager->flush();
                }
                // delete images
                $delete_images = $form->get('delete_images')->getData();
                if (count($delete_images)) {
                    $this->shopService->deleteAssetFromShop($delete_images, $Shop);
                }

                $CurrMember = $this->getUser();
                if ($CurrMember->getRole() == "ROLE_ADMIN") {
                    $this->saveIdentityDocs($Shop, $form);
                }
                // Save Logo
                $logo = $form->get('logo')->getData();
                $this->shopService->saveLogo( $logo, $Shop );

                $add_shop_photos =  $form->get('add_shop_photos')->getData(); //$this->shopService->saveAssetFromTemp()

                $ShopPhotos = $Shop->getShopPhotos();
                $asset_folder = $Shop->getAssetFolder();
                if (!$ShopPhotos || count($ShopPhotos) < 3) {
                    if ($add_shop_photos) {
                        foreach($add_shop_photos as $shop_photo) {
                            $this->shopService->saveAssetFromTemp($shop_photo, $Shop);
                            $ShopPhoto = new ShopPhoto;
                            $ShopPhoto->setFileName($asset_folder . "/" . $shop_photo)
                                    ->setShop($Shop)
                                    ->setSortNo(1);
                            $this->entityManager->persist($ShopPhoto);
                            $Shop->addShopPhoto($ShopPhoto);
                        }
                        $this->entityManager->flush();
                    }
                }
                
                // Save categories
                $Categories = $form->get('Category')->getData();
                $this->shopService->saveShopCategories($Shop, $Categories);
                $this->addSuccess('admin.common.save_complete', 'admin');
            }
        }
        $TopCategories = $this->categoryRepository->getList(null);
        $SelectedIds = array_map(function ( $Category ){
            return $Category->getId();
        }, $form->get('Category')->getData()); 
        return [
            'form'  =>  $form->createView(),
            'Shop'  =>  $Shop,
            'TopCategories' =>  $TopCategories,
            'SelectedIds'   =>  $SelectedIds
        ];
    }

    
    /**
     * @Route("/%eccube_admin_route%/shop/delete/{id}", name="malldevel_admin_shop_delete")
     */
    public function deleteShop(Request $request, $id, CacheUtil $cacheUtil) {
        if( $this->getUser()->getRole() !== "ROLE_ADMIN" || !$id ) {
            throw new NotFoundHttpException();
        }
        $Shop = $this->shopRepository->find($id);
        $Shop->setIsDeleted(true);
        $this->entityManager->persist($Shop);
        $this->entityManager->flush();
        $cacheUtil->clearDoctrineCache();

        return $this->redirectToRoute("malldevel_admin_shop_list");
    }

    
    /**
     * @Route("/%eccube_admin_route%/shop/bulkdelete", name="malldevel_admin_shop_bulk_delete")
     */
    public function bulkDelete(Request $request, CacheUtil $cacheUtil) {
        $ids = $request->get('ids');
        $Shops = $this->shopRepository->getByIdList($ids);
        foreach($Shops as $Shop){
            $Shop->setIsDeleted(true);
            $this->entityManager->persist($Shop);
        }
        $this->entityManager->flush();
        $cacheUtil->clearDoctrineCache();

        return $this->redirectToRoute('malldevel_admin_shop_list');
    }

    /**
     * @Route("/%eccube_admin_route%/shop/logo_upload", name="malldevel_admin_shop_logo_upload", methods={"POST"})
     */
    public function addLogo(Request $request) {
        if( !$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $images = $request->files->get('malldevel_shop');

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];
        $files = [];
        if (count($images) > 0) {
            foreach ($images as $image) {
                // var_dump($img); die();
                // foreach ($img as $image) {
                    //ファイルフォーマット検証
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    // 拡張子
                    $extension = $image->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), $allowExtensions)) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($this->eccubeConfig['eccube_temp_image_dir'], $filename);
                    $files[] = $filename;
                }
            // }
        }
        return $this->json(['files' => $files], 200);
    }

    /**
     * @Route("/%eccube_admin_route%/shop/iddocs/image/add", name="malldevel_admin_iddocs_add", methods={"POST"})
     */
    public function addImage(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $member = $this->getUser();
        if ($member->getRole() !== "ROLE_ADMIN") {
            throw new BadRequestHttpException();
        }

        $images = $request->files->get('malldevel_shop');

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];
        $files = [];
        if (count($images) > 0) {
            foreach ($images as $img) {
                foreach ($img as $image) {
                    //ファイルフォーマット検証
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    // 拡張子
                    $extension = $image->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), $allowExtensions)) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($this->eccubeConfig['eccube_temp_image_dir'], $filename);
                    $files[] = $filename;
                }
            }
        }

        return $this->json(['files' => $files], 200);
    }

    /**
     * @Route("/%eccube_admin_route%/shop/stripe_verif/requirements/{id}", name="malldevel_admin_shop_stripe_verif_status")
     */
    public function verifyStatus(Request $request, $id)
    {
        $member = $this->getUser();
        if ($member->getRole() != "ROLE_ADMIN") {
            throw new NotFoundHttpException();
        }
        $Shop = $this->shopRepository->find($id);
        if (!$Shop || !$Shop->getStripeId()) {
            return $this->json([
                'success'   =>  false,
                'error'     =>  trans('malldevel.admin.stripe.error.not_registered')
            ]);
        }
        $stripe_id = $Shop->getStripeId();

        $stripe_service = $this->container->get('malldevel.stripe.service');
        $stripe_account = $stripe_service->retrieveAccount($stripe_id);
        $req_errors = $stripe_account->requirements->errors;

        $errors = [];
        foreach($req_errors as $req_error) {
            $errors[] = $req_error->reason;
        }

        return $this->json([
            'success'       =>  true,
            'requirements'  =>  $errors
        ]);
    }
    /**
     * @Route("/%eccube_admin_route%/shop/stripe_verif/upload_identity_document/{id}", name="malldevel_admin_shop_stripe_upload_identity")
     */
    public function uploadIdentityDocToStripe(Request $request, $id)
    {
        $member = $this->getUser();
        if ($member->getRole() != "ROLE_ADMIN") {
            throw new NotFoundHttpException();
        }
        $Shop = $this->shopRepository->find($id);
        if (!$Shop || !$Shop->getStripeId()) {
            return $this->json([
                'success'   =>  false,
                'error'     =>  trans('malldevel.admin.stripe.error.not_registered')
            ]);
        }
        $stripe_id = $Shop->getStripeId();

        $ShopIdentityDocs = $Shop->getShopIdentityDocs();

        if (count($ShopIdentityDocs)) {
            $iddoc = $ShopIdentityDocs[0];
            $file_name = $iddoc->getFileName();
            $file_name = $this->eccubeConfig['eccube_save_image_dir'] . '/' . $file_name;

            $stripe_service = $this->container->get('malldevel.stripe.service');

            $account = $stripe_service->uploadFile($stripe_id, $file_name);
            return $this->json([
                'success'   =>  true,
            ]);
        } else {
            return $this->json([
                'success'   =>  false,
                'error'     => trans("malldevel.admin.stripe.upload_iddoc.no_iddoc")
            ]);
        }
    }
    
    public static function getFormErrorsTree(FormInterface $form): array
    {
        $errors = [];

        if (count($form->getErrors()) > 0) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }
        } else {
            foreach ($form->all() as $child) {
                $childTree = self::getFormErrorsTree($child);

                if (count($childTree) > 0) {
                    $errors[$child->getName()] = $childTree;
                }
            }
        }

        return $errors;
    }

    private function saveIdentityDocs($Shop, $form)
    {
        $shopIdDocRepository = $this->container->get(ShopIdentityDocRepository::class);
        // 画像の登録
        $add_images = $form->get('add_iddocs')->getData();
        foreach ($add_images as $add_image) {
            $ShopIdentityDoc = new ShopIdentityDoc();
            $ShopIdentityDoc
                ->setFileName('admin/' . $add_image)
                ->setShop($Shop)
                ->setSortNo(1);
            $Shop->addShopIdentityDoc($ShopIdentityDoc);
            $this->entityManager->persist($ShopIdentityDoc);

            // 移動
            $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image);
            $file->move($this->eccubeConfig['eccube_save_image_dir'] . '/admin');
        }
        $this->entityManager->flush();

        // 画像の削除
        $delete_images = $form->get('delete_images')->getData();
        foreach ($delete_images as $delete_image) {
            $idDoc = $shopIdDocRepository
                ->findOneBy(['file_name' => $delete_image]);

            if (!$idDoc) continue;
            // 追加してすぐに削除した画像は、Entityに追加されない
            if ($idDoc instanceof ShopIdentityDoc) {
                $Shop->removeShopIdentityDoc($idDoc);
                $this->entityManager->remove($idDoc);
            }
            $this->entityManager->persist($Shop);

            // 削除
            if (!\is_file($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image)) {

            }
            $fs = new Filesystem();
            $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
        }
        $this->entityManager->persist($Shop);
        $this->entityManager->flush();
    }

    private function saveShopPhotos($Shop, $form)
    {
        $shopIdDocRepository = $this->container->get(ShopPhotoRepository::class);
        // 画像の登録
        $add_images = $form->get('add_shop_photos')->getData();
        foreach ($add_images as $add_image) {
            $ShopIdentityDoc = new ShopPhoto();
            $ShopIdentityDoc
                ->setFileName('admin/' . $add_image)
                ->setShop($Shop)
                ->setSortNo(1);
            $Shop->addShopIdentityDoc($ShopIdentityDoc);
            $this->entityManager->persist($ShopIdentityDoc);

            // 移動
            $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image);
            $file->move($this->eccubeConfig['eccube_save_image_dir'] . '/admin');
        }
        $this->entityManager->flush();

        // 画像の削除
        $delete_images = $form->get('delete_images')->getData();
        foreach ($delete_images as $delete_image) {
            $idDoc = $shopIdDocRepository
                ->findOneBy(['file_name' => $delete_image]);

            if (!$idDoc) continue;
            // 追加してすぐに削除した画像は、Entityに追加されない
            if ($idDoc instanceof ShopIdentityDoc) {
                $Shop->removeShopIdentityDoc($idDoc);
                $this->entityManager->remove($idDoc);
            }
            $this->entityManager->persist($Shop);

            // 削除
            if (!\is_file($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image)) {

            }
            $fs = new Filesystem();
            $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
        }
        $this->entityManager->persist($Shop);
        $this->entityManager->flush();
    }

    // for apply list
    /**
     * @Route("/%eccube_admin_route%/apply/list", name="malldevel_admin_apply_list")
     * @Route("/%eccube_admin_route%/apply/list/page/{page_no}", requirements={"page_no" = "\d+"}, name="malldevel_admin_apply_list_page")
     * @Template("@admin/Shop/apply_list.twig")
     */
    public function applyList(Request $request, $page_no = null, PaginatorInterface $paginator) {
        
        $page_count = $this->session->get("malldevel.admin.shop_apply.pagecount",
            $this->eccubeConfig->get("eccube_default_page_count"));

        $page_count_param = (int) $request->get('page_count');
        $pageMaxis = $this->pageMaxRepository->findAll();

        if ( $page_count_param ) {
            foreach( $pageMaxis as $pageMax ) {
                if ( $pageMax->getName() ) {
                    $page_count = $pageMax->getName();
                    $this->session->set('malldevel.admin.shop_apply.pagecount', $page_count);
                    break;
                }
            }
        }

        $searchForm = $this->formFactory->createBuilder(SearchApplyType::class)->getForm();
        if ( 'POST' === $request->getMethod() ){
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $page_no = 1;
                $searchData = $searchForm->getData();
                $this->session->set('malldevel.admin.shop_apply.search', FormUtil::getViewData($searchForm));
                $this->session->set('malldevel.admin.shop_apply.page_no', $page_no);
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
        }else {
            if ( $page_no !== null || $request->get('resume') ) {
                if ( $page_no ) {
                    $this->session->set('malldevel.admin.shop_apply.page_no', ( int ) $page_no );
                } else {
                    $page_no = $this->session->get('malldevel.admin.shop_apply.page_no', 1);
                }
                $viewData = $this->session->get('malldevel.admin.shop_apply.search', []);
                
                $searchData = FormUtil::submitAndGetData( $searchForm, $viewData );
            } else {
                $page_no = 1;
                $viewData = [];

                // TODO search form data
                $viewData = FormUtil::getViewData($searchForm);
                $searchData = FormUtil::submitAndGetData($searchForm, $viewData);

                // セッション中の検索条件, ページ番号を初期化.
                $this->session->set('malldevel.admin.shop_apply.search', $viewData);
                $this->session->set('malldevel.admin.shop_apply.page_no', $page_no);
            }
        }
        $qb = $this->applyRepository->getAdminQueryBuilder($searchData);

        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );

        return [
            'searchForm' => $searchForm->createView(),
            'pagination'    =>  $pagination,
            'pageMaxis'     =>  $pageMaxis,
            'page_no'       =>  $page_no,
            'page_count'    =>  $page_count,
            'has_errors'    =>  false
        ];
    }
    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();
    
        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }
    
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
    
        return $errors;
    }
    /**
     * @Route("/%eccube_admin_route%/apply/edit/{id}", name="malldevel_admin_apply_edit")
     * @Template("@admin/Shop/apply_edit.twig")
     */
    public function editApply(Request $request, $id, ApplyService $applyService) {
        if (!$id || $this->getUser()->getRole() !== "ROLE_ADMIN") {
            throw new NotFoundHttpException();
        }
        $Apply = $this->applyRepository->get($id);
        $old_status = $Apply->getStatus();

        $builder = $this->formFactory->createBuilder(ApplyType::class, $Apply);
        $form = $builder->getForm();

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $Apply = $form->getData();

            if ($Apply->getStatus() == Apply::STATUS_ALLOWED && $old_status != $Apply->getStatus()) {
                $Member = $applyService->createApplicant($Apply);
            }

            if ($Apply->getStatus() == Apply::STATUS_HOLD && $old_status != $Apply->getStatus()) {
                $mailService = $this->get("malldevel.email.service");
                $mailService->sendApplicantHoldMail($Apply);
            }
            $this->entityManager->persist($Apply);
            $this->entityManager->flush();
        }

        return [
            'form'  =>  $form->createView(),
            'Apply' =>  $Apply,
        ];
    }


     /**
     * @Route("/%eccube_admin_route%/apply/update_status", name="malldevel_admin_apply_update_status")
     */
    public function applyUpdateStatus(Request $request, ApplyService $applyService) {
        if ($this->getUser()->getRole() !== "ROLE_ADMIN") {
            throw new NotFoundHttpException();
        }
        $id = $request->get("id");
        $Apply = $this->applyRepository->get($id);
        if (!$Apply) {
            throw new NotFoundHttpException();
        }
        $oldStatus = $Apply->getStatus();
        $status = $request->get('status');
        $Apply->setStatus($status);
        if ($Apply->getStatus() == Apply::STATUS_ALLOWED && $oldStatus != $Apply->getStatus()) {
            $existing = $this->entityManager->getRepository(Member::class)->findOneBy(['login_id' => $Apply->getLoginId()]);
            if ($existing) {
                return $this->json([
                    'success' => false,
                    'message' => trans('malldevel.admin.apply.error.duplicated_login_id')
                ]);
            }

            $Member = $applyService->createApplicant($Apply);
        }

        if ($Apply->getStatus() == Apply::STATUS_HOLD && $oldStatus != $Apply->getStatus()) {
            $mailService = $this->get("malldevel.email.service");
            $mailService->sendApplicantHoldMail($Apply);
        }
        $this->entityManager->persist($Apply);
        $this->entityManager->flush();

        $this->addSuccess("malldevel.admin.apply.status_update_success", "admin");
        
        return $this->json([
            'success'   =>  true
        ]);
    }
    // for applicant
    /**
     * @Route("/%eccube_admin_route%/apply/stripe_apply", name="malldevel_applicant_stripe_apply")
     * @Template("@admin/Shop/stripe_apply.twig")
     */
    public function stripeApply(StripeService $stripe_service)
    {
        $Member = $this->getUser();
        if (!$Member || $Member->getRole() != "ROLE_APPLICANT") {
            return $this->redirectToRoute("admin_homepage");
        }
        $apply_id = $Member->getApplyId();
        if (!$apply_id) {
            throw new NotFoundHttpException();
        }
        $Apply = $this->applyRepository->get($apply_id);

        if ($Apply->getStatus() == Apply::STATUS_PROCESSING) {
            throw new NotFoundHttpException();
        }
        if ($Apply->getStatus() == Apply::STATUS_HOLD) {
            return $this->render("@admin/Shop/apply_on_hold.twig");
        }

        if (empty($Apply->getStripeId())) {
            return [];
        }

        // check charge enabled
        $charge_enabled = $Apply->isChargeEnabled();
        if (!$charge_enabled) {
            try {
                $account = $stripe_service->retrieveAccount($Apply->getStripeId());
                if (!$account) return [];
                
                // accept Tos
                $account = $stripe_service->acceptTos($account);
                // TODO add stripe connect tos section int tos
                if (!$account->charges_enabled) {
                    return [];  
                } 
            } catch (\Exception $ex) {
                return [];
            }
        } 
        $shop = $this->shopRepository->findOneBy(['apply_id' => $Apply->getId()]);
        if (!$shop) {
            $shop = $this->shopService->createShopFromApply($Apply);
        }
        $this->shopService->createDefaultDeliveries($shop);
        $Apply->setChargeEnabled(1);
        $this->entityManager->persist($Apply);
        $this->entityManager->flush();
        $Member->setShop($shop);

        $Authority = $this->entityManager->getRepository(Authority::class)->find(EAuthority::SHOP_OWNER);
        $Member->setAuthority($Authority);
        

        $this->entityManager->persist($Member);
        $this->entityManager->flush();
        return $this->redirectToRoute("admin_homepage");
    }
    /**
     * @Route("/%eccube_admin_route%/apply/stripe_apply_request", name="malldevel_applicant_stripe_apply_request")
     */
    public function stripeApplyRequest(StripeService $stripe_service)
    {
        $Member = $this->getUser();
        if (!$Member || $Member->getRole() != "ROLE_APPLICANT") {
            return $this->redirectToRoute("admin_homepage");
        }
        $apply_id = $Member->getApplyId();
        if (!$apply_id) {
            throw new NotFoundHttpException();
        }
        $Apply = $this->applyRepository->get($apply_id);

        $stripe_account = $stripe_service->createAccount($Apply);
        $Apply->setStripeId($stripe_account->id);

        $this->entityManager->persist($Apply);
        $this->entityManager->flush();
        
        $account_links = $stripe_service->createAccountLink($stripe_account);

        if ($account_links) {
            return $this->redirect($account_links->url);
        } else {
            return $this->redirectToRoute("malldevel_applicant_stripe_apply");
        }
    }
}