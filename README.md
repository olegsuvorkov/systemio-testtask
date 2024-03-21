# Инициализация

1. Запускаем контейнер
```shell
docker-compose up -d
```

2. Устанавливаем зависимости
```shell
docker-compose exec app composer install
```

3. Применяем миграции
```shell
docker-compose exec app bin/console doctrine:migrations:migrate -n
```

4. Загружаем фикстуры
```
docker-compose exec app bin/console doctrine:fixtures:load --purge-with-truncate --quiet
```

# Unit-тесты

1. Создаем базу данных для тестового окружения
```shell
docker-compose exec app bin/console --env=test doctrine:database:create --if-not-exists
```

2. Применяем миграции для тестового окружения
```shell
docker-compose exec app bin/console --env=test doctrine:migrations:migrate -n
```

3. Запускаем тесты
```shell
docker-compose exec app bin/phpunit
```

# Ручное тестирование

## Рассчитать цену продукта (Успешный)
```shell
curl --location 'http://localhost/calculate-price' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 1,
    "taxNumber": "DE123456789",
    "couponCode": "47Pdsyze"
}'
```

## Рассчитать цену продукта (Не валидные данные)
```shell
curl --location 'http://localhost/calculate-price' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 1000,
    "taxNumber": "DE12345678",
    "couponCode": "47Pdsyze23"
}'
```

## Рассчитать цену продукта (Параллельное выполнение)

Проверка функционала блокировки параллельного запроса одного и того же курса валют

```shell
curl --location 'http://localhost/calculate-price' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 1,
    "taxNumber": "DE123456789",
    "couponCode": "5V26kJtK"
}' &
curl --location 'http://localhost/calculate-price' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 2,
    "taxNumber": "DE123456789",
    "couponCode": "5V26kJtK"
}' &
```

## Оплата (Успешный)

```shell
curl --location 'http://localhost/purchase' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 1,
    "taxNumber": "DE123456789",
    "couponCode": "kzYiyAXw",
    "paymentProcessor": "paypal"
}'
```

## Оплата (С ошибкой "Не удалось оплатить")

```shell
curl --location 'http://localhost/purchase' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 2,
    "taxNumber": "DE123456789",
    "couponCode": "kzYiyAXw",
    "paymentProcessor": "stripe"
}'
```

## Оплата (С ошибками валидации)

```shell
curl --location 'http://localhost/purchase' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "product": 4,
    "couponCode": "kzYiyAXw",
    "paymentProcessor": "paypal2"
}'
```