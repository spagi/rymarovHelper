<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727191103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX content_fulltext_idx ON web_page');
        $this->addSql('CREATE FULLTEXT INDEX web_content_fulltext_idx ON web_page (title, content)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX web_content_fulltext_idx ON web_page');
        $this->addSql('CREATE FULLTEXT INDEX content_fulltext_idx ON web_page (content)');
    }
}
