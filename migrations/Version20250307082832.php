<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250307082832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09642B8210');
        $this->addSql('DROP INDEX UNIQ_81398E09642B8210 ON customer');
        $this->addSql('ALTER TABLE customer DROP admin_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09642B8210 ON customer (admin_id)');
    }
}
