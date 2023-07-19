<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206124951 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if(!$schema->hasTable('cmd_shop')){
            $table = $schema->createTable('cmd_shop');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true]);
            $table->setPrimaryKey(['id']);

            $table->addColumn('order_mail', 'string', ['length' => 255]);
            $table->addColumn('name','string', ['length' => 255]);
            $table->addColumn('logo', 'string', ['length'   =>  255]);
        }

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        if($schema->hasTable('cmd_shop')){
            $schema->dropTable('cmd_shop');
        }
    }
}
