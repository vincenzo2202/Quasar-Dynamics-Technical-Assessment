<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240203153751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user table with fields id, name, email, created_at, and updated_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (
            id INT AUTO_INCREMENT NOT NULL, 
            username VARCHAR(50) NOT NULL, 
            email VARCHAR(100) NOT NULL UNIQUE, 
            password VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user');
    }
}
