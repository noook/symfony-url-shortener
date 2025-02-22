<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221122127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE visits (id SERIAL NOT NULL, short_link_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ip VARCHAR(255) NOT NULL, user_agent TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_444839EA605D5D9 ON visits (short_link_id)');
        $this->addSql('COMMENT ON COLUMN visits.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE visits ADD CONSTRAINT FK_444839EA605D5D9 FOREIGN KEY (short_link_id) REFERENCES short_link (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE visits DROP CONSTRAINT FK_444839EA605D5D9');
        $this->addSql('DROP TABLE visits');
    }
}
