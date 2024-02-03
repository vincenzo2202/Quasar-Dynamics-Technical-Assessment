<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

 
final class Version20240203171413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create category table with fields id, category, description, created_at, and updated_at';
    }

    public function up(Schema $schema): void
    { 
        $this->addSql('CREATE TABLE category (
            id INT AUTO_INCREMENT NOT NULL,
            category VARCHAR(50) NOT NULL,
            description VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY(id)
               ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notes CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE category');
        $this->addSql('ALTER TABLE notes CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
