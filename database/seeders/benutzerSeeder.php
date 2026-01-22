<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class BenutzerSeeder {
    private $conn;
    private $faker;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->faker = Faker\Factory::create('de_DE');
    }
    
    public function run() {
        $this->conn->query("DELETE FROM Benutzer");
        $this->conn->query("ALTER TABLE Benutzer AUTO_INCREMENT = 1");
        
        $fixedUsers = [
            [
                'name' => 'Admin',
                'vorname' => 'Test',
                'email' => 'admin@test.com',
                'username' => 'admin',
                'password' => 'password123',
                'art' => 'Admin'
            ],
            [
                'name' => 'Müller',
                'vorname' => 'Max',
                'email' => 'max.mueller@test.com',
                'username' => 'mmueller',
                'password' => 'password123',
                'art' => 'Lehrer'
            ],
            [
                'name' => 'Schmidt',
                'vorname' => 'Anna',
                'email' => 'anna.schmidt@test.com',
                'username' => 'aschmidt',
                'password' => 'password123',
                'art' => 'Ausbilder'
            ]
        ];
        
        $randomUsers = $this->generateRandomUsers(100);
        
        $allUsers = array_merge($fixedUsers, $randomUsers);
        
        $stmt = $this->conn->prepare(
            "INSERT INTO Benutzer (name, vorname, email, username, passwort_hash, art) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($allUsers as $user) {
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            $stmt->bind_param(
                "ssssss",
                $user['name'],
                $user['vorname'],
                $user['email'],
                $user['username'],
                $hashedPassword,
                $user['art']
            );
            $stmt->execute();
        }
        
        $stmt->close();
        
        echo "Seeded " . count($allUsers) . " users (3 fixed + 50 random)\n";
    }
    
    private function generateRandomUsers(int $count): array {
        $users = [];
        $roles = ['Lehrer', 'Ausbilder', 'Admin'];
        
        for ($i = 1; $i <= $count; $i++) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName();
            
            $users[] = [
                'name' => $lastName,
                'vorname' => $firstName,
                'email' => $this->faker->unique()->safeEmail(),
                'username' => $this->faker->unique()->userName(),
                'password' => 'password123',
                'art' => $this->faker->randomElement($roles)
            ];
        }
        
        return $users;
    }
}