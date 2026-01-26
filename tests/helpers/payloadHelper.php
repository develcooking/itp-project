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
    
    public static function createUser(array $overrides = []) {
        $faker = self::getFaker();
        
        $defaults = [
            'userName' => $faker->lastName(),
            'firstName' => $faker->firstName(),
            'lastName' => $faker->unique()->safeEmail(),
            'email' => $faker->unique()->userName(),
            'password' => 'Password123!',
            'role' => $faker->randomElement(['Lehrer', 'Ausbilder']),
            'securityAnswer' => $faker->lastName(),
            'activated' => $faker->randomElement([0,1]),
            'createdBy' => null,
            'modifiedBy' => null
        ];
        
        return array_merge($defaults, $overrides);
    }
}