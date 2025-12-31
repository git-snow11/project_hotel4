<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228122427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559D86650F');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849552680A339');
        $this->addSql('DROP INDEX IDX_42C849559D86650F ON reservation');
        $this->addSql('DROP INDEX IDX_42C849552680A339 ON reservation');
        $this->addSql('ALTER TABLE reservation CHANGE user_id_id user_id INT NOT NULL, CHANGE chambre_id_id chambre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id)');
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
        $this->addSql('CREATE INDEX IDX_42C849559B177F54 ON reservation (chambre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849559B177F54');
        $this->addSql('DROP INDEX IDX_42C84955A76ED395 ON reservation');
        $this->addSql('DROP INDEX IDX_42C849559B177F54 ON reservation');
        $this->addSql('ALTER TABLE reservation CHANGE user_id user_id_id INT NOT NULL, CHANGE chambre_id chambre_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849559D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849552680A339 FOREIGN KEY (chambre_id_id) REFERENCES chambre (id)');
        $this->addSql('CREATE INDEX IDX_42C849559D86650F ON reservation (user_id_id)');
        $this->addSql('CREATE INDEX IDX_42C849552680A339 ON reservation (chambre_id_id)');
    }
}
