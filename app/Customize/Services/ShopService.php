<?php


namespace Customize\Services;

require_once "IgoMaster/Igo.php";

use Customize\Services\IgoMaster\Igo;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Filesystem\Filesystem;
use Eccube\Repository\MemberRepository;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Product;
use Eccube\Entity\Member;
use Eccube\Entity\Payment;
use Eccube\Entity\PaymentOption;
use Eccube\Entity\DeliveryFee;
use Eccube\Entity\Master\Work;
use Eccube\Entity\Master\Authority;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Delivery;
use Customize\Entity\EAuthority;
use Customize\Entity\Shop;
use Customize\Entity\ShopCategory;
use Customize\Entity\Katakana;
use Customize\Entity\Apply;
use Customize\Entity\Master\ShopStatus;
use Customize\Entity\ShopSeries;
use Customize\Entity\ShopPhoto;
use Customize\Entity\ShopIdentityDoc;
use Customize\Repository\ApplyRepository;
use Customize\Util\RStrUtil;

// use Customize\Services\IgoMaster\Igo;

class ShopService {

    protected $container;
    protected $entityManager;
    protected $eccubeConfig;
    // protected $slugger;
    protected $kataRepository;

    public function __construct(
        ContainerInterface $container,
        EccubeConfig $eccubeConfig
    ) {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        $this->eccubeConfig = $eccubeConfig;
        // $this->slugger = $container->get(SluggerInterface::class);
        $this->kataRepository = $this->entityManager->getRepository(Katakana::class);
        
    }

    public function getKata(Shop $Shop) {
        $name = $Shop->getName();

        $kana = $Shop->getKana();
        if ($kana) {
            $kata_first = \mb_substr($kana, 0, 1, "UTF-8");
            $ka = $this->getKataCh($kata_first);
            return $this->kataRepository->getByChar($ka);
        }
        if($name) {
            $firstChar = \mb_substr($name, 0, 1, "UTF-8");

            
            $igo = new Igo( \dirname(__FILE__) . "/IgoMaster/ipadic", "UTF-8");
            $result = $igo->parse($firstChar);
            $str = "";
            foreach($result as $value) {
                $feature = explode(",", $value->feature);
                $str .= isset($feature[7]) ? $feature[7] : $value->surface;
            }
            $kata = mb_convert_kana($str, "C", "utf-8");
            $kata_first = \mb_substr($kata, 0, 1, "UTF-8");

            $ka = $this->getKataCh($kata_first);
            return $this->kataRepository->getByChar($ka);
        }
        return null;
    }

    public function getKataCh($kata_first)
    {
        $kana_table = [
            'ア'    =>  ['ア', 'イ', 'ウ', 'エ', 'オ'],
            'カ'    =>  ['カ', 'キ', 'ク', 'ケ', 'コ', 'ガ', 'ギ', 'グ', 'ゲ', 'ゴ'],
            'サ'    =>  ['サ', 'シ', 'ス', 'セ', 'ソ', 'ザ', 'ジ', 'ズ', 'ゼ', 'ゾ'],
            'タ'    =>  ['タ', 'チ', 'ツ', 'テ', 'ト', 'ダ', 'ヂ', 'ヅ', 'デ', 'ド'],
            'ナ'    =>  ['ナ', 'ニ', 'ヌ', 'ネ', 'ノ'],
            'ハ'    =>  ['ハ', 'ヒ', 'フ', 'ヘ', 'ホ', 'バ', 'ビ', 'ブ', 'ベ', 'ボ'],
            'マ'    =>  ['マ', 'ミ', 'ム', 'メ', 'モ'],
            'ヤ'    =>  ['ヤ', 'ユ', 'ヨ'],
            'ラ・ワ' =>  ['ラ', 'リ', 'ル', 'レ', 'ロ', 'ワ', 'ヲ'],
        ];
        $ka = "";
        foreach ($kana_table as $k => $v) {
            if (\in_array($kata_first, $v)) {
                $ka = $k;
                break;
            }
        }
        if (!$ka) {
            $ka = 'A～Z・数字';
        }
        return $ka;
    }

    

