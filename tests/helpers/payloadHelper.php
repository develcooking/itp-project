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
            'userName' => $faker->unique()->userName(),
            'firstName' => $faker->firstName(),
            'lastName' => $faker->lastName(),
            'email' => $faker->unique()->safeEmail(),
            'password' => 'Password123!',
            'role' => $faker->randomElement(['Lehrer', 'Ausbilder', 'Admin']),
            'securityAnswer' => $faker->word(),
            'activated' => $faker->randomElement([0, 1]),
            'createdBy' => null,
            'modifiedBy' => null
        ];

        return array_merge($defaults, $overrides);
    }

    public static function createJob(array $overrides = []) {
        $faker = self::getFaker();
            $defaults = [
                'name' => $faker->unique()->jobTitle() . ' ' . $faker->randomNumber(4),
                'createdBy' => 1,
                'modifiedBy' => 1
            ];
        return array_merge($defaults, $overrides);
    }


    public static function createUserJob(array $overrides = []) {
        $defaults = [
            'userId' => 1,
            'jobId' => 1,
            'createdBy' => 1
        ];

        return array_merge($defaults, $overrides);
    }
}