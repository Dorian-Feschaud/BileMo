<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221115517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product CHANGE screen_resolution screen_resolution INT NOT NULL, CHANGE back_camera_resolution back_camera_resolution INT NOT NULL, CHANGE front_camera_resolution front_camera_resolution INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product CHANGE screen_resolution screen_resolution VARCHAR(255) NOT NULL, CHANGE back_camera_resolution back_camera_resolution VARCHAR(255) NOT NULL, CHANGE front_camera_resolution front_camera_resolution VARCHAR(255) NOT NULL');
    }
}
