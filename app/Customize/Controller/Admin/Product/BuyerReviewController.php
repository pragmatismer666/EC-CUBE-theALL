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

namespace Customize\Controller\Admin\Product;

use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Util\FormUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Eccube\Controller\Admin\Product\ProductController as ParentController;
use Eccube\Repository\ProductRepository;
use Eccube\Entity\Product;

use Customize\Entity\BuyerReview;
use Customize\Repository\BuyerReviewRepository;
use Customize\Form\Type\Admin\BuyerReviewType;
use Symfony\Component\Form\FormInterface;

class BuyerReviewController extends AbstractController
{
    protected $productRepository;
    protected $buyerReviewRepository;

    public function __construct(
        ProductRepository $productRepository,
        BuyerReviewRepository $buyerReviewRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->buyerReviewRepository = $buyerReviewRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/product/{id}/buyer_review", name="malldevel_admin_buyer_review_index")
     * @Template("@admin/Product/buyer_review.twig")
     */
    public function index(Request $request, $id)
    {
        $Product = $this->productRepository->find($id);
        if (!$Product) {
           throw new NotFoundHttpException();
        }
        $builder = $this->formFactory
            ->createBuilder(BuyerReviewType::class);

        $form = $builder->getForm()->createView();

        $BuyerReviews = $this->buyerReviewRepository->getByProduct($Product);

        return compact("Product", "BuyerReviews", "form");
    }

    /**
     * 
     * @Route("/%eccube_admin_route%/product/{product_id}/buyer_review/new", requirements={"product_id" = "\d+"}, name="malldevel_admin_buyer_review_new")
     * @Route("/%eccube_admin_route%/product/{product_id}/buyer_review/edit/{buyer_review_id}", requirements={"product_id" = "\d+", "buyer_review_id" = "\d+"}, name="malldevel_admin_buyer_review_edit")
     * @Template("@admin/Product/buyer_review.twig")
     */
    public function edit(Request $request, $product_id, $buyer_review_id = null)
    {
        $Product = $this->productRepository->find($product_id);
        if (!$Product) 
            throw new NotFoundHttpException();
        $Shop = $Product->getShop();
        $asset_folder = $Shop->getAssetFolder();

        $EditingReview = null;

        if (\is_null($buyer_review_id)) {
            $limit = $this->eccubeConfig['malldevel_buyer_review_limit'];
            if (count($this->buyerReviewRepository->findBy(['Product' => $Product])) >= $this->eccubeConfig['malldevel_buyer_review_limit']) {
                $this->addError(trans("malldevel.admin.product.buyer_review.review_count_limit_reached", ['%count%' => $limit]), "admin");
                return $this->redirectToRoute("malldevel_admin_buyer_review_index", ['id' => $product_id]);
            }
            $BuyerReview = new BuyerReview;
        } else {
            $BuyerReview = $this->buyerReviewRepository->find($buyer_review_id);
            if (!$BuyerReview) {
                throw new NotFoundHttpException();
            }
            $EditingReview = $BuyerReview;
        }
        $BuyerReview->setProduct($Product);

        $builder = $this->formFactory
            ->createBuilder(BuyerReviewType::class);

        $form = $builder->getForm();
        $form->handleRequest($request);

        // if ($request->getMethod() === "POST") {
        if ($form->isSubmitted() && $form->isValid()) {

            $title = $form['title']->getData();
            $content = $form['content']->getData();
            
            $image = $form['image']->getData();
            $delete_images = $form['delete_images']->getData();

            $BuyerReview->setTitle($title);
            $BuyerReview->setContent($content);

            if ($delete_images && count($delete_images)) {
                $fs = new Filesystem();

                
                foreach($delete_images as $delete_image) {
                    $delete_image = \str_replace($asset_folder . '/' , '', $delete_image);
                    if (\is_file($this->eccubeConfig['eccube_save_image_dir'] . '/' . $asset_folder . '/' . $delete_image)) {
                        $fs->remove($this->eccubeConfig['eccube_save_image_dir'] . '/' . $asset_folder . '/' . $delete_image);
                    } else if (\is_file($this->eccubeConfig['eccube_temp_image_dir'] . '/' . $delete_image)) {
                        $fs->remove($this->eccubeConfig['eccube_temp_image_dir'] . '/' . $delete_image);
                    }
                    if ($BuyerReview->getImage() == $delete_image) {
                        $BuyerReview->setImage(null);
                    }
                }
            }

            if ($image) {
                $old_image = $BuyerReview->getImage();
                if (\is_file($this->eccubeConfig['eccube_save_image_dir'] . '/' . $asset_folder . '/' . $old_image)) {
                    $fs = new FileSystem();
                    $fs->remove($this->eccubeConfig['eccube_save_image_dir'] . '/' . $asset_folder . '/' . $old_image);
                }
                if (\is_file($this->eccubeConfig['eccube_temp_image_dir'] . '/' . $image)) {
                    $file = new File($this->eccubeConfig['eccube_temp_image_dir'] . '/' . $image);
                    $file->move($this->eccubeConfig['eccube_save_image_dir'] . '/' . $asset_folder);
                    $BuyerReview->setImage($image);
                }
            }

            $this->entityManager->persist($BuyerReview);
            $this->entityManager->flush();

            // return $this->json(['success'   =>  true]);
            $this->addSuccess("malldevel.admin.add_success", "admin");
            return $this->redirectToRoute("malldevel_admin_buyer_review_index", ['id' => $product_id]);
        }
        $BuyerReviews = $this->buyerReviewRepository->findBy(['Product' => $Product]);
        $form = $form->createView();

        return compact("Product", "BuyerReviews", "form", "EditingReview");
    }
    /**
     * @Route("/%eccube_admin_route%/product/{product_id}/buyer_review/delete/{buyer_review_id}", requirements={"product_id" = "\d+", "buyer_review_id" = "\d+"}, name="malldevel_admin_buyer_review_delete")
     */
    public function delete(Request $request, $product_id, $buyer_review_id)
    {
        $Product = $this->productRepository->find($product_id);
        if (!$Product) 
            throw new NotFoundHttpException();
        $BuyerReview = $this->buyerReviewRepository->find($buyer_review_id);
        if (!$BuyerReview) {
            throw new NotFoundHttpException();
        }

        $Shop = $Product->getShop();
        $asset_folder = $Shop->getAssetFolder();

        $image = $BuyerReview->getImage();
        if ($image && \is_file($this->eccubeConfig['eccube_save_image_dir'] . '/' . $asset_folder . '/' . $image)) {
            $fs = new Filesystem();
            $fs->remove($this->eccubeConfig['eccube_save_image_dir'] . '/' .  $asset_folder . '/' . $image);
        }
        $this->entityManager->remove($BuyerReview);
        $this->entityManager->flush();
        $this->addSuccess("malldevel.admin.delete_success", "admin");
        return $this->redirectToRoute("malldevel_admin_buyer_review_index", ['id' => $product_id]);
    }
    /**
     * @Route("/%eccube_admin_route%/product/{product_id}/buyer_review/get/{buyer_review_id}", requirements={"product_id" = "\d+", "buyer_review_id" = "\d+"}, name="malldevel_admin_buyer_review_get")
     */
    public function getBuyerReview(Request $request, $product_id, $buyer_review_id)
    {
        $Product = $this->productRepository->find($product_id);
        if (!$Product) 
            throw new NotFoundHttpException();
        $BuyerReview = $this->buyerReviewRepository->find($buyer_review_id);
        if (!$BuyerReview) {
            throw new NotFoundHttpException();
        }

        return $this->json([
            'success'   =>  true,
            'image' =>  $BuyerReview->getImagePath(),
            'title' =>  $BuyerReview->getTitle(),
            'content'=> $BuyerReview->getContent(),
        ]);
    }
    
}
