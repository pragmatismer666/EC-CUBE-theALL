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
use Customize\Entity\Shop;
use Customize\Entity\Master\Series;
use Customize\Repository\KatakanaRepository;
use Customize\Repository\ShopRepository;
use Customize\Repository\Master\BlogTypeRepository;
use Customize\Repository\Master\SeriesRepository;
use Customize\Services\MailService;
use Eccube\Entity\MailTemplate;
use Eccube\Entity\Payment;
use Customize\Services\Payment\Method\StripeCredit;
use Customize\Services\ShopService;

class MalldevelShopDeliveryCheckCommand extends Command {
    
    protected static $defaultName = "malldevel:shop_delivery:check";

    protected $container;
    protected $shop_service;
    protected $shop_repo;
    
    public function __construct(
        ContainerInterface $container,
        ShopService $shop_service,
        ShopRepository $shop_repo
    ) {
        $this->container = $container;
        $this->shop_service = $shop_service;
        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');
        $this->shop_repo = $shop_repo;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->write("malldevel shop delivery check...\n");
        
        $shops = $this->shop_repo->findAll();

        foreach($shops as $Shop)
        {
            $count = $this->shop_service->createDefaultDeliveries($Shop);
            if ($count) {
                $output->write("malldevel shop '" . $Shop->getName() . "' delivery created : $count\n");
            }
        }
    }
}
