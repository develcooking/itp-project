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
    public function testItShouldGetAllUsers(): void {
        $users = $this->user->getAll();
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
        $this->assertArrayHasKey('email', $users[0]);
        $this->assertArrayHasKey('userName', $users[0]);
        $this->assertArrayHasKey('firstName', $users[0]);
        $this->assertArrayHasKey('lastName', $users[0]);
        $this->assertArrayHasKey('role', $users[0]);
    }

    public function testItShouldFindUserByEmail(): void {
        $payload = PayloadHelper::createUser(['email' => 'adminTest@test.com']);
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setRole($payload['role']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());
        $result = $this->user->getByEmail('adminTest@test.com');
        $this->assertTrue($result);
        $this->assertEquals('adminTest@test.com', $this->user->getEmail());
    }
    public function testItShouldReturnFalseForNonexistentEmail():void {
        $result = $this->user->getByEmail('nonexistent@test.com');
        $this->assertFalse($result);
    }
//
//    public function it_should_create_user_with_payload_helper() {
//        $payload = PayloadHelper::createUser([
//            'name' => 'TestCreate',
//            'email' => 'testcreate@test.com'
//        ]);
//
//        $this->user->name = $payload['name'];
//        $this->user->vorname = $payload['vorname'];
//        $this->user->email = $payload['email'];
//        $this->user->username = $payload['username'];
//        $this->user->passwort = $payload['password'];
//        $this->user->art = $payload['art'];
//
//        $result = $this->user->post();
//
//        $this->assertTrue($result);
//        $this->assertNotNull($this->user->id);
//
//        $checkUser = new User($this->conn);
//        $this->assertTrue($checkUser->getByEmail('testcreate@test.com'));
//        $this->assertEquals('TestCreate', $checkUser->name);
//    }
//
//    public function it_should_create_multiple_users() {
//        $createdIds = [];
//
//        for ($i = 0; $i < 5; $i++) {
//            $payload = PayloadHelper::createUser();
//
//            $user = new User($this->conn);
//            $user->name = $payload['name'];
//            $user->vorname = $payload['vorname'];
//            $user->email = $payload['email'];
//            $user->username = $payload['username'];
//            $user->passwort = $payload['password'];
//            $user->art = $payload['art'];
//
//            $user->post();
//            $createdIds[] = $user->id;
//        }
//
//        $this->assertCount(5, $createdIds);
//        $this->assertCount(5, array_unique($createdIds)); // Все ID уникальны
//    }
//
//    public function it_should_hash_password_on_create() {
//        $payload = PayloadHelper::createUser([
//            'email' => 'hashtest@test.com',
//            'password' => 'plainpassword123'
//        ]);
//
//        $this->user->name = $payload['name'];
//        $this->user->vorname = $payload['vorname'];
//        $this->user->email = $payload['email'];
//        $this->user->username = $payload['username'];
//        $this->user->passwort = $payload['password'];
//        $this->user->art = $payload['art'];
//
//        $this->user->post();
//
//        $this->user->getByEmail('hashtest@test.com');
//        $this->assertStringStartsWith('$2y$', $this->user->passwort);
//        $this->assertTrue(password_verify('plainpassword123', $this->user->passwort));
//    }
//
//    public function it_should_update_user() {
//        $this->user->getByEmail('admin@test.com');
//        $userId = $this->user->id;
//        $this->user->vorname = 'UpdatedName';
//        $this->user->art = 'Lehrer';
//        $result = $this->user->update();
//        $this->assertTrue($result);
//        $checkUser = new User($this->conn);
//        $checkUser->getById($userId);
//        $this->assertEquals('UpdatedName', $checkUser->vorname);
//        $this->assertEquals('Lehrer', $checkUser->art);
//    }
//
//    public function it_should_get_user_by_id() {
//        $this->user->getByEmail('admin@test.com');
//        $userId = $this->user->id;
//        $newUser = new User($this->conn);
//        $result = $newUser->getById($userId);
//        $this->assertTrue($result);
//        $this->assertEquals('admin@test.com', $newUser->email);
//    }
//
//    public function it_should_convert_user_to_array() {
//        $this->user->getByEmail('admin@test.com');
//        $array = $this->user->toArray();
//        $this->assertIsArray($array);
//        $this->assertArrayHasKey('id', $array);
//        $this->assertArrayHasKey('email', $array);
//        $this->assertArrayHasKey('name', $array);
//        $this->assertArrayHasKey('art', $array);
//        $this->assertArrayNotHasKey('passwort', $array);
//    }
}