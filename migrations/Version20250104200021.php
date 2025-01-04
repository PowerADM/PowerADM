<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104200021 extends AbstractMigration {
	public function getDescription(): string {
		return '';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user_forward_zone DROP FOREIGN KEY FK_561B81A8A76ED395');
		$this->addSql('ALTER TABLE user_forward_zone DROP FOREIGN KEY FK_561B81A8B0BC5D7');
		$this->addSql('ALTER TABLE user_forward_zone ADD CONSTRAINT FK_561B81A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE user_forward_zone ADD CONSTRAINT FK_561B81A8B0BC5D7 FOREIGN KEY (forward_zone_id) REFERENCES forward_zone (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE user_reverse_zone DROP FOREIGN KEY FK_AF682BFBA76ED395');
		$this->addSql('ALTER TABLE user_reverse_zone DROP FOREIGN KEY FK_AF682BFBF8152122');
		$this->addSql('ALTER TABLE user_reverse_zone ADD CONSTRAINT FK_AF682BFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
		$this->addSql('ALTER TABLE user_reverse_zone ADD CONSTRAINT FK_AF682BFBF8152122 FOREIGN KEY (reverse_zone_id) REFERENCES reverse_zone (id) ON DELETE CASCADE');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user_reverse_zone DROP FOREIGN KEY FK_AF682BFBA76ED395');
		$this->addSql('ALTER TABLE user_reverse_zone DROP FOREIGN KEY FK_AF682BFBF8152122');
		$this->addSql('ALTER TABLE user_reverse_zone ADD CONSTRAINT FK_AF682BFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE user_reverse_zone ADD CONSTRAINT FK_AF682BFBF8152122 FOREIGN KEY (reverse_zone_id) REFERENCES reverse_zone (id)');
		$this->addSql('ALTER TABLE user_forward_zone DROP FOREIGN KEY FK_561B81A8A76ED395');
		$this->addSql('ALTER TABLE user_forward_zone DROP FOREIGN KEY FK_561B81A8B0BC5D7');
		$this->addSql('ALTER TABLE user_forward_zone ADD CONSTRAINT FK_561B81A8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE user_forward_zone ADD CONSTRAINT FK_561B81A8B0BC5D7 FOREIGN KEY (forward_zone_id) REFERENCES forward_zone (id)');
	}
}
