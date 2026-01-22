<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/helpers/payloadHelper.php';

require_once __DIR__ . '/../database/seeders/benutzerSeeder.php';
$seeder = new BenutzerSeeder($conn);
$seeder->run();

$GLOBALS['conn'] = $conn;