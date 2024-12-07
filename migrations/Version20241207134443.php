<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241207134443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE template_record (id INT AUTO_INCREMENT NOT NULL, template_id INT NOT NULL, name VARCHAR(255) NOT NULL, ttl INT NOT NULL, type VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_90DC96635DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE template_record ADD CONSTRAINT FK_90DC96635DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE template_record DROP FOREIGN KEY FK_90DC96635DA0FB8');
        $this->addSql('DROP TABLE template_record');
    }
}
