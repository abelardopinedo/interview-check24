<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260501072413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__provider AS SELECT id, name, url, has_discount FROM provider');
        $this->addSql('DROP TABLE provider');
        $this->addSql('CREATE TABLE provider (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, has_discount BOOLEAN NOT NULL, internal_key VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO provider (id, name, url, has_discount) SELECT id, name, url, has_discount FROM __temp__provider');
        $this->addSql('DROP TABLE __temp__provider');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92C4739C69F34668 ON provider (internal_key)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__provider AS SELECT id, name, url, has_discount FROM provider');
        $this->addSql('DROP TABLE provider');
        $this->addSql('CREATE TABLE provider (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, has_discount BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO provider (id, name, url, has_discount) SELECT id, name, url, has_discount FROM __temp__provider');
        $this->addSql('DROP TABLE __temp__provider');
    }
}
