<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241209140409 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', change_set JSON NOT NULL COMMENT \'(DC2Type:json)\', entity JSON NOT NULL COMMENT \'(DC2Type:json)\', action VARCHAR(32) NOT NULL, INDEX IDX_F6E1C0F5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE forward_zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(8) NOT NULL, serial INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE reverse_zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(8) NOT NULL, serial INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, records JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_IDENTIFIER_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE template_record (id INT AUTO_INCREMENT NOT NULL, template_id INT NOT NULL, name VARCHAR(255) NOT NULL, ttl INT NOT NULL, type VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_90DC96635DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, role VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, fullname VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE template_record ADD CONSTRAINT FK_90DC96635DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user_forward_zone DROP FOREIGN KEY FK_561B81A8B0BC5D7');
		$this->addSql('ALTER TABLE user_reverse_zone DROP FOREIGN KEY FK_AF682BFBF8152122');
		$this->addSql('ALTER TABLE user_forward_zone DROP FOREIGN KEY FK_561B81A8A76ED395');
		$this->addSql('ALTER TABLE user_reverse_zone DROP FOREIGN KEY FK_AF682BFBA76ED395');
		$this->addSql('ALTER TABLE audit_log DROP FOREIGN KEY FK_F6E1C0F5A76ED395');
		$this->addSql('ALTER TABLE template_record DROP FOREIGN KEY FK_90DC96635DA0FB8');
		$this->addSql('DROP TABLE audit_log');
		$this->addSql('DROP TABLE forward_zone');
		$this->addSql('DROP TABLE reverse_zone');
		$this->addSql('DROP TABLE template');
		$this->addSql('DROP TABLE template_record');
		$this->addSql('DROP TABLE user');
	}
}
