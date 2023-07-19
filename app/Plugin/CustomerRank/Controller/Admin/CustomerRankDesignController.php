<?php
/*
* Plugin Name : CustomerRank
*
* Copyright (C) BraTech Co., Ltd. All Rights Reserved.
* http://www.bratech.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\CustomerRank\Controller\Admin;

use Eccube\Util\StringUtil;
use Plugin\CustomerRank\Form\Type\Admin\CustomerRankDesignType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class CustomerRankDesignController extends \Eccube\Controller\AbstractController
{
    /**
     * @Route("/%eccube_admin_route%/content/customer_rank/list", name="admin_content_customerrank_list")
     * @Template("@CustomerRank/admin/Content/list.twig")
     */
    public function listDesign(Request $request, Environment $twig, FileSystem $fs)
    {
        $form = $this->formFactory
                ->createBuilder(CustomerRankDesignType::class)
                ->getForm();

        $html = $twig->getLoader()
                ->getSourceContext('Product/customer_price_list.twig')
                ->getCode();

        $form->get('list_html')->setData($html);

        if ('POST' === $request->getMethod()) {
            switch($request->get('mode')){
                case 'regist':
                    $form->handleRequest($request);

                    $dir = sprintf('%s/app/template/%s/Product',
                        $this->getParameter('kernel.project_dir'),
                        $this->getParameter('eccube.theme'));

                    $file = $dir.'/customer_price_list.twig';

                    $source = $form->get('list_html')->getData();
                    $source = StringUtil::convertLineFeed($source);
                    $fs->dumpFile($file, $source);

                    // twigキャッシュの削除
                    $cacheDir = $this->getParameter('kernel.cache_dir').'/twig';
                    $fs->remove($cacheDir);

                    $this->addSuccess('admin.content.customerrank.save.complete', 'admin');
                    break;
                case 'init':
                    $requestData = $request->get('customer_rank_design');
                    $content = file_get_contents($this->getParameter('plugin_realdir') . '/CustomerRank/Resource/template/default/Product/customer_price_list.twig');
                    $requestData['list_html'] = $content;
                    $request->request->set('customer_rank_design',$requestData);
                    $form->handleRequest($request);
                    break;
                default:
                    break;
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/customer_rank/detail", name="admin_content_customerrank_detail")
     * @Template("@CustomerRank/admin/Content/detail.twig")
     */
    public function detailDesign(Request $request, Environment $twig, FileSystem $fs)
    {
        $form = $this->formFactory
                ->createBuilder(CustomerRankDesignType::class)
                ->getForm();

        $html = $twig->getLoader()
                ->getSourceContext('Product/customer_price_detail.twig')
                ->getCode();

        $form->get('detail_html')->setData($html);

        if ('POST' === $request->getMethod()) {
            switch($request->get('mode')){
                case 'regist':
                    $form->handleRequest($request);

                    $dir = sprintf('%s/app/template/%s/Product',
                        $this->getParameter('kernel.project_dir'),
                        $this->getParameter('eccube.theme'));

                    $file = $dir.'/customer_price_detail.twig';

                    $source = $form->get('detail_html')->getData();
                    $source = StringUtil::convertLineFeed($source);
                    $fs->dumpFile($file, $source);

                    // twigキャッシュの削除
                    $cacheDir = $this->getParameter('kernel.cache_dir').'/twig';
                    $fs->remove($cacheDir);

                    $this->addSuccess('admin.content.customerrank.save.complete', 'admin');
                    break;
                case 'init':
                    $requestData = $request->get('customer_rank_design');
                    $content = file_get_contents($this->getParameter('plugin_realdir') . '/CustomerRank/Resource/template/default/Product/customer_price_detail.twig');
                    $requestData['detail_html'] = $content;
                    $request->request->set('customer_rank_design',$requestData);
                    $form->handleRequest($request);
                    break;
                default:
                    break;
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}