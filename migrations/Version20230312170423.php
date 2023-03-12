<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230312170423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD COLUMN max_allowed_per_order INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, name, label, price, description, stock, images FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, description CLOB DEFAULT NULL, stock INTEGER NOT NULL, images CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO product (id, name, label, price, description, stock, images) SELECT id, name, label, price, description, stock, images FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
    }
}
