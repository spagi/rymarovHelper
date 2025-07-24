<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250723161305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bulletin_board_document (id INT AUTO_INCREMENT NOT NULL, item_id INT NOT NULL, file_name VARCHAR(255) NOT NULL, file_url VARCHAR(512) NOT NULL, INDEX IDX_4FC46D02126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bulletin_board_item (id INT AUTO_INCREMENT NOT NULL, iri VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, department VARCHAR(255) DEFAULT NULL, agenda VARCHAR(255) DEFAULT NULL, reference_number VARCHAR(255) DEFAULT NULL, published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', relevant_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', detail_url VARCHAR(255) NOT NULL, full_text_content LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_970254229120EB35 (iri), FULLTEXT INDEX IDX_9702542296A9B60A (full_text_content), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bulletin_board_document ADD CONSTRAINT FK_4FC46D02126F525E FOREIGN KEY (item_id) REFERENCES bulletin_board_item (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bulletin_board_document DROP FOREIGN KEY FK_4FC46D02126F525E');
        $this->addSql('DROP TABLE bulletin_board_document');
        $this->addSql('DROP TABLE bulletin_board_item');
    }
}
