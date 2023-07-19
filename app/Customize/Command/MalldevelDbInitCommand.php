<?php

namespace Customize\Command;

use Customize\Entity\Master\ShopStatus;
use Customize\Repository\Master\ShopStatusRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Entity\Master\Authority;
use Eccube\Entity\AuthorityRole;
use Eccube\Repository\AuthorityRoleRepository;
use Eccube\Repository\Master\AuthorityRepository;
use Customize\Entity\Master\BlogType;
use Customize\Entity\Katakana;
use Customize\Entity\Master\Series;
use Customize\Repository\KatakanaRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Customize\Repository\Master\SeriesRepository;
use Customize\Services\MailService;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\Payment;
use Eccube\Entity\Csv;
use Eccube\Entity\Master\CsvType;
use Customize\Services\Payment\Method\StripeCredit;

class MalldevelDbInitCommand extends Command {
    
    protected static $defaultName = "malldevel:db:init";

    const AUTHORITY_NAME = 'ショップ';
    const TEMP_SHOP_AUTHORITY_NAME = "申請者";

    const SHOP_AUTHORITY_XML_PATH = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'shop_authority.xml';

    const DENY_URLS = [
        '/product/category',
        '/product/tag',
        '/product/category_csv_upload',
        '/customer',
        '/content/news',
        '/content/file_manager',
        '/content/layout',
        '/content/page',
        '/content/css',
        '/content/js',
        '/content/block',
        '/content/cache',
        '/content/category',
        '/content/tag',
        '/content/blog',
        '/content/customer_rank',
        '/content/maintenance',
        '/setting/shop/payment',
        '/setting/shop/tax',
        '/setting/shop/mail',
        '/setting/shop/csv',
        '/setting/system',
        '/setting/customer_rank',
        '/store',
        '/shopping_mall',
        '/shop/list',
        '/stripe',
        '/series',
        '/content/feature',
        '/plugin',
        '/order/search/customer/html',
        '/apply'
    ];

    const KATAKANA = [
        'ア', 'カ', 'サ', 'タ', 'ナ', 'ハ', 'マ', 'ヤ', 'ラ・ワ',
        'A～Z・数字'];

    const BLOG_TYPES = [
        [
            'id' => BlogType::NOTICE,
            'name' => 'お知らせ'
        ],
        [
            'id' => BlogType::INFORMATION_SITE,
            'name' => '情報発信サイト',
        ]
    ];

    const SERIES = [
        [
            'id'    =>  Series::DC2MALL,
            'name'  =>  "DC2モール"
        ],
        [
            'id'    =>  Series::KODAWARI,
            'name'  =>  'BUYER SHOP　KODAWARI'
        ],
        [
            'id'    =>  Series::SPECIALTY,
            'name'  =>  'SPECIALTY SHOP　匠'
        ],
        [
            'id'    =>  Series::SUPER_GENERATION,
            'name'  =>  'スーパー世代のこだわり（仮）'
        ]
    ];

    const SHOP_STATUSES = [
        [
            'id' => ShopStatus::DISPLAY_SHOW,
            'name' => '公開'
        ],
        [
            'id' => ShopStatus::DISPLAY_HIDE,
            'name' => '非公開'
        ]
    ];

    protected $container;
    protected $authorityRepository;
    protected $authorityRoleRepository;
    protected $entityManager;
    protected $katakanaRepository;
    /**
     * @var \Customize\Repository\Master\BlogTypeRepository
     */
    protected $blogTypeRepository;
    /**
     * @var ShopStatusRepository
     */
    protected $shopStatusRepository;

