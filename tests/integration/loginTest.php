<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../models/User.php';

class LoginTest extends TestCase
{
    private $conn;
    private $testUser;
    private $testPassword = 'TestPassword123!';
    private $baseUrl = 'http://localhost:8000';

    protected function setUp(): void
    {
        $this->conn = $GLOBALS['conn'];
        $payload = PayloadHelper::createUser([
            'password' => $this->testPassword,
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
        $user->setActivated(1);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $user->post();

        $this->testUser = $payload;
    }

    private function sendPostRequest(string $url, array $postData): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        return [
            'code' => $httpCode,
            'headers' => $headers,
            'body' => $body
        ];
    }

    public function test_It_Should_Login_Successfully_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(302, $response['code'], 'Should redirect after successful login');
        $this->assertStringContainsString('Location: /controllers/startpage.php', $response['headers']);
    }

    public function test_It_Should_Fail_Login_With_Wrong_Password_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => 'WrongPassword123!',
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('Ungültige E-Mail oder Passwort', $response['body']);
    }

    public function test_It_Should_Fail_Login_With_Nonexistent_Email_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => 'nonexistent@example.com',
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('Ungültige E-Mail oder Passwort', $response['body']);
    }

    public function test_It_Should_Fail_Login_With_Inactive_User_Via_HTTP(): void
    {
        $payload = PayloadHelper::createUser([
            'password' => $this->testPassword,
            'activated' => 0
        ]);

        $inactiveUser = new User($this->conn);
        $inactiveUser->setUserName($payload['userName']);
        $inactiveUser->setFirstName($payload['firstName']);
        $inactiveUser->setLastName($payload['lastName']);
        $inactiveUser->setEmail($payload['email']);
        $inactiveUser->setPassword($payload['password']);
        $inactiveUser->setRole($payload['role']);
        $inactiveUser->setSecurityAnswer(password_hash($payload['securityAnswer'], PASSWORD_DEFAULT));
        $inactiveUser->setActivated(0);
        $inactiveUser->setCreatedBy(null);
        $inactiveUser->setModifiedBy(null);
        $inactiveUser->post();

        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $payload['email'],
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('nicht aktiviert', $response['body']);
    }

    public function test_It_Should_Fail_Login_With_Empty_Email_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => '',
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('erforderlich', $response['body']);
    }

    public function test_It_Should_Fail_Login_With_Empty_Password_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => '',
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('erforderlich', $response['body']);
    }

    public function test_It_Should_Fail_Login_With_Invalid_Email_Format_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => 'not-an-email',
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('Ungültiges E-Mail-Format', $response['body']);
    }

    public function test_It_Should_Login_Users_With_Different_Roles_Via_HTTP(): void
    {
        $roles = ['Lehrer', 'Ausbilder', 'Admin'];

        foreach ($roles as $role) {
            $payload = PayloadHelper::createUser([
                'password' => $this->testPassword,
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
            $user->setActivated(1);
            $user->setCreatedBy(null);
            $user->setModifiedBy(null);
            $user->post();

            $response = $this->sendPostRequest(
                $this->baseUrl . '/controllers/login.php',
                [
                    'email' => $payload['email'],
                    'password' => $this->testPassword,
                    'login' => true
                ]
            );

            $this->assertEquals(302, $response['code'], "Should login user with role: $role");
            $this->assertStringContainsString('Location: /controllers/startpage.php', $response['headers']);
        }
    }

    public function test_It_Should_Allow_Multiple_Login_Attempts_Via_HTTP(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $response = $this->sendPostRequest(
                $this->baseUrl . '/controllers/login.php',
                [
                    'email' => $this->testUser['email'],
                    'password' => $this->testPassword,
                    'login' => true
                ]
            );

            $this->assertEquals(302, $response['code'], "Login attempt #" . ($i + 1) . " should succeed");
        }
    }

    public function test_It_Should_Handle_Email_With_Whitespace_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => '  ' . $this->testUser['email'] . '  ',
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(302, $response['code']);
        $this->assertStringContainsString('Location: /controllers/startpage.php', $response['headers']);
    }

    public function test_It_Should_Sanitize_Email_Input_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => '<script>alert("xss")</script>' . $this->testUser['email'],
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(200, $response['code']);
        $this->assertStringContainsString('Ungültige E-Mail', $response['body']);
    }

    public function test_It_Should_Handle_Special_Characters_In_Password_Via_HTTP(): void
    {
        $specialPassword = 'P@$$w0rd!#%&*()_+-=[]{}|;:,.<>?';

        $payload = PayloadHelper::createUser([
            'password' => $specialPassword,
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
        $user->setActivated(1);
        $user->setCreatedBy(null);
        $user->setModifiedBy(null);
        $user->post();

        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $payload['email'],
                'password' => $specialPassword,
                'login' => true
            ]
        );

        $this->assertEquals(302, $response['code']);
        $this->assertStringContainsString('Location: /controllers/startpage.php', $response['headers']);
    }

    public function test_It_Should_Logout_User_Via_HTTP(): void
    {
        $loginResponse = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(302, $loginResponse['code']);

        $logoutResponse = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'logout' => true
            ]
        );

        $this->assertEquals(302, $logoutResponse['code']);
        $this->assertStringContainsString('Location: /views/loginsite.php', $logoutResponse['headers']);
    }

    public function test_It_Should_Set_Session_Cookie_After_Login_Via_HTTP(): void
    {
        $response = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => $this->testPassword,
                'login' => true
            ]
        );

        $this->assertEquals(302, $response['code']);
        $this->assertStringContainsString('Set-Cookie: PHPSESSID=', $response['headers']);
    }

    public function test_It_Should_Be_Case_Sensitive_For_Password_Via_HTTP(): void
    {
        $response1 = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => $this->testPassword,
                'login' => true
            ]
        );
        $this->assertEquals(302, $response1['code']);

        $response2 = $this->sendPostRequest(
            $this->baseUrl . '/controllers/login.php',
            [
                'email' => $this->testUser['email'],
                'password' => strtolower($this->testPassword),
                'login' => true
            ]
        );
        $this->assertEquals(200, $response2['code']);
        $this->assertStringContainsString('Ungültige E-Mail oder Passwort', $response2['body']);
    }
}