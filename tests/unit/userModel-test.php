<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/User.php';

class UserModelTest extends TestCase {
    private $conn;
    private $user;
    
    protected function setUp(): void {
        $this->conn = $GLOBALS['conn'];
        $this->user = new User($this->conn);
    }
    
    /**
     * @test
     * Тест получения всех пользователей
     */
    public function it_should_get_all_users() {
        // Act
        $users = $this->user->getAll();
        
        // Assert
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
        $this->assertArrayHasKey('email', $users[0]);
        $this->assertArrayHasKey('name', $users[0]);
        $this->assertArrayHasKey('art', $users[0]);
    }
    
    /**
     * @test
     * Тест поиска пользователя по email
     */
    public function it_should_find_user_by_email() {
        // Act
        $result = $this->user->getByEmail('admin@test.com');
        
        // Assert
        $this->assertTrue($result);
        $this->assertEquals('Admin', $this->user->name);
        $this->assertEquals('Test', $this->user->vorname);
        $this->assertEquals('Admin', $this->user->art);
    }
    
    /**
     * @test
     * Тест поиска несуществующего пользователя
     */
    public function it_should_return_false_for_nonexistent_email() {
        // Act
        $result = $this->user->getByEmail('nonexistent@test.com');
        
        // Assert
        $this->assertFalse($result);
    }
    
    /**
     * @test
     * Тест создания пользователя с PayloadHelper
     */
    public function it_should_create_user_with_payload_helper() {
        // Arrange
        $payload = PayloadHelper::createUser([
            'name' => 'TestCreate',
            'email' => 'testcreate@test.com'
        ]);
        
        $this->user->name = $payload['name'];
        $this->user->vorname = $payload['vorname'];
        $this->user->email = $payload['email'];
        $this->user->username = $payload['username'];
        $this->user->passwort = $payload['password'];
        $this->user->art = $payload['art'];
        
        // Act
        $result = $this->user->post();
        
        // Assert
        $this->assertTrue($result);
        $this->assertNotNull($this->user->id);
        
        // Проверка, что пользователь действительно создан
        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getByEmail('testcreate@test.com'));
        $this->assertEquals('TestCreate', $checkUser->name);
    }
    
    /**
     * @test
     * Тест создания множественных пользователей
     */
    public function it_should_create_multiple_users() {
        // Arrange & Act
        $createdIds = [];
        
        for ($i = 0; $i < 5; $i++) {
            $payload = PayloadHelper::createUser();
            
            $user = new User($this->conn);
            $user->name = $payload['name'];
            $user->vorname = $payload['vorname'];
            $user->email = $payload['email'];
            $user->username = $payload['username'];
            $user->passwort = $payload['password'];
            $user->art = $payload['art'];
            
            $user->post();
            $createdIds[] = $user->id;
        }
        
        // Assert
        $this->assertCount(5, $createdIds);
        $this->assertCount(5, array_unique($createdIds)); // Все ID уникальны
    }
    
    /**
     * @test
     * Тест хеширования пароля при создании
     */
    public function it_should_hash_password_on_create() {
        // Arrange
        $payload = PayloadHelper::createUser([
            'email' => 'hashtest@test.com',
            'password' => 'plainpassword123'
        ]);
        
        $this->user->name = $payload['name'];
        $this->user->vorname = $payload['vorname'];
        $this->user->email = $payload['email'];
        $this->user->username = $payload['username'];
        $this->user->passwort = $payload['password'];
        $this->user->art = $payload['art'];
        
        // Act
        $this->user->post();
        
        // Assert
        $this->user->getByEmail('hashtest@test.com');
        $this->assertStringStartsWith('$2y$', $this->user->passwort);
        $this->assertTrue(password_verify('plainpassword123', $this->user->passwort));
    }
    
    /**
     * @test
     * Тест обновления пользователя
     */
    public function it_should_update_user() {
        // Arrange
        $this->user->getByEmail('admin@test.com');
        $userId = $this->user->id;
        
        // Act
        $this->user->vorname = 'UpdatedName';
        $this->user->art = 'Lehrer';
        $result = $this->user->update();
        
        // Assert
        $this->assertTrue($result);
        
        // Проверка обновления
        $checkUser = new User($this->conn);
        $checkUser->getById($userId);
        $this->assertEquals('UpdatedName', $checkUser->vorname);
        $this->assertEquals('Lehrer', $checkUser->art);
    }
    
    /**
     * @test
     * Тест получения пользователя по ID
     */
    public function it_should_get_user_by_id() {
        // Arrange
        $this->user->getByEmail('admin@test.com');
        $userId = $this->user->id;
        
        // Act
        $newUser = new User($this->conn);
        $result = $newUser->getById($userId);
        
        // Assert
        $this->assertTrue($result);
        $this->assertEquals('admin@test.com', $newUser->email);
    }
    
    /**
     * @test
     * Тест метода toArray
     */
    public function it_should_convert_user_to_array() {
        // Arrange
        $this->user->getByEmail('admin@test.com');
        
        // Act
        $array = $this->user->toArray();
        
        // Assert
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('art', $array);
        $this->assertArrayNotHasKey('passwort', $array); // Пароль не должен быть в массиве
    }
}