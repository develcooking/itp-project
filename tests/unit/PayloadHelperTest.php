<?php

use PHPUnit\Framework\TestCase;

class PayloadHelperTest extends TestCase {

    public function testItShouldCreateDefaultUserPayload() {
        $payload = PayloadHelper::createUser();

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('userName', $payload);
        $this->assertArrayHasKey('firstName', $payload);
        $this->assertArrayHasKey('lastName', $payload);
        $this->assertArrayHasKey('email', $payload);
        $this->assertArrayHasKey('password', $payload);
        $this->assertArrayHasKey('role', $payload);
        $this->assertEquals('Password123!', $payload['password']);
        $this->assertContains($payload['role'], ['Lehrer', 'Ausbilder', 'Admin']);
    }

    public function testItShouldOverrideDefaultValues() {
        $payload = PayloadHelper::createUser([
            'lastName' => 'CustomName',
            'email' => 'custom@test.com',
            'role' => 'Admin'
        ]);

        $this->assertEquals('CustomName', $payload['lastName']);
        $this->assertEquals('custom@test.com', $payload['email']);
        $this->assertEquals('Admin', $payload['role']);
        $this->assertEquals('Password123!', $payload['password']);
    }

    public function testItShouldGenerateUniqueEmails() {
        $payload1 = PayloadHelper::createUser();
        $payload2 = PayloadHelper::createUser();
        $payload3 = PayloadHelper::createUser();

        $this->assertNotEquals($payload1['email'], $payload2['email']);
        $this->assertNotEquals($payload2['email'], $payload3['email']);
        $this->assertNotEquals($payload1['email'], $payload3['email']);
    }

    public function testItShouldGenerateUniqueUsernames() {
        $payload1 = PayloadHelper::createUser();
        $payload2 = PayloadHelper::createUser();

        $this->assertNotEquals($payload1['userName'], $payload2['userName']);
    }

    public function testItShouldGenerateValidEmailFormat() {
        $payload = PayloadHelper::createUser();

        $this->assertTrue(filter_var($payload['email'], FILTER_VALIDATE_EMAIL) !== false);
    }
}