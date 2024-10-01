# RabbitMQ vs Kafka: обрабатываем большие объёмы

Запускаем контейнеры командой `docker compose --profile main up -d`

## Первоначальная настройка

1. Заходим в контейнер `rabbit-mq` командой `docker exec -it rabbit-mq sh`
   1. выполняем в контейнере команду `rabbitmq-plugins enable rabbitmq_consistent_hash_exchange`
   2. выходим из контейнера
2. Заходим в контейнер `php` командой `docker exec -it php sh`
   1. устанавливаем зависимости командой `composer install`
   2. выполняем миграции командой `php bin/console doctrine:migrations:migrate -q`
   3. выходим из контейнера
3. Переходим в браузере по ссылке http://localhost:3000
   1. Логинимся с реквизитами `admin / admin`
   2. Добавляем Data source Graphite с реквизитами http://graphite:80
   3. Импортируем Dashboard из файла `docs/dashboard.json`

## Пример с масштабированием

1. Запускаем по одному консьюмеру для RabbitMQ и Kafka командой `docker compose --profile single up -d`
2. Выполняем запрос `1. Send messages for single consumer` из Postman-коллекции
3. Заходим в Grafana по адресу http://localhost:3000, настраиваем панель на базе файла `docs/panel.json`, сравниваем
   результаты
4. Останавливаем консьюмеры командой `docker compose --profile single down`
5. Запускаем по 4 консьюмера для RabbitMQ и Kafka командой `docker compose --profile scaled up -d`
6. Выполняем запрос `2. Send messages for concurrent consumers` из Postman-коллекции
7. Наблюдаем заметное проседание производительности у Kafka
8. Заходим в контейнер `kafka` командой `docker exec -it kafka sh`
   1. Выполняем команду `kafka-topics --bootstrap-server localhost:9092 --alter --topic scaling --partitions 4`
   2. выходим из контейнера
9. Ещё раз выполняем запрос `2. Send messages for concurrent consumers` из Postman-коллекции
10. Перезапускаем консьюмеры командами
   ```shell
   docker compose --profile scaled down
   docker compose --profile scaled up -d
   ```
11. Видим, что производительность в Kafka восстановлена, но появились проблемы с порядком обработки и даже
    Race condition

## Разделяем потоки сообщений

1. Останавливаем консьюмеры командой `docker compose --profile scaled down`
2. Заходим в контейнер `kafka` командой `docker exec -it kafka sh`
   1. Выполняем команду `kafka-topics --bootstrap-server localhost:9092 --create --topic distributed --partitions 4`
   2. выходим из контейнера
3. Запускаем по 4 консьюмера для RabbitMQ и Kafka командой `docker compose --profile distributed up -d`
4. Ещё раз выполняем запрос `3. Send messages for distributed consumers` из Postman-коллекции
5. Видим, что производительность не упала, зато ушли проблемы с порядком обработки.

## Согласованное хэширование в RabbitMQ

1. Останавливаем консьюмеры командой `docker compose --profile distributed down`
2. Заходим в контейнер `kafka` командой `docker exec -it kafka sh`
   1. Выполняем команду `kafka-topics --bootstrap-server localhost:9092 --create --topic hashed --partitions 4`
   2. выходим из контейнера
3. Запускаем по 4 консьюмера для RabbitMQ и Kafka командой `docker compose --profile hashed up -d`
4. Ещё раз выполняем запрос `4. Send messages for hashed consumers` из Postman-коллекции
5. Видим, что распределение между консьюмерами в RabbitMQ выполнено внутри него и оно не равномерное

## Согласованное хэширование с ребалансировкой в RabbitMQ

1. Останавливаем консьюмеры командой `docker compose --profile hashed down`
2. В интерфейсе RabbitMQ удаляем очереди с префиксом `hashed`
3. Запускаем по 4 консьюмера для RabbitMQ и Kafka командой `docker compose --profile hashed_balanced up -d`
4. Ещё раз выполняем запрос `4. Send messages for hashed consumers` из Postman-коллекции
5. Видим, что распределение между консьюмерами в RabbitMQ выполнено внутри него и оно не равномерное

## Перераспределение имеющихся в очереди сообщений

1. Останавливаем консьюмеры командой `docker compose --profile hashed_balanced down`
2. Запускаем перенаправляющий и по 4 обрабатывающих консьюмера для RabbitMQ и Kafka командой
   `docker compose --profile redistributed up -d`
3. Выполняем запрос `5. Send messages for redistribute between consumers` из Postman-коллекции
4. Видим, что распределение между консьюмерами в RabbitMQ выполнено внутри него и оно не равномерное
