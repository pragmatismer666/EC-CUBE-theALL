<?php

namespace Customize\Controller\Admin;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Customize\Services\ShopService;
use Customize\Repository\ShopRepository;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageController extends AbstractController {

    protected $container;
    protected $shopService;
    protected $shopRepository;

    public function __construct(
        ContainerInterface $container,
        ShopService $shopService
    ){
        $this->container = $container;
        $this->shopService = $shopService;
        $this->shopRepository = $container->get(ShopRepository::class);
    }
    
    /**
     * @Route("/%eccube_admin_route%/file/image_upload", name="malldevel_admin_image_upload", methods={"POST"})
     * @Route("/%eccube_admin_route%/file/image_upload/{id}", name="malldevel_admin_image_upload_with_id", methods={"POST"})
     */
    public function imageUpload(Request $request, $id = null) {
        $member = $this->getUser();
        if( $member->getRole() == "ROLE_ADMIN" && $id ) {
            $Shop = $this->shopRepository->find($id);
        }
        else if ($member->getRole() == "ROLE_ADMIN") {
            $Shop = null;
        }
        else {
            $Shop = $member->getShop();
            if ( !$Shop ) {
                die("shop is none");
                throw new BadRequestHttpException();
            }
        }

        $image = $this->recursivelyFindUploadedImage($request->files);

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];
            
        $mimeType = $image->getMimeType();
        if (0 !== strpos($mimeType, 'image')) {
            throw new UnsupportedMediaTypeHttpException();
        }

        // 拡張子
        $extension = $image->getClientOriginalExtension();
        if (!in_array(strtolower($extension), $allowExtensions)) {
            throw new UnsupportedMediaTypeHttpException();
        }
        $file_name = $this->shopService->saveAsset($image, $Shop);
        return $this->json([
            'uploaded'  =>  1,
            'fileName'  =>  $file_name,
            'url'       =>  '/html/upload/save_image/' . (!empty($Shop) ? $Shop->getAssetfolder() : 'admin' ) . '/' . $file_name
        ], 200);
    }

    /**
     * @Route("/%eccube_admin_route%/file/_image_upload", name="malldevel_admin_image_temp_upload", methods={"POST"})
     */
    public function imageTempUpload(Request $request) {
        if( !$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }
        $image = $this->recursivelyFindUploadedImage($request->files);

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];
        
            
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
        
        return $this->json(['files' => [$filename]], 200);
    }

    /**
     * @param FileBag|array $fileBag
     * @return UploadedFile|null
     */
    protected function recursivelyFindUploadedImage($fileBag)
    {
        foreach($fileBag as $item) {
            if ($item instanceof UploadedFile) {
                return $item;
            } else {
                if (is_array($item)) {
                    $file = $this->recursivelyFindUploadedImage($item);
                    if ($file) {
                        return $file;
                    }
                }
            }
        }
        return null;
    }
}
