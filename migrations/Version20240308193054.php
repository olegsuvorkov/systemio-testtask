<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308193054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Структура данных для "Tax"';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE SEQUENCE "tax_id_seq" INCREMENT BY 1 MINVALUE 1 START 1
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE "tax"
(
    "id"           INT              NOT NULL,
    "country_code" CHAR(3)          NOT NULL,
    "format"       VARCHAR(64)      NOT NULL,
    "percent"      FLOAT            NOT NULL CHECK("percent" > 0.0),
    PRIMARY KEY ("id")
)
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE UNIQUE INDEX "UNIQ_8E81BA76F026BB7CDEBA72DF" ON "tax" ("country_code", "format")
SQL;
        $this->addSql($sql);

    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP SEQUENCE "tax_id_seq" CASCADE
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
DROP TABLE "tax"
SQL;
        $this->addSql($sql);
    }
}
