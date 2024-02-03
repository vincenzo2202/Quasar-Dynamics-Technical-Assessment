<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

 
final class Version20240203163542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notes table with fields id, user_id,title,note, created_at, and updated_at';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notes (
            id INT AUTO_INCREMENT NOT NULL, 
            user_id INT NOT NULL, 
            title VARCHAR(100) NOT NULL, 
            note VARCHAR(255) NOT NULL, 
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX IDX_11BA68CA76ED395 (user_id), 
            PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA76ED395');
        $this->addSql('DROP TABLE notes');
        $this->addSql('ALTER TABLE user MODIFY created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, MODIFY updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    }
}
