<?php

namespace Customize\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Layout;


class MalldevelPageInitCommand extends Command {
    protected static $defaultName = "malldevel:page:init";

    protected $container;
    protected $entityManager;

    protected $sql_base_path = __DIR__ . '/../../Database/';

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.entity_manager');
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->clearPages($output);

        $output->write("insert pages...\n");
        $this->executeSql($this->sql_base_path . 'dtb_layout.sql', $output);
        $this->executeSql($this->sql_base_path . 'dtb_block.sql', $output);
        $this->executeSql($this->sql_base_path . 'dtb_block_position.sql', $output);
        $this->executeSql($this->sql_base_path . 'dtb_page.sql', $output);
        $this->executeSql($this->sql_base_path . 'dtb_page_layout.sql', $output);
        $output->write("All pages inserted!\n");
        $this->entityManager->flush();
        $output->write("Page insertion flushed!\n");
    }

    private function clearPages($output) {
        
        $output->write("delete dtb_block_position...\n");
        $this->entityManager->getRepository(BlockPosition::class)->createQueryBuilder('bp')
            ->delete()
            ->getQuery()
            ->execute();
        
        $output->write("dtb_block_position deleted!\n");

        $output->write("delete dtb_block...\n");
        $this->entityManager->getRepository(Block::class)->createQueryBuilder('b')
            ->delete()
            ->getQuery()
            ->execute();
        $output->write("dtb_block deleted!\n");
        
        $output->write("delete dtb_page_layout...");
        $this->entityManager->getRepository(PageLayout::class)->createQueryBuilder('pl')
            ->delete()
            ->getQuery()
            ->execute();
        $output->write("dtb_page_layout deleted!\n");
        $this->entityManager->flush();

        $output->write('delete dtb_page...\n');
        $page_repo = $this->entityManager->getRepository(Page::class);
        $pages = $page_repo->findAll();
        $normal_pages = []; $child_pages = [];
        foreach($pages as $page){
            if ($page->getMasterPage()) {
                $child_pages[] = $page;
            } else {
                $normal_pages[] = $page;
            }
        }
        foreach($child_pages as $page) {
            $this->entityManager->remove($page);
        }
        foreach($normal_pages as $page) {
            $this->entityManager->remove($page);
        }
        $output->write("dtb_page deleted!\n");
        
        $output->write("delete dtb_layout...\n");
        $this->entityManager->getRepository(Layout::class)->createQueryBuilder('l')
            ->delete()
            ->getQuery()
            ->execute();
        $output->write("dtb_layout deleted!\n");
        
        $this->entityManager->flush();
        $output->write("page sql flushed!!!\n");
    }

    private function executeSql($file_name, $output) {
        $full_sql = \file_get_contents($file_name);

        $insert_sql = \strstr($full_sql, "INSERT INTO");
        $this->entityManager->getConnection()->exec($insert_sql);
    }
}