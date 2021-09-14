<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210906133415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD phone_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN client.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555E237E06 ON client (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_C74404555E237E06');
        $this->addSql('ALTER TABLE client DROP phone_number');
        $this->addSql('ALTER TABLE client DROP address');
        $this->addSql('ALTER TABLE client DROP description');
        $this->addSql('ALTER TABLE client DROP email');
        $this->addSql('ALTER TABLE client DROP created_at');
    }
}
