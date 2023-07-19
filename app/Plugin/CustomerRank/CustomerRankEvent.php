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

namespace Plugin\CustomerRank;

use Eccube\Event\EccubeEvents;
use Eccube\Event\RenderEvent;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\ProductClassRepository;
use Eccube\Service\CartService;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Plugin\CustomerRank\Repository\CustomerPriceRepository;
use Plugin\CustomerRank\Entity\CustomerPrice;
use Plugin\CustomerRank\Entity\CustomerRankConfig;
use Plugin\CustomerRank\Service\CustomerRankService;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;

class CustomerRankEvent implements EventSubscriberInterface
{

    private $entityManager;

    private $customerRepository;

    private $productClassRepository;

    private $cartService;

    private $customerRankRepository;

    private $customerPriceRepository;

    private $customerRankService;

    /**
     * CustomerRankController constructor.
     * @param CustomerRankRepository $customerRankRepository
     */
    public function __construct(
            EntityManagerInterface $entityManager,
            CustomerRepository $customerRepository,
            ProductClassRepository $productClassRepository,
            CartService $cartService,
            CustomerRankRepository $customerRankRepository,
            CustomerPriceRepository $customerPriceRepository,
            CustomerRankService $customerRankService
            )
    {
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->productClassRepository = $productClassRepository;
        $this->cartService = $cartService;
        $this->customerRankRepository = $customerRankRepository;
        $this->customerPriceRepository = $customerPriceRepository;
        $this->customerRankService = $customerRankService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            '@admin/Customer/index.twig' => 'onTemplateAdminCustomer',
            '@admin/Customer/edit.twig' => 'onTemplateAdminCustomerEdit',
            '@admin/Product/product.twig' => 'onTemplateAdminProductEdit',
            EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE => 'hookAdminProductEditComplete',
            EccubeEvents::ADMIN_PRODUCT_COPY_COMPLETE => 'hookAdminProductCopyComplete',
            EccubeEvents::ADMIN_PRODUCT_CSV_EXPORT => 'hookAdminProductCsvExport',
            '@admin/Product/product_class.twig' => 'onTemplateAdminProductClassEdit',
            '@admin/Order/index.twig' => 'onTemplateAdminOrder',
            '@admin/Order/edit.twig' => 'onTemplateAdminOrderEdit',
            EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_PRODUCT_COMPLETE => 'hookAdminOrderEditSearchProductComplete',
            EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE => 'hookAdminOrderEditIndexComplete',
            'Product/list.twig' => 'onTemplateProductList',
            'Product/detail.twig' => 'onTemplateProductDetail',
            'Cart/index.twig' => 'onTemplateCart',
            EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE => 'hookFrontEntryIndexComplete',
            EccubeEvents::FRONT_SHOPPING_COMPLETE_INITIALIZE => 'hookFrontShoppingCompleteInitialize',
            'Mypage/history.twig' => 'onTemplateMypageHistory',
            'Mypage/favorite.twig' => 'onTemplateMypageFavorite',
            'csvimportproductext.admin.product.csv.import.product.descriptions' => 'hookAdminProductCsvImportProductDescriptions',
            'csvimportproductext.admin.product.csv.import.product.check'=> 'hookAdminProductCsvImportProductCheck',
            'csvimportproductext.admin.product.csv.import.product.process' => 'hookAdminProductCsvImportProductProcess',
            '@MailMagazine4/admin/index.twig' => 'onTemplateAdminCustomer',
            '@MailMagazine4/admin/history_condition.twig' => 'onTemplateMailmagazineHistoryCondition',
        ];
    }

    public function onTemplateAdminCustomer(TemplateEvent $event)
    {
        $twig = '@CustomerRank/admin/Customer/customer_index.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateAdminCustomerEdit(TemplateEvent $event)
    {
        $twig = '@CustomerRank/admin/Customer/customer_rank.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateAdminProductEdit(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        $CustomerRanks = $this->customerRankRepository->getList();
        $parameters['CustomerRanks'] = $CustomerRanks;
        $event->setParameters($parameters);

        $twig = '@CustomerRank/admin/Product/customer_price.twig';
        $event->addSnippet($twig);
    }

    public function hookAdminProductEditComplete(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $form = $event->getArgument('form');

        $has_class = $Product->hasProductClass();
        if(!$has_class){
            $CustomerRanks = $this->customerRankRepository->getList();
            $ProductClass = $form['class']->getData();

            foreach($CustomerRanks as $CustomerRank){
                if($form['class']->has('customer_price_'. $CustomerRank->getId())){

                    $CustomerPrice = $this->customerPriceRepository->findOneBy(['ProductClass' => $ProductClass, 'CustomerRank' => $CustomerRank]);
                    if(!$CustomerPrice){
                        $CustomerPrice =  new CustomerPrice();
                        $CustomerPrice->setProductClass($ProductClass);
                        $CustomerPrice->setCustomerRank($CustomerRank);
                    }

                    $CustomerPrice->setPrice($form['class']->get('customer_price_'. $CustomerRank->getId())->getData());

                    $this->entityManager->persist($CustomerPrice);
                }
            }
            $this->entityManager->flush();
        }
    }

    public function hookAdminProductCopyComplete(EventArgs $event)
    {
        $Product = $event->getArgument('Product');
        $CopyProduct = $event->getArgument('CopyProduct');
        $orgProductClasses = $Product->getProductClasses();

        $CustomerRanks = $this->customerRankRepository->getList();

        foreach($CustomerRanks as $CustomerRank){
            foreach ($orgProductClasses as $ProductClass) {
                $CopyProductClass = $this->productClassRepository->findOneBy(['Product'=> $CopyProduct, 'ClassCategory1' => $ProductClass->getClassCategory1(), 'ClassCategory2' => $ProductClass->getClassCategory2()]);
                $orgCustomerPrice = $this->customerPriceRepository->findOneBy(['ProductClass' => $ProductClass, 'CustomerRank' => $CustomerRank]);
                if($CopyProductClass){
                    $CustomerPrice = new CustomerPrice();
                    $CustomerPrice->setProductClass($CopyProductClass);
                    $CustomerPrice->setCustomerRank($CustomerRank);
                    if($orgCustomerPrice){
                        $CustomerPrice->setPrice($orgCustomerPrice->getPrice());
                    }

                    $this->entityManager->persist($CustomerPrice);
                }
            }
        }
        $this->entityManager->flush();
    }

    public function hookAdminProductCsvExport(EventArgs $event)
    {
        $ExportCsvRow = $event->getArgument('ExportCsvRow');
        if ($ExportCsvRow->isDataNull()) {
            $csvService = $event->getArgument('csvService');
            $ProductClass = $event->getArgument('ProductClass');
            $Csv = $event->getArgument('Csv');

            $csvEntityName = str_replace('\\\\', '\\', $Csv->getEntityName());
            if($csvEntityName == 'Plugin\CustomerRank\Entity\CustomerPrice'){
                $customer_rank_id = ltrim($Csv->getFieldName(), 'customerrank_price_');
                if(is_numeric($customer_rank_id)){
                    $CustomerRank = $this->customerRankRepository->find($customer_rank_id);
                    if(!is_null($CustomerRank)){
                        $CustomerPrice = $this->customerPriceRepository->findOneBy(['ProductClass' => $ProductClass, 'CustomerRank' => $CustomerRank]);
                        if(!is_null($CustomerPrice)){
                            $ExportCsvRow->setData($CustomerPrice->getPrice());
                        }
                    }
                }
            }
        }
    }

    public function onTemplateAdminProductClassEdit(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        $CustomerRanks = $this->customerRankRepository->getList();
        $parameters['CustomerRanks'] = $CustomerRanks;
        $event->setParameters($parameters);

        $twig = '@CustomerRank/admin/Product/customer_price_class.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateAdminOrder(TemplateEvent $event)
    {
        $twig = '@CustomerRank/admin/Order/order_index.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateAdminOrderEdit(TemplateEvent $event)
    {
        $source = $event->getSource();

        if(preg_match("/\\$\('\#admin\_search\_product\_id'\)\.val\(\),/",$source, $result)){
            $search = $result[0];
            $replace = $search . "\n'customer_id':$('#order_CustomerId').text(),";
            $source = str_replace($search, $replace, $source);
        }

        $event->setSource($source);

        $twig = '@CustomerRank/admin/Order/customer_rank.twig';
        $event->addSnippet($twig);
    }

    public function hookAdminOrderEditSearchProductComplete(EventArgs $event)
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $pagination = $event->getArgument('pagination');

        if ('POST' === $request->getMethod()) {
            $customer_id = $request->get('customer_id');
            $session->set('eccube.cusstomerrank.order.product.search', $customer_id);
        }else{
            $customer_id = $session->get('eccube.cusstomerrank.order.product.search');
        }

        if($customer_id > 0){
            $Customer = $this->customerRepository->find($customer_id);
            $CustomerRank = $Customer->getCustomerRank();
            if(!is_null($CustomerRank)){
                foreach($pagination as $Product){
                    foreach($Product->getProductClasses() as $ProductClass){
                        $ProductClass->setPrice02($this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank, $ProductClass));
                    }
                }
            }
        }
    }

    public function hookAdminOrderEditIndexComplete(EventArgs $event)
    {
        $Customer = $event->getArgument('Customer');
        if(!is_null($Customer))$this->customerRankService->checkRank($Customer);
    }

    public function hookFrontEntryIndexComplete(EventArgs $event)
    {
        $Customer = $event->getArgument('Customer');

        $CustomerRank = $this->customerRankRepository->findOneBy(['initial_flg' => true]);
        if(!is_null($CustomerRank)){
            $Customer->setCustomerRank($CustomerRank);
            $this->entityManager->persist($Customer);
            $this->entityManager->flush($Customer);
        }
    }

    public function onTemplateProductDetail(TemplateEvent $event)
    {
        $this->rankCheckForFront();

        $parameters = $event->getParameters();
        $Product = $parameters['Product'];

        $class_categories = [];
        $loginDisp = $this->customerRankService->getConfig('login_disp');
        $initFlag = $loginDisp == CustomerRankConfig::DISABLED ? true : false;
        $CustomerRank = $this->customerRankService->getCustomerRank($initFlag);

        if(!is_null($CustomerRank)){
            $class_categories[$Product->getId()]['__unselected']['#'] = [
                'customer_rank_price' => '',
                'customer_rank_price_inc_tax' => '',
            ];
            foreach($Product->getProductClasses() as $ProductClass){
                if(!$ProductClass->isVisible())continue;
                // ver.1.1.0未満のバージョン用に残しておく
                $ProductClass->setCustomerRankPrice($this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank, $ProductClass));
                $ProductClass->setCustomerRankPriceIncTax($this->customerPriceRepository->getCustomerPriceIncTaxByProductClass($CustomerRank, $ProductClass));

                $ClassCategory1 = $ProductClass->getClassCategory1();
                $ClassCategory2 = $ProductClass->getClassCategory2();
                if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                    continue;
                }
                $class_category_id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
                $class_category_id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';
                $class_category_name2 = $ClassCategory2 ? $ClassCategory2->getName().($ProductClass->getStockFind() ? '' : trans('product.text.out_of_stock')) : '';

                $class_categories[$Product->getId()][$class_category_id1]['#'] = [
                    'customer_rank_price' => '',
                    'customer_rank_price_inc_tax' => '',
                ];
                $class_categories[$Product->getId()][$class_category_id1]['#'.$class_category_id2] = [
                    'customer_rank_price' => number_format($ProductClass->getCustomerRankPrice($CustomerRank->getId())),
                    'customer_rank_price_inc_tax' => number_format($ProductClass->getCustomerRankPriceIncTax($CustomerRank->getId())),
                ];
            }
        }

        $parameters['CustomerRank'] = $CustomerRank;
        $parameters['CustomerPrices'] = $class_categories;
        $event->setParameters($parameters);

        $twig = '@CustomerRank/default/Product/product_js.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateProductList(TemplateEvent $event)
    {
        $this->rankCheckForFront();

        $parameters = $event->getParameters();
        $pagination = $parameters['pagination'];

        $class_categories = [];
        $loginDisp = $this->customerRankService->getConfig('login_disp');
        $initFlag = $loginDisp == CustomerRankConfig::DISABLED ? true : false;
        $CustomerRank = $this->customerRankService->getCustomerRank($initFlag);

        if(!is_null($CustomerRank)){
            foreach($pagination as $Product){
                $class_categories[$Product->getId()]['__unselected']['#'] = [
                    'customer_rank_price' => '',
                    'customer_rank_price_inc_tax' => '',
                ];
                foreach($Product->getProductClasses() as $ProductClass){
                    if(!$ProductClass->isVisible())continue;
                    // ver.1.1.0未満のバージョン用に残しておく
                    $ProductClass->setCustomerRankPrice($this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank, $ProductClass));
                    $ProductClass->setCustomerRankPriceIncTax($this->customerPriceRepository->getCustomerPriceIncTaxByProductClass($CustomerRank, $ProductClass));

                    $ClassCategory1 = $ProductClass->getClassCategory1();
                    $ClassCategory2 = $ProductClass->getClassCategory2();
                    if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                        continue;
                    }
                    $class_category_id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
                    $class_category_id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';
                    $class_category_name2 = $ClassCategory2 ? $ClassCategory2->getName().($ProductClass->getStockFind() ? '' : trans('product.text.out_of_stock')) : '';

                    $class_categories[$Product->getId()][$class_category_id1]['#'] = [
                        'customer_rank_price' => '',
                        'customer_rank_price_inc_tax' => '',
                    ];
                    $class_categories[$Product->getId()][$class_category_id1]['#'.$class_category_id2] = [
                        'customer_rank_price' => number_format($ProductClass->getCustomerRankPrice($CustomerRank->getId())),
                        'customer_rank_price_inc_tax' => number_format($ProductClass->getCustomerRankPriceIncTax($CustomerRank->getId())),
                    ];
                }
            }
        }

        $parameters['CustomerRank'] = $CustomerRank;
        $parameters['CustomerPrices'] = $class_categories;
        $event->setParameters($parameters);

        $twig = '@CustomerRank/default/Product/product_js.twig';
        $event->addSnippet($twig);
    }

    public function onTemplateCart(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        $least = $parameters['least'];
        $isDeliveryFree = $parameters['is_delivery_free'];

        $CustomerRank = $this->customerRankService->getCustomerRank(false);
        if(!is_null($CustomerRank)){
            $Carts = $this->cartService->getCarts();
            if (strlen($CustomerRank->getDeliveryFreeCondition()) > 0) {
                foreach ($Carts as $Cart) {
                    $isDeliveryFree[$Cart->getCartKey()] = false;
                    if ($CustomerRank->getDeliveryFreeCondition() <= $Cart->getTotalPrice()) {
                        $isDeliveryFree[$Cart->getCartKey()] = true;
                    } else {
                        $least[$Cart->getCartKey()] = $CustomerRank->getDeliveryFreeCondition() - $Cart->getTotalPrice();
                    }
                }
            }
        }

        $parameters['least'] = $least;
        $parameters['is_delivery_free'] = $isDeliveryFree;
        $event->setParameters($parameters);
    }

    public function hookFrontShoppingCompleteInitialize(EventArgs $event)
    {
        $Order = $event->getArgument('Order');
        $Customer = $Order->getCustomer();
        if(!is_null($Customer))$this->customerRankService->checkRank($Customer);
    }

    public function onTemplateMypageHistory(TemplateEvent $event)
    {
        $this->rankCheckForFront();

        $parameters = $event->getParameters();
        $Order = $parameters['Order'];

        $CustomerRank = $this->customerRankService->getCustomerRank(true);
        foreach($Order->getOrderItems() as $OrderItem){
            $ProductClass = $OrderItem->getProductClass();
            if(!is_null($ProductClass)){
                if(!$ProductClass->isVisible())continue;
                $ProductClass->setCustomerRankPrice($this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank, $ProductClass));
                $ProductClass->setCustomerRankPriceIncTax($this->customerPriceRepository->getCustomerPriceIncTaxByProductClass($CustomerRank, $ProductClass));
            }
        }

        $event->setParameters($parameters);
        $source = $event->getSource();
        $source = preg_replace("/price02/","customer_rank_price",$source);
        $event->setSource($source);
    }

    public function onTemplateMypageFavorite(TemplateEvent $event)
    {
        $this->rankCheckForFront();

        $parameters = $event->getParameters();
        $pagination = $parameters['pagination'];
        $CustomerRank = $this->customerRankService->getCustomerRank(true);
        foreach($pagination as $FavoriteItem){
            $Product = $FavoriteItem->getProduct();
            foreach($Product->getProductClasses() as $ProductClass){
                if(!is_null($ProductClass)){
                    if(!$ProductClass->isVisible())continue;
                    $ProductClass->setCustomerRankPrice($this->customerPriceRepository->getCustomerPriceByProductClass($CustomerRank, $ProductClass));
                    $ProductClass->setCustomerRankPriceIncTax($this->customerPriceRepository->getCustomerPriceIncTaxByProductClass($CustomerRank, $ProductClass));
                }
            }
        }

        $parameters['CustomerRank'] = $CustomerRank;
        $event->setParameters($parameters);
    }

    public function hookAdminProductCsvImportProductDescriptions(EventArgs $event)
    {
        $header = $event->getArgument('header');
        $key = $event->getArgument('key');
        $CustomerRanks = $this->customerRankRepository->findAll();
        foreach($CustomerRanks as $CustomerRank){
            if($key == $CustomerRank->getName() . trans('customerrank.common.customer_price')){
                $header['description'] = trans('customerrank.admin.product.product_csv.customer_price_description');
                $header['required'] = false;
            }
        }

        $event->setArgument('header',$header);
    }

    public function hookAdminProductCsvImportProductCheck(EventArgs $event)
    {
        $row = $event->getArgument('row');
        $data = $event->getArgument('data');
        $errors = $event->getArgument('errors');

        $CustomerRanks = $this->customerRankRepository->findAll();
        foreach($CustomerRanks as $CustomerRank){
            if(isset($row[$CustomerRank->getName(). trans('customerrank.common.customer_price')])){
                if($row[$CustomerRank->getName(). trans('customerrank.common.customer_price')] !== '' && !is_numeric($row[$CustomerRank->getName(). trans('customerrank.common.customer_price')])){
                    $message = trans('admin.common.csv_invalid_greater_than_zero', [
                        '%line%' => $data->key() + 1,
                        '%name%' => $CustomerRank->getName(). trans('customerrank.common.customer_price'),
                    ]);
                    $errors[] = $message;
                }
            }
        }

        $event->setArgument('errors',$errors);
    }

    public function hookAdminProductCsvImportProductProcess(EventArgs $event)
    {
        $row = $event->getArgument('row');
        $data = $event->getArgument('data');
        $ProductClass = $event->getArgument('ProductClass');
        $Product = $ProductClass->getProduct();

        $CustomerRanks = $this->customerRankRepository->findAll();
        foreach($CustomerRanks as $CustomerRank){
            if(isset($row[$CustomerRank->getName() . trans('customerrank.common.customer_price')])){
                $plgPrice = $this->customerPriceRepository->findOneBy(['ProductClass' => $ProductClass, 'CustomerRank' => $CustomerRank]);
                if($row[$CustomerRank->getName() . trans('customerrank.common.customer_price')] != ''){
                    if(is_null($plgPrice)){
                        $plgPrice = new CustomerPrice();
                        $plgPrice->setProductClass($ProductClass);
                        $plgPrice->setCustomerRank($CustomerRank);
                    }
                    $plgPrice->setPrice($row[$CustomerRank->getName() . trans('customerrank.common.customer_price')]);
                    $this->entityManager->persist($plgPrice);
                }else{
                    if(isset($plgPrice))$this->entityManager->remove($plgPrice);
                }
            }
            if(isset($plgPrice))unset($plgPrice);
        }
    }

    public function onTemplateMailmagazineHistoryCondition(TemplateEvent $event)
    {
        $parameters = $event->getParameters();

        $searchData = $parameters['search_data'];

        if(isset($searchData['customer_rank']) && is_array($searchData['customer_rank'])){
            $val = [];
            foreach($searchData['customer_rank'] as $id){
                $CustomerRank = $this->customerRankRepository->find($id);
                if($CustomerRank){
                    $val[] = $CustomerRank->getName();
                }
            }
            if(count($val) != 0){
                $searchData['customer_rank'] = implode(', ', $val);
            }else{
                $searchData['customer_rank'] = null;
            }
        }

        $parameters['search_data'] = $searchData;
        $event->setParameters($parameters);

        $twig = '@CustomerRank/admin/mailmagazine_history_condition_add.twig';
        $event->addSnippet($twig);

    }

    public function rankCheckForFront()
    {
        $Customer = $this->customerRankService->getLoginCustomer();
        if(!is_null($Customer))$this->customerRankService->checkRank($Customer);
    }
}