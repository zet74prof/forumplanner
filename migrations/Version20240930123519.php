<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240930123519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE forum ADD CONSTRAINT FK_852BBECDC54C8C93 FOREIGN KEY (type_id) REFERENCES type_forum (id)');
        $this->addSql('CREATE INDEX IDX_852BBECDC54C8C93 ON forum (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forum DROP FOREIGN KEY FK_852BBECDC54C8C93');
        $this->addSql('DROP INDEX IDX_852BBECDC54C8C93 ON forum');
        $this->addSql('ALTER TABLE forum DROP type_id');
    }
}
