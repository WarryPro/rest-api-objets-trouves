<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216140423 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_C53D045F126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE category CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE item DROP image, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(150) DEFAULT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE category CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE item ADD image VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE avatar avatar VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE role role VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
