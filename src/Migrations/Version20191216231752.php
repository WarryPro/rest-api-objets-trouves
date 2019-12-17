<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216231752 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE image CHANGE item_id item_id INT DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE item CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(150) DEFAULT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE image CHANGE item_id item_id INT DEFAULT NULL, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE item CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE avatar avatar VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE role role VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
