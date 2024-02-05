<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240203190847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE category_note (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, note_id INT NOT NULL, INDEX IDX_4426805312469DE2 (category_id), INDEX IDX_4426805326ED0855 (note_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_note ADD CONSTRAINT FK_4426805312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE' );
        $this->addSql('ALTER TABLE category_note ADD CONSTRAINT FK_4426805326ED0855 FOREIGN KEY (note_id) REFERENCES notes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category_note DROP FOREIGN KEY FK_4426805312469DE2');
        $this->addSql('ALTER TABLE category_note DROP FOREIGN KEY FK_4426805326ED0855');
        $this->addSql('DROP TABLE category_note');
  
    }
}
