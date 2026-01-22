<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class UsersSeeder
{
    private $conn;
    private $faker;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->faker = Faker\Factory::create('de_DE');
    }

    public function run()
    {
        $this->conn->query("DELETE FROM Users");
        $this->conn->query("ALTER TABLE Users AUTO_INCREMENT = 1");

        $fixedUsers = [
            [
                'userName' => 'admin',
                'firstName' => 'Test',
                'lastName' => 'Admin',
                'email' => 'admin@test.com',
                'password' => 'password123',
                'role' => 'Admin',
                'securityAnswer' => 'test_answer',
                'activated' => 1
            ],
            [
                'userName' => 'mmueller',
                'firstName' => 'Max',
                'lastName' => 'Müller',
                'email' => 'max.mueller@test.com',
                'password' => 'password123',
                'role' => 'Lehrer',
                'securityAnswer' => 'test_answer1',
                'activated' => 1
            ],
            [
                'userName' => 'aschmidt',
                'firstName' => 'Anna',
                'lastName' => 'Schmidt',
                'email' => 'anna.schmidt@test.com',
                'password' => 'password123',
                'role' => 'Ausbilder',
                'securityAnswer' => 'test_answer2',
                'activated' => 1
            ]
        ];

        $randomUsers = $this->generateRandomUsers(100);

        $allUsers = array_merge($fixedUsers, $randomUsers);

        $stmt = $this->conn->prepare(
            "INSERT INTO Users (userName, firstName, lastName, email, password, role, securityAnswer, activated, createdBy, modifiedBy) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($allUsers as $user) {
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            $hashedSecurityAnswer = password_hash($user['securityAnswer'], PASSWORD_DEFAULT);
            $createdBy = null;
            $modifiedBy = null;

            $stmt->bind_param(
                "ssssssisii",
                $user['userName'],
                $user['firstName'],
                $user['lastName'],
                $user['email'],
                $hashedPassword,
                $user['role'],
                $hashedSecurityAnswer,
                $user['activated'],
                $createdBy,
                $modifiedBy
            );
            $stmt->execute();
        }

        $stmt->close();

        echo "Seeded " . count($allUsers) . " users (3 fixed + 100 random)\n";
    }

    private function generateRandomUsers(int $count): array
    {
        $users = [];
        $roles = ['Lehrer', 'Ausbilder', 'Admin'];

        for ($i = 1; $i <= $count; $i++) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName();

            $users[] = [
                'userName' => $this->faker->unique()->userName(),
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $this->faker->unique()->safeEmail(),
                'password' => 'password123',
                'role' => $this->faker->randomElement($roles),
                'securityAnswer' => $this->faker->word(),
                'activated' => $this->faker->randomElement([0, 1])
            ];
        }

        return $users;
    }
}