<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230313143331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__pay_pal_order AS SELECT id, data, created_at, paypal_id, tracking_number, finalized FROM pay_pal_order');
        $this->addSql('DROP TABLE pay_pal_order');
        $this->addSql('CREATE TABLE pay_pal_order (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , paypal_id VARCHAR(255) NOT NULL, tracking_number VARCHAR(255) DEFAULT NULL, finalized BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO pay_pal_order (id, data, created_at, paypal_id, tracking_number, finalized) SELECT id, data, created_at, paypal_id, tracking_number, finalized FROM __temp__pay_pal_order');
        $this->addSql('DROP TABLE __temp__pay_pal_order');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5203014349BE503 ON pay_pal_order (paypal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__pay_pal_order AS SELECT id, data, created_at, paypal_id, tracking_number, finalized FROM pay_pal_order');
        $this->addSql('DROP TABLE pay_pal_order');
        $this->addSql('CREATE TABLE pay_pal_order (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , paypal_id VARCHAR(255) NOT NULL, tracking_number VARCHAR(255) DEFAULT NULL, finalized BOOLEAN DEFAULT NULL)');
        $this->addSql('INSERT INTO pay_pal_order (id, data, created_at, paypal_id, tracking_number, finalized) SELECT id, data, created_at, paypal_id, tracking_number, finalized FROM __temp__pay_pal_order');
        $this->addSql('DROP TABLE __temp__pay_pal_order');
    }
}