    public function createShopFromApply(Apply $Apply)
    {
        // BOC create a shop
        $Shop = new Shop;
        $Shop->copyFromApply($Apply);
        $HiddenStatus = $this->entityManager->getRepository(ShopStatus::class)->find(ShopStatus::DISPLAY_HIDE);
        $Shop->setStatus($HiddenStatus);
        $Katakana = $this->getKata($Shop);
        $Shop->setKatakana($Katakana);
        $this->entityManager->persist($Shop);
        $this->entityManager->flush();
        // EOC create a shop
        return $Shop;
    }

    public function createDefaultDeliveries(Shop $Shop) 
    {
        $delivery_repo = $this->entityManager->getRepository(Delivery::class);
        $delivery_fee_repo = $this->entityManager->getRepository(DeliveryFee::class);
        $payment_repo = $this->entityManager->getRepository(Payment::class);
        $pref_repo = $this->entityManager->getRepository(Pref::class);
        $sale_types = $this->entityManager->getRepository(SaleType::class)->findAll();

        $count = 0;

        foreach($sale_types as $SaleType) {
            $existing_delivery = $delivery_repo->findOneBy(['SaleType' => $SaleType, 'Shop' => $Shop]);
            if ($existing_delivery) continue;
            $Delivery = $delivery_repo->findOneBy([], ['sort_no' => 'DESC']);

            $sortNo = 1;
            if ($Delivery) {
                $sortNo = $Delivery->getSortNo() + 1;
            }

            $Delivery = new Delivery();
            $Delivery->setSortNo($sortNo)
                    ->setVisible(true)
                    ->setSaleType($SaleType)
                    ->setShop($Shop)
                    ->setName("通常配送")
                    ->setServiceName("通常配送");
            $Prefs = $pref_repo->findAll();

            foreach($Prefs as $Pref) {
                $DeliveryFee = new DeliveryFee;
                $DeliveryFee->setPref($Pref)
                            ->setDelivery($Delivery)
                            ->setFee(0);
                $Delivery->addDeliveryFee($DeliveryFee);
            }

            // sort
            $DeliveryFees = $Delivery->getDeliveryFees();
            $DeliveryFeesIndex = [];
            foreach ($DeliveryFees as $DeliveryFee) {
                $Delivery->removeDeliveryFee($DeliveryFee);
                $DeliveryFeesIndex[$DeliveryFee->getPref()->getId()] = $DeliveryFee;
            }
            ksort($DeliveryFeesIndex);
            foreach ($DeliveryFeesIndex as $timeId => $DeliveryFee) {
                $Delivery->addDeliveryFee($DeliveryFee);
            }

            $this->entityManager->persist($Delivery);
            $this->entityManager->flush();

            $Payments = $payment_repo->findBy(['visible' => true]);

            foreach($Payments as $Payment) {
                $PaymentOption = new PaymentOption();
                $PaymentOption
                    ->setPaymentId($Payment->getId())
                    ->setPayment($Payment)
                    ->setDeliveryId($Delivery->getId())
                    ->setDelivery($Delivery);
                $Delivery->addPaymentOption($PaymentOption);
                $this->entityManager->persist($Delivery);
            }
            $this->entityManager->persist($Delivery);

            $this->entityManager->flush();

            $count++;
        }
        return $count;
    }

    public function saveLogo($logo, Shop $Shop){
        if( $logo ) {
            $temp_path = $this->eccubeConfig['eccube_temp_image_dir'] . '/' . $logo;
            if ( \is_file($temp_path) ) {
                $logo = new File( $temp_path );
                $folder = $this->eccubeConfig['eccube_save_image_dir'] . '/' . $Shop->getAssetFolder();
                if (!\is_dir($folder) ) {
                    \mkdir($folder);
                }
                if( $Shop->getLogo() && \is_file($folder . '/' . $Shop->getLogo())) {
                    unlink( $folder . '/' . $Shop->getLogo() );
                }
                $logo->move( $folder );
            }
        }
    }

