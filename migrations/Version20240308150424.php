<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308150424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Структура данных для "Coupon"';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE SEQUENCE "coupon_id_seq" INCREMENT BY 1 MINVALUE 1 START 1
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE "coupon"
(
    "id"       INT          NOT NULL,
    "code"     VARCHAR(32)  NOT NULL,
    "type"     VARCHAR(255) NOT NULL,
    "percent"  FLOAT        CHECK (("percent" > 0.0 AND "type" = 'percent') OR ("percent" IS NULL AND "type" <> 'percent')),
    "price"    FLOAT        CHECK (("price" > 0.0 AND "type" = 'fixed') OR ("price" IS NULL AND "type" <> 'fixed')),
    "currency" CHAR(3) DEFAULT NULL,
    PRIMARY KEY ("id")
)
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE UNIQUE INDEX "UNIQ_64BF3F0277153098" ON "coupon" ("code")
SQL;
        $this->addSql($sql);

    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP SEQUENCE "coupon_id_seq" CASCADE
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
DROP TABLE "coupon"
SQL;
        $this->addSql($sql);
    }
}
