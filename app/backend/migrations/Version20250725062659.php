<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725062659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_9702542296A9B60A ON bulletin_board_item');
        $this->addSql('CREATE FULLTEXT INDEX fulltext_idx ON bulletin_board_item (title, full_text_content)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX fulltext_idx ON bulletin_board_item');
        $this->addSql('CREATE FULLTEXT INDEX IDX_9702542296A9B60A ON bulletin_board_item (full_text_content)');
    }
}
