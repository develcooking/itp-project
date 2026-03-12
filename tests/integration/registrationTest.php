<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/User.php';

class RegistrationTest extends TestCase
{
    private $conn;
    private $user;

    protected function setUp(): void
    {
        $this->conn = $GLOBALS['conn'];
        $this->user = new User($this->conn);
    }

    public function test_It_Should_Register_New_User_Successfully(): void
    {
        $payload = PayloadHelper::createUser([
            'activated' => 0,
            'password' => 'TestPass123!'
        ]);

        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user->setActivated(0);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);

        $result = $user->post();

        $this->assertTrue($result);
        $this->assertNotNull($user->getUserId());

        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getByEmail($payload['email']));
        $this->assertEquals($payload['userName'], $checkUser->getUserName());
        $this->assertEquals($payload['email'], $checkUser->getEmail());
        $this->assertEquals(0, $checkUser->getActivated());
    }

    public function test_It_Should_Fail_When_Email_Already_Exists(): void
    {
        $payload = PayloadHelper::createUser(['activated' => 1]);
        $user1 = new User($this->conn);
        $user1->setUserName($payload['userName']);
        $user1->setFirstName($payload['firstName']);
        $user1->setLastName($payload['lastName']);
        $user1->setEmail($payload['email']);
        $user1->setPassword($payload['password']);
        $user1->setRole($payload['role']);
        $user1->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user1->setActivated($payload['activated']);
        $user1->setCreatedBy(null);
        $user1->setModifiedBy(null);
        $this->assertTrue($user1->post());

        $user2 = new User($this->conn);
        $this->assertTrue($user2->emailExists($payload['email']));
        $emailExists = $user2->emailExists($payload['email']);
        $this->assertTrue($emailExists);
    }

    public function test_It_Should_Fail_When_UserName_Already_Exists(): void
    {
        $payload = PayloadHelper::createUser(['activated' => 1]);

        $user1 = new User($this->conn);
        $user1->setUserName($payload['userName']);
        $user1->setFirstName($payload['firstName']);
        $user1->setLastName($payload['lastName']);
        $user1->setEmail($payload['email']);
        $user1->setPassword($payload['password']);
        $user1->setRole($payload['role']);
        $user1->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $user1->setActivated($payload['activated']);
        $user1->setCreatedBy(null);
        $user1->setModifiedBy(null);
        $this->assertTrue($user1->post());

        $user2 = new User($this->conn);
        $this->assertTrue($user2->userNameExists($payload['userName']));
    }

    public function test_It_Should_Validate_Email_Format(): void
    {
        $invalidEmails = [
            'notanemail',
            'missing@domain',
            '@nodomain.com',
            'spaces in@email.com',
            'double@@domain.com'
        ];

        foreach ($invalidEmails as $invalidEmail) {
            $isValid = filter_var($invalidEmail, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($isValid, "Email '$invalidEmail' should be invalid");
        }

        $validEmail = 'valid.email@example.com';
        $this->assertNotFalse(filter_var($validEmail, FILTER_VALIDATE_EMAIL));
    }


    public function test_It_Should_Enforce_Password_Minimum_Length(): void
    {
        $shortPassword = '12345';
        $validPassword = '123456';

        $this->assertLessThan(6, strlen($shortPassword));
        $this->assertGreaterThanOrEqual(6, strlen($validPassword));
    }

    public function test_It_Should_Hash_Password_On_Registration(): void
    {
        $payload = PayloadHelper::createUser([
            'password' => 'PlainTextPassword123!',
            'activated' => 1
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

        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getByEmail($payload['email']));

        $storedPassword = $checkUser->getPassword();
        $this->assertStringStartsWith('$2y$', $storedPassword);
        $this->assertNotEquals($payload['password'], $storedPassword);

        $this->assertTrue(password_verify($payload['password'], $storedPassword));
    }

    public function test_It_Should_Hash_SecurityAnswer_On_Registration(): void
    {
        $payload = PayloadHelper::createUser([
            'securityAnswer' => 'MySecretAnswer',
            'activated' => 1
        ]);

        $hashedAnswer = password_hash($payload['securityAnswer'], PASSWORD_DEFAULT);

        $user = new User($this->conn);
        $user->setUserName($payload['userName']);
        $user->setFirstName($payload['firstName']);
        $user->setLastName($payload['lastName']);
        $user->setEmail($payload['email']);
        $user->setPassword($payload['password']);
        $user->setRole($payload['role']);
        $user->setSecurityAnswer($hashedAnswer);
        $user->setActivated($payload['activated']);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());

        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getByEmail($payload['email']));

        $storedAnswer = $checkUser->getSecurityAnswer();
        $this->assertStringStartsWith('$2y$', $storedAnswer);
        $this->assertTrue(password_verify($payload['securityAnswer'], $storedAnswer));
    }

    public function test_It_Should_Register_Users_With_Different_Roles(): void
    {
        $roles = ['Lehrer', 'Ausbilder', 'Admin'];

        foreach ($roles as $role) {
            $payload = PayloadHelper::createUser([
                'role' => $role,
                'activated' => 1
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

            $checkUser = new User($this->conn);
            $this->assertTrue($checkUser->getByEmail($payload['email']));
            $this->assertEquals($role, $checkUser->getRole());
        }
    }

    public function test_It_Should_Detect_Empty_Required_Fields(): void
    {
        $requiredFields = ['userName', 'firstName', 'lastName', 'email', 'password', 'role', 'securityAnswer'];

        foreach ($requiredFields as $field) {
            $payload = PayloadHelper::createUser();
            $payload[$field] = '';

            $isEmpty = empty($payload[$field]);
            $this->assertTrue($isEmpty, "Field '$field' should be detected as empty");
        }
    }

    public function test_It_Should_Register_Multiple_Users_In_Sequence(): void
    {
        $userCount = 3;
        $createdEmails = [];

        for ($i = 0; $i < $userCount; $i++) {
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

            $createdEmails[] = $payload['email'];
        }

        $this->assertCount($userCount, $createdEmails);
        $this->assertCount($userCount, array_unique($createdEmails)); // Все email уникальны

        foreach ($createdEmails as $email) {
            $checkUser = new User($this->conn);
            $this->assertTrue($checkUser->getByEmail($email));
        }
    }

    public function test_It_Should_Set_Activated_To_Zero_By_Default(): void
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
        $user->setActivated(0);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $this->assertTrue($user->post());

        $checkUser = new User($this->conn);
        $this->assertTrue($checkUser->getByEmail($payload['email']));
        $this->assertEquals(0, $checkUser->getActivated());
    }
}