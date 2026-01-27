<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/seeders/usersSeeder.php';

$command = $argv[1] ?? 'seed';

switch ($command) {
    case 'seed':
        echo "Running seeders...\n";
        $seeder = new usersSeeder($conn);
        $seeder->run();
        echo "Seeding complete!\n";
        break;
        
    default:
        echo "Usage: php setup.php [seed]\n";
        break;
}

$conn->close();