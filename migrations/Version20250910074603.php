<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250910074603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `member` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, address VARCHAR(255) NOT NULL, date_of_birth DATE NOT NULL, phone VARCHAR(20) NOT NULL, membership_status VARCHAR(16) NOT NULL, membership_expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membership_request (id INT AUTO_INCREMENT NOT NULL, validated_by_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, message LONGTEXT DEFAULT NULL, rgpd_accepted TINYINT(1) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', reminder_send_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B23A19CEC69DE5E5 (validated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(60) NOT NULL, label VARCHAR(120) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(30) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_permission (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_permission_role (role_permission_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_C9740D607128C459 (role_permission_id), INDEX IDX_C9740D60D60322AC (role_id), PRIMARY KEY(role_permission_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_permission_permission (role_permission_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_17EAC7F67128C459 (role_permission_id), INDEX IDX_17EAC7F6FED90CCA (permission_id), PRIMARY KEY(role_permission_id, permission_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE membership_request ADD CONSTRAINT FK_B23A19CEC69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES `member` (id)');
        $this->addSql('ALTER TABLE role_permission_role ADD CONSTRAINT FK_C9740D607128C459 FOREIGN KEY (role_permission_id) REFERENCES role_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_permission_role ADD CONSTRAINT FK_C9740D60D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_permission_permission ADD CONSTRAINT FK_17EAC7F67128C459 FOREIGN KEY (role_permission_id) REFERENCES role_permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_permission_permission ADD CONSTRAINT FK_17EAC7F6FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE membership_request DROP FOREIGN KEY FK_B23A19CEC69DE5E5');
        $this->addSql('ALTER TABLE role_permission_role DROP FOREIGN KEY FK_C9740D607128C459');
        $this->addSql('ALTER TABLE role_permission_role DROP FOREIGN KEY FK_C9740D60D60322AC');
        $this->addSql('ALTER TABLE role_permission_permission DROP FOREIGN KEY FK_17EAC7F67128C459');
        $this->addSql('ALTER TABLE role_permission_permission DROP FOREIGN KEY FK_17EAC7F6FED90CCA');
        $this->addSql('DROP TABLE `member`');
        $this->addSql('DROP TABLE membership_request');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_permission');
        $this->addSql('DROP TABLE role_permission_role');
        $this->addSql('DROP TABLE role_permission_permission');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