    public function __construct(
        ContainerInterface $container,
        AuthorityRepository $authorityRepository,
        AuthorityRoleRepository $authorityRoleRepository,
        KatakanaRepository $katakanaRepository,
        BlogTypeRepository $blogTypeRepository,
        ShopStatusRepository $shopStatusRepository
    ) {
        $this->container = $container;
        $this->authorityRepository = $authorityRepository;
        $this->authorityRoleRepository = $authorityRoleRepository;
        $this->katakanaRepository = $katakanaRepository;
        $this->blogTypeRepository = $blogTypeRepository;
        $this->shopStatusRepository = $shopStatusRepository;
        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->write("malldevel role initializing...\n");
        
        $Authority = $this->authorityRepository->findOneBy(['name' => self::AUTHORITY_NAME]);
        if( !$Authority ) {
            $id = $this->authorityRepository->createQueryBuilder('a')
                ->select('MAX(a.id)')
                ->getQuery()
                ->getSingleScalarResult();
            if (!$id) {
                $id = 0;
            }
            $sortNo = $this->authorityRepository->createQueryBuilder('a')
                ->select('MAX(a.sort_no)')
                ->getQuery()
                ->getSingleScalarResult();
            if (!$sortNo) {
                $sortNo = 0;
            }
            $Authority = new Authority();
            $Authority->setId($id + 1);
            $Authority->setName(self::AUTHORITY_NAME);
            $Authority->setSortNo($sortNo + 1);
            $this->authorityRepository->save($Authority);

            // 作成した権限を保持
            file_put_contents(
                self::SHOP_AUTHORITY_XML_PATH,
                $Authority->toXML()
            );
        }
        
        foreach (self::DENY_URLS as $denyUrl) {
            $AuthorityRole = $this->authorityRoleRepository->findOneBy(['deny_url' => $denyUrl]);
            if( $AuthorityRole ) {
                continue;
            }
            $AuthorityRole = new AuthorityRole();
            $AuthorityRole->setAuthority($Authority);
            $AuthorityRole->setDenyUrl($denyUrl);
            $this->authorityRoleRepository->save($AuthorityRole);
        }
        $this->entityManager->flush();

        $Authority = $this->authorityRepository->findOneBy(['name'  =>  self::TEMP_SHOP_AUTHORITY_NAME]);
        if (!$Authority) {
            $Authority = new Authority();
            $Authority->setId(3);
            $Authority->setName(self::TEMP_SHOP_AUTHORITY_NAME);
            $Authority->setSortNo(3);
            $this->authorityRepository->save($Authority);
        }
        $output->write("Authority table initialized!\n");
        $output->write("successfully initialized\n");
        
        // Katakana init
        $katas = $this->katakanaRepository->findAll();
        if ( count( $katas ) > 0 ) {
            $output->write("Katakana table is already initialized.\n");
        }else {
            foreach( self::KATAKANA as $ch) {
                $kata = new Katakana;
                $kata->setCharacter($ch);
                $this->katakanaRepository->save($kata);
            }
            $this->entityManager->flush();
            $output->write("Katakana initialized.\n");
        }

        $this->initShopStatuses($output);
        $this->initBlogTypes($output);
        $this->initSeries($output);
        $this->insertMailTemplate($output);
        $this->paymentMethodRegister($output);
        $this->insertShopAndMemberToOrderCsv($output);
    }

    private function insertShopAndMemberToOrderCsv(OutputInterface $output) {
        $CsvRepo = $this->entityManager->getRepository(Csv::class);
        $CsvTypeRepo = $this->entityManager->getRepository(CsvType::class);
        $OrderCsvType = $CsvTypeRepo->find(CsvType::CSV_TYPE_ORDER);
        if (!$OrderCsvType) {
            $output->write("Order csv type does not exists\n");
            return;
        }
        $LastField = $CsvRepo->findOneBy([], ['sort_no' => 'DESC']);
        $last_sort_no = $LastField->getSortNo();

        $ShopField = $CsvRepo->findOneBy(['entity_name' => 'Customize\\\\Entity\\\\Shop', 'field_name' => 'name']);
        if (!$ShopField) {
            $ShopField = new Csv;
            $ShopField->setEntityName('Customize\\\\Entity\\\\Shop')
                ->setFieldName('name')
                ->setDispName('ショップ')
                ->setSortNo($last_sort_no + 1)
                ->setEnabled(true)
                ->setCsvType($OrderCsvType);
            $CsvRepo->save($ShopField);
            $output->write("Shop Field successfully saved in order csv\n");
        } else {
            $output->write("Shop Field already exists in order csv\n");
        }

        $MemberField = $CsvRepo->findOneBy(['entity_name' => 'Eccube\\\\Entity\\\\Member', 'field_name' => 'name']);
        if (!$MemberField) {
            $MemberField = new Csv;
            $MemberField->setEntityName('Eccube\\\\Entity\\\\Member')
                ->setFieldName('name')
                ->setDispName('メンバー')
                ->setSortNo($last_sort_no + 2)
                ->setEnabled(true)
                ->setCsvType($OrderCsvType);
            $CsvRepo->save($MemberField);
            $output->write("Member Field successfully saved in order csv\n");
        } else {
            $output->write("Member Field already exists in order csv\n");
        }
        $this->entityManager->flush();
    }

