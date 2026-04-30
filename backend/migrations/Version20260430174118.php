<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430174118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE provider_request_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, request_payload CLOB DEFAULT NULL, response_payload CLOB DEFAULT NULL, latency INTEGER DEFAULT NULL, status VARCHAR(20) NOT NULL, http_code INTEGER DEFAULT NULL, url VARCHAR(255) NOT NULL, error_message CLOB DEFAULT NULL, created_at DATETIME NOT NULL, request_id INTEGER NOT NULL, provider_id INTEGER NOT NULL, CONSTRAINT FK_878AA47B427EB8A5 FOREIGN KEY (request_id) REFERENCES request_log (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_878AA47BA53A8AA FOREIGN KEY (provider_id) REFERENCES provider (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_878AA47B427EB8A5 ON provider_request_log (request_id)');
        $this->addSql('CREATE INDEX IDX_878AA47BA53A8AA ON provider_request_log (provider_id)');
        $this->addSql('CREATE TABLE request_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, request_payload CLOB DEFAULT NULL, response_payload CLOB DEFAULT NULL, latency INTEGER DEFAULT NULL, endpoint VARCHAR(255) NOT NULL, http_method VARCHAR(10) NOT NULL, status_code INTEGER NOT NULL, created_at DATETIME NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE provider_request_log');
        $this->addSql('DROP TABLE request_log');
    }
}
