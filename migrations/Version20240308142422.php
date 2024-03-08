<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308142422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Структура данных для "Product"';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE SEQUENCE "product_id_seq" INCREMENT BY 1 MINVALUE 1 START 1
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE "product"
(
    "id"       INT          NOT NULL,
    "name"     VARCHAR(255) NOT NULL,
    "price"    FLOAT        NOT NULL CHECK ("price" > 0.0),
    "currency" CHAR(3)      NOT NULL,
    PRIMARY KEY ("id")
)
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP SEQUENCE "product_id_seq" CASCADE
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
DROP TABLE "product"
SQL;
        $this->addSql($sql);
    }
}
