<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/User.php';

class UserModelTest extends TestCase
{
    private $conn;
    private $user;

    protected function setUp(): void
    {
        $this->conn = $GLOBALS['conn'];
        $this->user = new User($this->conn);
    }

    public function test_It_Should_Get_All_Users(): void
    {
        $users = $this->user->getAll();
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
        $this->assertArrayHasKey('email', $users[0]);
        $this->assertArrayHasKey('userName', $users[0]);
        $this->assertArrayHasKey('firstName', $users[0]);
        $this->assertArrayHasKey('lastName', $users[0]);
        $this->assertArrayHasKey('role', $users[0]);
    }

    public function test_It_Should_Find_User_By_Email(): void
    {
        $payload = PayloadHelper::createUser([
            'email' => 'adminTest_' . uniqid() . '@test.com',
            'activated' => 1,
            'role' => 'Admin',
        ]);

        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());
        $result = $this->user->getByEmail($payload['email']);
        $this->assertTrue($result);
        $this->assertEquals($payload['email'], $this->user->getEmail());
    }

    public function test_It_Should_Return_False_For_Nonexistent_Email(): void
    {
        $result = $this->user->getByEmail('nonexistent@test.com');
        $this->assertFalse($result);
    }

    public function test_It_Should_Create_User_With_PayloadHelper(): void
    {
        $payload = PayloadHelper::createUser();
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $result = $user->post();
        $this->assertTrue($result);
        $this->assertNotNull($user->getUserId());
        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getByEmail($payload['email']));
        $this->assertEquals($payload['email'], $checkUser->getEmail());
        $this->assertEquals($payload['userName'], $checkUser->getUserName());
    }

    public function test_It_Should_Create_Multiple_Users()
    {
        $createdIds = [];
        for ($i = 0; $i < 5; $i++) {
            $payload = PayloadHelper::createUser();
            $user = new User($this->conn);
            $user->setUserName($payload['userName']);
            $user->setFirstName($payload['firstName']);
            $user->setLastName($payload['lastName']);
            $user->setEmail($payload['email']);
            $user->setPassword($payload['password']);
            $user->setRole($payload['role']);
            $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
            $user->setActivated($payload['activated']);
            $user->setCreatedBy(null);
            $user->setModifiedBy(null);
            $user->post();
            $createdIds[] = $user->getUserId();
        }
        $this->assertCount(5, $createdIds);
        $this->assertCount(5, array_unique($createdIds)); // Все ID уникальны
    }

    public function test_It_Should_Hash_Password_On_Create()
    {
        $payload = PayloadHelper::createUser();
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $user->post();
        $this->user->getByEmail($payload['email']);
        $this->assertStringStartsWith('$2y$', $this->user->getPassword());
        $this->assertTrue(password_verify($payload['password'], $this->user->getPassword()));
    }

    public function test_It_Should_Update_UserName(): void
    {
        $payload = PayloadHelper::createUser(['activated' => 1]);
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());
        $userId = $user->getUserId();
        $this->assertNotNull($userId);
        $newUserName = 'newUserNameTest' . uniqid();
        $userToUpdate = new User($this->conn);
        $this->assertTrue($userToUpdate->getById($userId));
        $userToUpdate->setUserName($newUserName);
        $userToUpdate->setFirstName($payload['firstName']);
        $userToUpdate->setLastName($payload['lastName']);
        $userToUpdate->setEmail($payload['email']);
        $userToUpdate->setPassword($payload['password']);
        $userToUpdate->setRole($payload['role']);
        $userToUpdate->setSecurityAnswer($userToUpdate->getSecurityAnswer() ?? password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $userToUpdate->setActivated($payload['activated']);
        $this->assertTrue($userToUpdate->update($userId));
        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getById($userId));
        $this->assertEquals($newUserName, $checkUser->getUserName());
    }

    public function test_It_Should_Get_User_By_Id()
    {
        $payload = PayloadHelper::createUser(['activated' => 1]);
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());
        $userId = $user->getUserId();
        $result = $user->getById($userId);
        $this->assertTrue($result);
        $this->assertEquals($payload['email'], $user->getEmail());
    }

    public function test_It_Should_Delete_User_By_Id(){
        $payload = PayloadHelper::createUser(['activated' => 1]);
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());
        $userId = $user->getUserId();
        $result = $user->delete($userId);
        $this->assertTrue($result);
    }

    public function test_It_Should_Convert_User_To_Array() {
        $payload = PayloadHelper::createUser(['activated' => 1]);
        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());
        $this->user->getByEmail($payload['email']);
        $array = $this->user->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('userId', $array);
        $this->assertArrayHasKey('userName', $array);
        $this->assertArrayHasKey('firstName', $array);
        $this->assertArrayHasKey('lastName', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayHasKey('role', $array);
        $this->assertArrayNotHasKey('securityAnswer', $array);
        $this->assertArrayHasKey('activated', $array);
    }

//-> da frage ich den Lehrer, ob es alles so tief getestet werden soll...
//    public function test_It_Should_Return_An_Error_On_Delete_If_The_User_Does_Not_Exist(){}
//    public function test_It_Should_Return_An_Error_On_Update_If_The_User_Does_Exist(){}
//    public function test_It_Should_Return_An_Error_On_Get_By_Id_If_The_User_Does_Exist(){}
//    public function test_It_Should_Return_An_Error_On_Post_If_User_Email_already_exists(){}
//    public function test_It_Should_Not_Return_Password_Field_On_Get_By_Id(){}
//    public function test_It_Should_Not_Return_SecurityAnswer_Field_On_Get_By_Id(){}
//    public function test_It_Should_Successfully_Update_UserName_Field(){}
//    public function test_It_Should_Successfully_Update_FirstName_Field(){}
//    public function test_It_Should_Successfully_Update_LastName_Field(){}
//    public function test_It_Should_Successfully_Update_Email_Field(){}
//    public function test_It_Should_Successfully_Update_Password_Field(){}
//    public function test_It_Should_Successfully_Update_Role_Field(){}
//    public function test_It_Should_Successfully_Update_SecurityAnswer_Field(){}
}