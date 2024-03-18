<?php declare(strict_types=1);

namespace App\Service\ExchangeRate\Store;

use App\Service\ExchangeRate\Exception\UnexpectedExchangeRateException;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[AsAlias]
#[AsDoctrineListener(ToolEvents::postGenerateSchema)]
readonly class PgSqlDatabaseStore implements StoreInterface
{
    public function __construct(
        #[Target('doctrine.dbal.default_connection')]
        private Connection $connection,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function load(string $from, array $to, DateTimeInterface $date): array
    {
        $rates = array_fill_keys($to, null);
        $expr = $this->connection->createExpressionBuilder();
        $data = $this->connection->createQueryBuilder()
            ->select('"to"', '"rate"')
            ->from('"exchange_rate"')
            ->where(
                $expr->eq('"from"', ':from'),
                $expr->in('"to"', ':to'),
                $expr->eq('"date"', ':date')
            )
            ->setParameter('from', $from, Types::STRING)
            ->setParameter('to', $to, ArrayParameterType::STRING)
            ->setParameter('date', $date, Types::DATE_IMMUTABLE)
            ->fetchAllKeyValue();
        $data = array_map(floatval(...), $data);
        $rates = [...$rates, ...$data];
        UnexpectedExchangeRateException::throwIfExistUnexpected($rates);
        return $rates;
    }

    /**
     * @inheritDoc
     */
    public function save(string $from, DateTimeInterface $date, array $rates): void
    {
        $values = [];
        foreach ($rates as $to => $rate) {
            $values[] = implode(', ', [
                $this->connection->quote($from, Types::STRING),
                $this->connection->quote($to, Types::STRING),
                $this->connection->quote($date, Types::DATE_IMMUTABLE),
                $this->connection->quote($rate, Types::FLOAT),
            ]);
        }
        $values = '('.implode('), (', $values).')';
        $sql = <<<SQL
INSERT INTO "exchange_rate" ("from", "to", "date", "rate")
VALUES {$values}
ON CONFLICT ("from", "to", "date") DO UPDATE
    SET "rate" = EXCLUDED."rate"
SQL;
        $this->connection->executeStatement($sql);
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {
        $schema = $event->getSchema();
        $table = $schema->createTable('exchange_rate');
        $table->addColumn('from', Types::STRING, [
            'length' => 3,
            'fixed' => true,
        ]);
        $table->addColumn('to', Types::STRING, [
            'length' => 3,
            'fixed' => true,
        ]);
        $table->addColumn('date', Types::DATE_IMMUTABLE);
        $table->addColumn('rate', Types::FLOAT);
        $table->setPrimaryKey(['from', 'to', 'date']);
    }

    public function clear(): void
    {
        $this->connection->executeStatement('DELETE FROM exchange_rate');
    }
}
