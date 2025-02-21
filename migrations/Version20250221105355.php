<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221105355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, manufacturer VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL, price DOUBLE PRECISION NOT NULL, color VARCHAR(255) NOT NULL, capacity INT NOT NULL, height DOUBLE PRECISION NOT NULL, width DOUBLE PRECISION NOT NULL, thickness DOUBLE PRECISION NOT NULL, weight INT NOT NULL, screen VARCHAR(255) NOT NULL, screen_height DOUBLE PRECISION NOT NULL, screen_width DOUBLE PRECISION NOT NULL, screen_resolution VARCHAR(255) NOT NULL, back_camera VARCHAR(255) NOT NULL, back_camera_resolution VARCHAR(255) NOT NULL, front_camera_resolution VARCHAR(255) NOT NULL, processor VARCHAR(255) NOT NULL, ram INT NOT NULL, battery_capacity INT NOT NULL, network VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product');
    }
}
