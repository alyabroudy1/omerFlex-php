<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231002124659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movie (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, state SMALLINT DEFAULT NULL, card_image VARCHAR(255) DEFAULT NULL, background_image VARCHAR(255) DEFAULT NULL, rate VARCHAR(20) DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, total_time INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movie_movie_source (movie_id INT NOT NULL, movie_source_id INT NOT NULL, INDEX IDX_85BB03358F93B6FC (movie_id), INDEX IDX_85BB03356E91567 (movie_source_id), PRIMARY KEY(movie_id, movie_source_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movie_source (id INT AUTO_INCREMENT NOT NULL, server_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, active TINYINT(1) DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, INDEX IDX_A64DE2061844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE server (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, headers LONGTEXT DEFAULT NULL, cookie LONGTEXT DEFAULT NULL, web_address VARCHAR(255) DEFAULT NULL, rate SMALLINT DEFAULT NULL, active TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE movie_movie_source ADD CONSTRAINT FK_85BB03358F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_movie_source ADD CONSTRAINT FK_85BB03356E91567 FOREIGN KEY (movie_source_id) REFERENCES movie_source (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE movie_source ADD CONSTRAINT FK_A64DE2061844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie_movie_source DROP FOREIGN KEY FK_85BB03358F93B6FC');
        $this->addSql('ALTER TABLE movie_movie_source DROP FOREIGN KEY FK_85BB03356E91567');
        $this->addSql('ALTER TABLE movie_source DROP FOREIGN KEY FK_A64DE2061844E6B7');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE movie_movie_source');
        $this->addSql('DROP TABLE movie_source');
        $this->addSql('DROP TABLE server');
    }
}
