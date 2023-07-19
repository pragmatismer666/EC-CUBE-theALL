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

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Csv;
use Plugin\CustomerRank\Entity\CustomerRank;
use Plugin\CustomerRank\Entity\CustomerRankConfig;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class PluginManager extends AbstractPluginManager
{
    public function install(array $meta, ContainerInterface $container)
    {
        $file = new Filesystem();
        try {
            $file->copy($container->getParameter('plugin_realdir'). '/CustomerRank/Resource/template/default/Block/customer_rank_info.twig', $container->getParameter('eccube_theme_front_dir'). '/Block/customer_rank_info.twig', true);
            $file->copy($container->getParameter('plugin_realdir'). '/CustomerRank/Resource/template/default/Product/customer_price_list.twig', $container->getParameter('eccube_theme_front_dir'). '/Product/customer_price_list.twig', true);
            $file->copy($container->getParameter('plugin_realdir'). '/CustomerRank/Resource/template/default/Product/customer_price_detail.twig', $container->getParameter('eccube_theme_front_dir'). '/Product/customer_price_detail.twig', true);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uninstall(array $meta, ContainerInterface $container)
    {
        if(file_exists($container->getParameter('eccube_theme_front_dir') . '/Block/customer_rank_info.twig'))
                unlink($container->getParameter('eccube_theme_front_dir') . '/Block/customer_rank_info.twig');
        if(file_exists($container->getParameter('eccube_theme_front_dir') . '/Product/customer_price_list.twig'))
                unlink($container->getParameter('eccube_theme_front_dir') . '/Product/customer_price_list.twig');
        if(file_exists($container->getParameter('eccube_theme_front_dir') . '/Product/customer_price_detail.twig'))
                unlink($container->getParameter('eccube_theme_front_dir') . '/Product/customer_price_detail.twig');
    }

    public function enable(array $meta, ContainerInterface $container)
    {
        $translator = $container->get('translator');
        $ymlPath = $container->getParameter('plugin_realdir') . '/CustomerRank/Resource/locale/messages.'.$translator->getLocale().'.yaml';
        if(!file_exists($ymlPath))$ymlPath = $container->getParameter('plugin_realdir') . '/CustomerRank/Resource/locale/messages.ja.yaml';
        $messages = Yaml::parse(file_get_contents($ymlPath));

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $now = new \DateTime();
        //ブロックの登録
        $Block = new \Eccube\Entity\Block();
        $Block->setFileName('customer_rank_info');
        $Block->setName($messages['customerrank.block.title']);
        $Block->setUseController(true);
        $Block->setDeletable(false);
        $DeviceType = $entityManager->getRepository(DeviceType::class)->find(DeviceType::DEVICE_TYPE_PC);
        $Block->setDeviceType($DeviceType);
        $entityManager->persist($Block);
        $entityManager->flush();

        $this->addCsv($container);

        // 初回有効時に設定の初期値を設定
        $Configs = $entityManager->getRepository(CustomerRankConfig::class)->findAll();
        if(count($Configs) == 0){
            $SetConfigs = [
                'login_disp' => CustomerRankConfig::DISABLED,
                'term' => CustomerRankConfig::UPDATE_OFF,
                'term_start' => CustomerRankConfig::DISABLED,
                'rank_down' => CustomerRankConfig::DISABLED,
                    ];
            foreach($SetConfigs as $name => $value){
                $Config = new CustomerRankConfig();
                $Config->setName($name);
                $Config->setValue($value);
                $entityManager->persist($Config);
            }
            $entityManager->flush();
        }
    }

    public function disable(array $meta, ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => 'customer_rank_info']);
        if($Block){
            $BlockPositions = $entityManager->getRepository(BlockPosition::class)->findBy(['Block' => $Block]);
            foreach($BlockPositions as $BlockPosition){
                $entityManager->remove($BlockPosition);
            }
            $entityManager->remove($Block);
        }

        $Csv = $entityManager->getRepository(Csv::class)->findOneBy(['field_name' => 'CustomerRank']);
        if($Csv){
            $entityManager->remove($Csv);
        }

        $Csvs = $entityManager->getRepository(Csv::class)->findBy(['entity_name' => 'Plugin\\CustomerRank\\Entity\\CustomerPrice']);
        foreach($Csvs as $Csv){
            $entityManager->remove($Csv);
        }

        $Csvs = $entityManager->getRepository(Csv::class)->findBy(['field_name' => 'customer_rank_id']);
        foreach($Csvs as $Csv){
            $entityManager->remove($Csv);
        }
        $Csvs = $entityManager->getRepository(Csv::class)->findBy(['field_name' => 'customer_rank_name']);
        foreach($Csvs as $Csv){
            $entityManager->remove($Csv);
        }
        $entityManager->flush();
    }

    private function addCsv($container)
    {
        $translator = $container->get('translator');
        $ymlPath = $container->getParameter('plugin_realdir') . '/CustomerRank/Resource/locale/messages.'.$translator->getLocale().'.yaml';
        if(!file_exists($ymlPath))$ymlPath = $container->getParameter('plugin_realdir') . '/CustomerRank/Resource/locale/messages.ja.yaml';
        $messages = Yaml::parse(file_get_contents($ymlPath));

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $now = new \DateTime();

        //会員CSV項目追加
        $Csv = new Csv();
        $CsvType = $entityManager->getRepository(CsvType::class)->find(CsvType::CSV_TYPE_CUSTOMER);
        $sort_no = $entityManager->createQueryBuilder()
            ->select('MAX(c.sort_no)')
            ->from('Eccube\Entity\Csv','c')
            ->where('c.CsvType = :csvType')
            ->setParameter(':csvType',$CsvType)
            ->getQuery()
            ->getSingleScalarResult();
        if (!$sort_no) {
            $sort_no = 0;
        }
        $Csv = $entityManager->getRepository(Csv::class)->findOneBy(['field_name' => 'CustomerRank']);
        if(is_null($Csv)){
            $Csv = new Csv();
            $Csv->setCsvType($CsvType);
            $Csv->setEntityName('Eccube\Entity\Customer');
            $Csv->setFieldName('CustomerRank');
            $Csv->setReferenceFieldName('name');
            $Csv->setEnabled(false);
            $Csv->setSortNo($sort_no + 1);
            $Csv->setCreateDate($now);
        }
        $Csv->setDispName($messages['customerrank.common.rank_name']);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);


        // 商品CSV項目追加
        $CsvType = $entityManager->getRepository(CsvType::class)->find(CsvType::CSV_TYPE_PRODUCT);
        $sort_no = $entityManager->createQueryBuilder()
            ->select('MAX(c.sort_no)')
            ->from('Eccube\Entity\Csv','c')
            ->where('c.CsvType = :csvType')
            ->setParameter(':csvType',$CsvType)
            ->getQuery()
            ->getSingleScalarResult();
        if (!$sort_no) {
            $sort_no = 0;
        }

        $CustomerRanks = $entityManager->getRepository(CustomerRank::class)->findBy([], ['priority' => 'desc']);
        foreach($CustomerRanks as $CustomerRank){
            $Csv = new Csv();
            $Csv->setCsvType($CsvType);
            $Csv->setEntityName('Plugin\\CustomerRank\\Entity\\CustomerPrice');
            $Csv->setFieldName('customerrank_price_'.$CustomerRank->getId());
            $Csv->setDispName($CustomerRank->getName().$messages['customerrank.common.customer_price']);
            $Csv->setEnabled(false);
            $Csv->setSortNo(++$sort_no);
            $Csv->setCreateDate($now);
            $Csv->setUpdateDate($now);
            $entityManager->persist($Csv);
        }
        $entityManager->flush();

        $CsvType = $entityManager->getRepository(CsvType::class)->find(CsvType::CSV_TYPE_ORDER);
        $sort_no = $entityManager->createQueryBuilder()
            ->select('MAX(c.sort_no)')
            ->from('Eccube\Entity\Csv','c')
            ->where('c.CsvType = :csvType')
            ->setParameter(':csvType',$CsvType)
            ->getQuery()
            ->getSingleScalarResult();
        if (!$sort_no) {
            $sort_no = 0;
        }
        $Csv = new Csv();
        $Csv->setCsvType($CsvType);
        $Csv->setEntityName('Eccube\Entity\\Order');
        $Csv->setFieldName('customer_rank_id');
        $Csv->setDispName($messages['customerrank.common.rank_id']);
        $Csv->setEnabled(false);
        $Csv->setSortNo(++$sort_no);
        $Csv->setCreateDate($now);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);

        $Csv = new Csv();
        $Csv->setCsvType($CsvType);
        $Csv->setEntityName('Eccube\Entity\\Order');
        $Csv->setFieldName('customer_rank_name');
        $Csv->setDispName($messages['customerrank.common.rank_name']);
        $Csv->setEnabled(false);
        $Csv->setSortNo(++$sort_no);
        $Csv->setCreateDate($now);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);

        $CsvType = $entityManager->getRepository(CsvType::class)->find(CsvType::CSV_TYPE_SHIPPING);
        $sort_no = $entityManager->createQueryBuilder()
            ->select('MAX(c.sort_no)')
            ->from('Eccube\Entity\Csv','c')
            ->where('c.CsvType = :csvType')
            ->setParameter(':csvType',$CsvType)
            ->getQuery()
            ->getSingleScalarResult();
        if (!$sort_no) {
            $sort_no = 0;
        }
        $Csv = new Csv();
        $Csv->setCsvType($CsvType);
        $Csv->setEntityName('Eccube\Entity\\Order');
        $Csv->setFieldName('customer_rank_id');
        $Csv->setDispName($messages['customerrank.common.rank_id']);
        $Csv->setEnabled(false);
        $Csv->setSortNo(++$sort_no);
        $Csv->setCreateDate($now);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);

        $Csv = new Csv();
        $Csv->setCsvType($CsvType);
        $Csv->setEntityName('Eccube\Entity\\Order');
        $Csv->setFieldName('customer_rank_name');
        $Csv->setDispName($messages['customerrank.common.rank_name']);
        $Csv->setEnabled(false);
        $Csv->setSortNo(++$sort_no);
        $Csv->setCreateDate($now);
        $Csv->setUpdateDate($now);
        $entityManager->persist($Csv);
    }

}