    public function saveAsset($file, Shop $Shop = null) {
        if( $file ) {
            $folder = $this->eccubeConfig['eccube_save_image_dir'] . '/' . (!empty($Shop) ? $Shop->getAssetfolder() : 'admin' );
            if (!\is_dir($folder) ) {
                \mkdir($folder);
            }

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $new_file_name = $originalFilename . '-' . \uniqid() . '_' . date('Y_m_d_H_i_s') . '.' . $file->getClientOriginalExtension();

            $file->move( $folder, $new_file_name );
            return $new_file_name;
        }
        return false;
    }

    public function saveAssetFromTemp($file_path, Shop $Shop) { 
        if ($file_path) {
            $temp_path = $this->eccubeConfig['eccube_temp_image_dir'] . '/' . $file_path;
            if (\is_file($temp_path)) {
                $file = new File($temp_path);
                $folder = $this->eccubeConfig['eccube_save_image_dir'] . '/' . $Shop->getAssetFolder();
                if (!\is_dir($folder) ) {
                    \mkdir($folder);
                }
                $file->move($folder);
            }
        }
    }

    public function deleteAssetFromShop($delete_images, Shop $Shop) {
        if (count($delete_images)) {
            $temp_dir = $this->eccubeConfig['eccube_temp_image_dir'];
            $asset_dir = $this->eccubeConfig['eccube_save_image_dir'] . '/' . $Shop->getAssetFolder();
            $fs = new Filesystem();
            $shopPhotoRepo = $this->entityManager->getRepository(ShopPhoto::class);
            $shopIdDocRepo = $this->entityManager->getRepository(ShopIdentityDoc::class);
            foreach ($delete_images as $delete_image) {
                if (\is_file($temp_dir . '/' . $delete_image)) {
                    $fs->remove($temp_dir . '/' . $delete_image);
                }
                if (\is_file($asset_dir . '/' . $delete_image)) {
                    $fs->remove($asset_dir . '/' . $delete_image);
                }
                $shop_asset_dir = $Shop->getAssetFolder();
                if (RStrUtil::startsWith($delete_image, $shop_asset_dir . '/')) {
                    $delete_image = \str_replace($shop_asset_dir . '/', '',  $delete_image);
                }
                
                $shopPhotoRepo->createQueryBuilder('sph')
                    ->delete()
                    ->where('sph.file_name=:file_name')
                    ->setParameter('file_name', $shop_asset_dir . '/' . $delete_image)
                    ->getQuery()
                    ->execute();
                $shopIdDocRepo->createQueryBuilder('sir')
                    ->where('sir.file_name=:file_name')
                    ->setParameter('file_name', $shop_asset_dir . '/' . $delete_image)
                    ->getQuery()
                    ->execute();
            }
            $this->entityManager->flush();
        }
    }

    public function saveShopCategories($Shop, $Categories) {

        $this->clearShopCategories($Shop);

        $ids = [];
        foreach ( $Categories as $Category ) {
            if( in_array( $Category->getId(), $ids )) continue;
            $ids[] = $Category->getId();
            
            $ShopCategory = new ShopCategory;
            $ShopCategory->setShop($Shop);
            $ShopCategory->setShopId($Shop->getId());
            $ShopCategory->setCategory($Category);
            $ShopCategory->setCategoryId($Category->getId());
            $this->entityManager->persist($ShopCategory);
            $Shop->addShopCategory($ShopCategory);
        }
        $this->entityManager->flush();
    }
    public function saveShopSeries($Shop, $Serieses)
    {
        $this->clearShopSerieses($Shop);
        $ids = [];
        foreach ( $Serieses as $Series ) {
            if( in_array( $Series->getId(), $ids )) continue;
            $ids[] = $Series->getId();
            
            $ShopSeries = new ShopSeries;
            $ShopSeries->setShop($Shop);
            $ShopSeries->setShopId($Shop->getId());
            $ShopSeries->setSeries($Series);
            $ShopSeries->setSeriesId($Series->getId());
            $this->entityManager->persist($ShopSeries);
            $Shop->addShopSeries($ShopSeries);
        }
        $this->entityManager->flush();
    }
    public function clearShopCategories($Shop) {
        foreach($Shop->getShopCategories() as $ShopCategory) {
            $Shop->removeShopCategory($ShopCategory);
            $this->entityManager->remove($ShopCategory);
        }
        $this->entityManager->persist($Shop);
        $this->entityManager->flush();
    }
    public function clearShopSerieses($Shop) {
        foreach($Shop->getShopSerieses() as $ShopSeries) {
            $Shop->removeShopSeries($ShopSeries);
            $this->entityManager->remove($ShopSeries);
        }
        $this->entityManager->persist($Shop);
        $this->entityManager->flush();
    }
    
