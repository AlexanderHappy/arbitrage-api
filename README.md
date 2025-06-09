# План разработки Crypto Arbitrage API

## 1. Обзор проекта

### Цель

Разработка Laravel API для поиска арбитражных возможностей между криптовалютными биржами с фокусом на изучение
продвинутых паттернов и технологий.

### Ключевые технологии

- Laravel 12
- PHP 8.3 с атрибутами
- Redis (кэширование, очереди), Horizon
- Scheduler
- Laravel Telescope
- Guzzle HTTP
- Telegram Bot API
- Паттерны: Repository, Strategy, Builder, Iterator, Observer, Factory, Service Layer, DTO

### MVP функционал

- Интеграция с 3 биржами (Binance, Kraken, Coinbase)
- Поиск арбитражных возможностей
- Расчет прибыли с учетом всех комиссий
- Telegram и Email уведомления
- REST API с аутентификацией
- Система мониторинга и логирования

## 2. Архитектура проекта

### Структура директорий

```
app/
├── Attributes/          # PHP 8 атрибуты
│   ├── RateLimit.php
│   └── CacheKey.php
├── Builders/           # Builder pattern
│   ├── ArbitrageSearchBuilder.php
│   └── NotificationBuilder.php
├── Collections/        # Custom collections с Iterator
│   └── ArbitrageOpportunityCollection.php
├── Interfaces/          # Интерфейсы
│   ├── ExchangeStrategyInterface.php
│   ├── NotificationChannelInterface.php
│   ├── ArbitrageRepositoryInterface.php
│   └── CacheRepositoryInterface.php
├── DTOs/              # Data Transfer Objects
│   ├── PriceData.php
│   ├── ArbitrageOpportunity.php
│   └── NotificationData.php
├── Enums/             # PHP Enums
│   ├── ExchangeStatus.php
│   ├── NotificationChannel.php
│   └── ArbitrageStatus.php
├── Events/            # События системы
│   ├── ArbitrageFound.php
│   ├── ExchangeOffline.php
│   └── PriceUpdated.php
├── Exceptions/        # Кастомные исключения
│   ├── ExchangeApiException.php
│   ├── ArbitrageCalculationException.php
│   └── NotificationDeliveryException.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── ArbitrageController.php
│   │   │   ├── ExchangeController.php
│   │   │   └── NotificationController.php
│   │   └── Controller.php
│   ├── Middleware/
│   │   ├── RateLimitMiddleware.php
│   │   └── ApiKeyMiddleware.php
│   ├── Requests/
│   │   ├── ArbitrageSearchRequest.php
│   │   └── NotificationSettingsRequest.php
│   └── Resources/
│       ├── ArbitrageResource.php
│       └── ExchangeResource.php
├── Jobs/              # RabbitMQ jobs
│   ├── ScanArbitrageJob.php
│   ├── StorePriceSnapshotJob.php
│   ├── SendNotificationJob.php
│   └── HealthCheckExchangeJob.php
├── Listeners/         # Обработчики событий
│   ├── SendArbitrageNotification.php
│   ├── LogExchangeStatus.php
│   └── CachePriceUpdate.php
├── Models/
│   ├── User.php
│   ├── Exchange.php
│   ├── ArbitrageOpportunity.php
│   ├── PriceSnapshot.php
│   ├── UserNotification.php
│   ├── NotificationLog.php
│   └── ExchangeHealthCheck.php
├── Observers/         # Model observers
│   └── ArbitrageOpportunityObserver.php
├── Policies/          # Авторизация
│   └── ArbitragePolicy.php
├── Repositories/      # Repository pattern
│   ├── ArbitrageRepository.php
│   ├── ExchangeRepository.php
│   ├── PriceRepository.php
│   └── CacheRepository.php
├── Services/          # Бизнес-логика
│   ├── Exchange/      # Strategy pattern для бирж
│   │   ├── ExchangeService.php
│   │   ├── BinanceStrategy.php
│   │   ├── KrakenStrategy.php
│   │   └── CoinbaseStrategy.php
│   ├── Arbitrage/     # Поиск возможностей
│   │   ├── ArbitrageScanner.php
│   │   ├── ProfitCalculator.php
│   │   └── FeeCalculator.php
│   ├── Notification/  # Observer pattern
│   │   ├── NotificationService.php
│   │   ├── TelegramChannel.php
│   │   └── EmailChannel.php
│   └── Cache/         # Redis кэширование
│       ├── PriceCacheService.php
│       └── ArbitrageCacheService.php
├── Strategies/        # Exchange strategies
│   ├── AbstractExchangeStrategy.php
│   └── ExchangeStrategyFactory.php
└── ValueObjects/      # Immutable objects
    ├── Price.php
    ├── TradingPair.php
    └── ProfitMargin.php

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2024_01_01_000003_create_exchanges_table.php
│   ├── 2024_01_01_000004_create_arbitrage_opportunities_table.php
│   ├── 2024_01_01_000005_create_price_snapshots_table.php
│   ├── 2024_01_01_000006_create_user_notifications_table.php
│   ├── 2024_01_01_000007_create_notification_logs_table.php
│   └── 2024_01_01_000008_create_exchange_health_checks_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── ExchangeSeeder.php
│   └── UserSeeder.php
└── factories/
    ├── UserFactory.php
    ├── ExchangeFactory.php
    └── ArbitrageOpportunityFactory.php
    
    
tests/
├── Feature/
│   ├── ArbitrageApiTest.php
│   ├── ExchangeIntegrationTest.php
│   └── NotificationTest.php
└── Unit/
    ├── Services/
    │   ├── ArbitrageScannerTest.php
    │   └── ProfitCalculatorTest.php
    └── Strategies/
        └── BinanceStrategyTest.php
```

