<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class PayloadHelper {
    private static $faker;
    
    private static function getFaker() {
        if (!self::$faker) {
            self::$faker = Faker\Factory::create('de_DE');
        }
        return self::$faker;
    }
    
    public static function createUser(array $overrides = []): array {
        $faker = self::getFaker();
        
        $defaults = [
            'name' => $faker->lastName(),
            'vorname' => $faker->firstName(),
            'email' => $faker->unique()->safeEmail(),
            'username' => $faker->unique()->userName(),
            'password' => 'Password123!',
            'art' => $faker->randomElement(['Lehrer', 'Ausbilder', 'Admin'])
        ];
        
        return array_merge($defaults, $overrides);
    }
}