    private function initShopStatuses(OutputInterface $output)
    {
        $shopStatuses = $this->shopStatusRepository->findAll();
        if (count($shopStatuses) > 0) {
            $output->write("Shop status table is already initialized.\n");
            return;
        }
        foreach(self::SHOP_STATUSES as $index => $shopStatus) {
            $ShopStatus = new ShopStatus();
            $ShopStatus->setId($shopStatus['id']);
            $ShopStatus->setName($shopStatus['name']);
            $ShopStatus->setSortNo(count(self::SHOP_STATUSES) - $index);
            $this->shopStatusRepository->save($ShopStatus);
        }
    }

    /**
     * @param OutputInterface $output
     */
    private function initBlogTypes(OutputInterface $output)
    {
        $blogTypes = $this->blogTypeRepository->findAll();
        if (count($blogTypes) > 0) {
            $output->write("Blog type table is already initialized.\n");
        } else {
            foreach(self::BLOG_TYPES as $index => $blogType) {
                $BlogType = new BlogType();
                $BlogType->setId($blogType['id']);
                $BlogType->setName($blogType['name']);
                $BlogType->setSortNo(count(self::BLOG_TYPES) - $index);
                $this->blogTypeRepository->save($BlogType);
            }
            $this->entityManager->flush();
            $output->write("Blog type initialized.\n");
        }
    }

    private function initSeries($output)
    {
        $SeriesRepository = $this->entityManager->getRepository(Series::class);
        $Series = $SeriesRepository->findAll();
        if (count($Series) > 0) {
            $output->write("Series is already initialized\n");
        } else {
            foreach (self::SERIES as $index => $SeriesItem)
            {
                $Series = new Series();
                $Series->setId($SeriesItem['id']);
                $Series->setName($SeriesItem['name']);
                $Series->setSortNo($index + 1);
                $SeriesRepository->save($Series);
            }
            $this->entityManager->flush();
            $output->write("Series table is initialized!\n");
        }
    }
    private function insertMailTemplate($output) {
        $template_list = [
            [
                'name'          =>  MailService::SHOP_CREATED,
                'file_name'     =>  'Mail/shop_created.twig',
                'mail_subject'  =>  "ショップが作成されました"
            ],
            [
                'name'          =>  MailService::APPLICANT_CREATED,
                'file_name'     =>  'Mail/applicant_created.twig',
                'mail_subject'  =>  "申し込みが承認されました"
            ],
            [
                'name'          =>  MailService::APPLICANT_HOLDED,
                'file_name'     =>  'Mail/applicant_on_hold.twig',
                'mail_subject'  =>  "申し込みが保留になりました"
            ],
            [
                'name'          =>  MailService::APPLICATION_REGISTERED,
                'file_name'     =>  'Mail/application_registered.twig',
                'mail_subject'  =>  "申し込みが登録されました"
            ]
        ];
        $output->write("insert mail template ...\n");

        $i = 0;
        foreach($template_list as $template_data)
        {
            $MailTemplate = $this->entityManager->getRepository(MailTemplate::class)->findOneBy(['name' =>  $template_data['name']]);
            if ($MailTemplate) continue;

            $i++;
            $MailTemplate = new MailTemplate();
            $MailTemplate
                ->setName($template_data['name'])
                ->setFileName($template_data['file_name'])
                ->setMailSubject($template_data['mail_subject']);
            $this->entityManager->persist($MailTemplate);
            $this->entityManager->flush();
        }
        if ($i) {
            $output->write("$i of mail templates are added.\n");
        } else {
            $output->write("Mail templates are already initialized\n");
        }
    }

    private function paymentMethodRegister($output) 
    {
        $output->write("Register stripe credit payment\n");
        $paymentRepository = $this->entityManager->getRepository(Payment::class);
        $Payment = $paymentRepository->findOneBy([], ['sort_no' => 'DESC']);
        $current_sort_no = $Payment ? $Payment->getSortNo() : 1;

        $CreditPayment = $paymentRepository->findOneBy(['method_class' => StripeCredit::class]);
        if (!$CreditPayment) {
            $output->write("credit card payment registering ...\n");
            $current_sort_no++;
            $Payment = new Payment();
            $Payment->setCharge(0);
            $Payment->setSortNo($current_sort_no);
            $Payment->setVisible(true);

            $Payment->setMethodClass(StripeCredit::class);
            $Payment->setMethod('クレジットカード');
            $this->entityManager->persist($Payment);
        }
        $output->write("credit card payment registered!\n");

        $this->entityManager->flush();
    }
}