    // customer page
    public function getProductsByShop( $Shop ) {
        $prodRepo = $this->entityManager->getRepository(Product::class);
        // TODO pagination

        return $prodRepo->findBy(['Shop' => $Shop]);
    }

    public function replaceNavForShopOwner( $eccube_nav ) {
        $eccube_nav_alt = $eccube_nav;
        // change admin_setting_shop to shop edit screen
        $eccube_nav_alt['setting']['children']['shop']['children']['shop_index']['url'] = 'malldevel_admin_shop_edit';
        unset($eccube_nav_alt['mshop']);
        // print_r($eccube_nav);
        if (!empty($eccube_nav_alt['setting']['children']['rank'])) {
            unset($eccube_nav_alt['setting']['children']['rank']);
        }
        if (!empty($eccube_nav_alt['content']['children']['customerrank'])) {
            unset($eccube_nav_alt['content']['children']['customerrank']);
        }
        return $eccube_nav_alt;
    }
    
    public function navForApplicant($eccube_nav) 
    {
        $nav = [];
        $nav["mshop"] = [
            'name'  =>  'malldevel.admin.shop.label',
            'icon'  =>  'fas fa-store',
            'children'  =>  [
                'list'  =>  [
                    'name'  =>  'malldevel.admin.apply.stripe_apply',
                    'url'   =>  'malldevel_applicant_stripe_apply',
                ],
            ]
        ];
        return $nav;
    }
    public function random_password($length){
        //A list of characters that can be used in our
        //random password.
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!-.[]?*()';
        //Create a blank string.
        $password = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $password .= $characters[random_int(0, $characterListLength)];
        }
        return $password;
    }

    public function random_string($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        //Create a blank string.
        $rand = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $rand .= $characters[random_int(0, $characterListLength)];
        }
        return $rand;
    }
    public function random_number($length) {
        $characters = '0123456789';
        //Create a blank string.
        $number = '';
        //Get the index of the last character in our $characters string.
        $characterListLength = mb_strlen($characters, '8bit') - 1;
        //Loop from 1 to the $length that was specified.
        foreach(range(1, $length) as $i){
            $number .= $characters[random_int(0, $characterListLength)];
        }
        return $number;
    }
    public function generateApplyUuid() {
        $applyRepository = $this->container->get(ApplyRepository::class);
        
        while(true) {
            $uuid = $this->random_string(25);
            $existing = $applyRepository->findOneBy(['uuid' => $uuid]);
            if ($existing) continue;
            return $uuid;
        }
    }
    public function getUniqLoginId($seedLoginId) {
        $memberRepository = $this->container->get(MemberRepository::class);
        $applyRepository = $this->container->get(ApplyRepository::class);

        $login_id = $seedLoginId;
        while(true) {
            $existing_apply = $applyRepository->findOneBy(['login_id' => $login_id, 'status' => Apply::STATUS_ALLOWED]);
                
            if ($existing_apply) {
                $login_id = $seedLoginId . $this->random_number(4);
                continue;
            }
            
            $existing_member = $memberRepository->findOneBy(['login_id' => $login_id]);
            if ($existing_member) {
                $login_id = $seedLoginId . $this->random_number(4);
                continue;
            }
            return $login_id;
        }
    }

}