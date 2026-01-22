<?php

// Автозагрузка Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Загрузка .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Подключение к БД
require_once __DIR__ . '/../database/db.php';

// Загрузка helpers
require_once __DIR__ . '/helpers/payloadHelper.php';

// Запуск seeders перед тестами
require_once __DIR__ . '/../database/seeders/benutzerSeeder.php';
$seeder = new BenutzerSeeder($conn);
$seeder->run();

// Глобальная переменная для тестов
$GLOBALS['conn'] = $conn;