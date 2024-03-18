<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240316051220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TABLE "exchange_rate"
(
    "from" CHAR(3)          NOT NULL,
    "to"   CHAR(3)          NOT NULL,
    "date" DATE             NOT NULL,
    "rate" DOUBLE PRECISION NOT NULL,
    PRIMARY KEY ("from", "to", "date")
)
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
COMMENT ON COLUMN "exchange_rate"."date" IS '(DC2Type:date_immutable)'
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
COMMENT ON COLUMN "exchange_rate"."rate" IS '(DC2Type:float)'
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP TABLE "exchange_rate"
SQL;
        $this->addSql($sql);
    }
}
