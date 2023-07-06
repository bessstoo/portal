<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230706141150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A4B89032C');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A7E3C61F9');
        $this->addSql('DROP INDEX IDX_5F9E962A7E3C61F9 ON comments');
        $this->addSql('DROP INDEX IDX_5F9E962A4B89032C ON comments');
        $this->addSql('ALTER TABLE comments DROP owner_id, DROP post_id, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D7E3C61F9');
        $this->addSql('DROP INDEX IDX_5A8A6C8D7E3C61F9 ON post');
        $this->addSql('ALTER TABLE post DROP owner_id, CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments ADD owner_id INT NOT NULL, ADD post_id INT NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5F9E962A7E3C61F9 ON comments (owner_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A4B89032C ON comments (post_id)');
        $this->addSql('ALTER TABLE post ADD owner_id INT NOT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D7E3C61F9 ON post (owner_id)');
        $this->addSql('ALTER TABLE user CHANGE id id INT AUTO_INCREMENT NOT NULL');
    }
}