## 3. База данных

### Основные таблицы

1. **users** - пользователи системы
2. **exchanges** - биржи и их конфигурация
   exchanges:
   - id
     - name (KuCoin, Gate)
     - api_url (https://api.binance.com/api/v3)
     - api_key (ваш ключ)
     - api_secret (секретный ключ)
     - is_active (включена/выключена)
     - rate_limit (макс запросов в минуту)
     - trading_fee (комиссия торговли в %)
     - withdrawal_fee (комиссия вывода)
     - min_trade_amount (минимальная сумма сделки)
     - supported_pairs (JSON: ["BTC/USDT", "ETH/USDT"])
     - last_health_check (когда проверяли доступность)
     - created_at/updated_at
3. **arbitrage_opportunities** - найденные возможности
   arbitrage_opportunities:
   - id
     - pair (BTC/USDT)
     - buy_exchange_id (где покупать) (foreignKey -> exchanges.id)
     - sell_exchange_id (где продавать) (foreignKey -> exchanges.id)
     - buy_price (цена покупки)
     - sell_price (цена продажи)
     - spread_amount (разница в цене)
     - spread_percentage (% прибыли)
     - profit_after_fees (чистая прибыль)
     - volume (объем для сделки)
     - total_fees (общие комиссии)
     - status (active/expired/executed)
     - expires_at (когда истекает)
     - created_at
4. **price_snapshots** - исторические данные цен
   price_snapshots:
   - id
     - exchange_id (foreign key -> exchanges.id)
     - pair (BTC/USDT)
     - price (цена на момент снимка)
     - volume_24h (объем торгов за 24ч)
     - timestamp (когда зафиксировали)
     - created_at    
5. **user_notifications** - настройки уведомлений (!ПОЛИМОРФНАЯ СВЯЗЬ)
   user_notifications:
   - id
     - user_id (foreign key -> users.id)
     - min_profit_threshold (минимальная прибыль для уведомления)
     - notification_channels (JSON: ["email", "telegram"])
     - pairs (JSON: ["BTC/USDT", "ETH/USDT"])
     - exchanges (JSON: [1, 2, 3] - ID бирж)
     - is_active (включены/выключены)
     - telegram_chat_id (для Telegram Bot)
     - created_at/updated_at
6. **notification_logs** - история отправленных уведомлений
   notification_logs:
   - id
     - user_id (foreign key -> users.id)
     - arbitrage_opportunity_id (foreign key -> arbitrage_opportunities.id)
     - channel (email/telegram)
     - status (sent/failed/pending)
     - sent_at
     - error_message (если failed)
     - created_at
7. **exchange_health_checks** - мониторинг доступности бирж
   exchange_health_checks:
   - id
     - exchange_id (foreign key -> exchanges.id)
     - status (online/offline/slow)
     - response_time_ms
     - last_error
     - checked_at
     - created_at    

## 4. Используемые паттерны

### Repository Pattern

Для абстракции работы с данными и возможности смены источника данных.

### Strategy Pattern

Для реализации различных стратегий работы с биржами.

### Builder Pattern

Для построения сложных объектов поиска и уведомлений.

### Iterator Pattern

Для удобной итерации по коллекциям арбитражных возможностей.

### Observer Pattern

Через Laravel Events для системы уведомлений.

### Service Layer

Для инкапсуляции бизнес-логики.

### DTO Pattern

Для типизированной передачи данных между слоями.

### Factory Pattern

Для создания экземпляров стратегий бирж.

## 5. Технические требования

### Минимальные требования

- PHP 8.2+
- Laravel 10+
- Redis 6+
- MySQL 8+ или PostgreSQL 13+
- Composer 2+

### Рекомендуемое окружение

- Docker с WSL2
- PhpStorm или VS Code
- TablePlus для работы с БД
- Postman для тестирования API

## 6. Дальнейшее развитие

### После MVP

- Добавление новых бирж
- WebSocket для real-time данных
- AI анализ через Claude API
- Мобильное приложение
- Автоматическое исполнение сделок

## Заключение

Проект разработан с фокусом на изучение продвинутых возможностей Laravel и паттернов проектирования. MVP может быть
завершен за 6 недель при работе 6-8 часов в день. Архитектура позволяет легко расширять функционал после завершения
основной разработки.
