<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112094153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace the categories table with a category enum column on products, keeping each product\'s category.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD category VARCHAR(20) DEFAULT NULL');
        $this->addSql('UPDATE products p JOIN categories c ON c.category_id = p.category_id SET p.category = LOWER(c.name)');
        $this->addSql('ALTER TABLE products MODIFY category VARCHAR(20) NOT NULL');

        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `FK_B3BA5A5A12469DE2`');
        $this->addSql('DROP INDEX IDX_B3BA5A5A12469DE2 ON products');
        $this->addSql('ALTER TABLE products DROP category_id');
        $this->addSql('DROP TABLE categories');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE categories (category_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_3AF346685E237E06 (name), PRIMARY KEY (category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO categories (name) VALUES ('Workstation'), ('Gaming'), ('Personal')");
        $this->addSql('ALTER TABLE products ADD category_id INT DEFAULT NULL');
        $this->addSql('UPDATE products p JOIN categories c ON LOWER(c.name) = p.category SET p.category_id = c.category_id');
        $this->addSql('ALTER TABLE products MODIFY category_id INT NOT NULL, DROP category');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES categories (category_id)');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A12469DE2 ON products (category_id)');
    }
}
