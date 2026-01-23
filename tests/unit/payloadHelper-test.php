<?php

use PHPUnit\Framework\TestCase;

class PayloadHelperTest extends TestCase {
    
    /**
     * @test
     * Тест создания базового payload пользователя
     */
    public function it_should_create_default_user_payload() {
        // Act
        $payload = PayloadHelper::createUser();
        
        // Assert
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('name', $payload);
        $this->assertArrayHasKey('vorname', $payload);
        $this->assertArrayHasKey('email', $payload);
        $this->assertArrayHasKey('username', $payload);
        $this->assertArrayHasKey('password', $payload);
        $this->assertArrayHasKey('art', $payload);
        
        // Проверка значений по умолчанию
        $this->assertEquals('Password123!', $payload['password']);
        $this->assertContains($payload['art'], ['Lehrer', 'Ausbilder', 'Admin']);
    }
    
    /**
     * @test
     * Тест переопределения полей через overrides
     */
    public function it_should_override_default_values() {
        // Act
        $payload = PayloadHelper::createUser([
            'name' => 'CustomName',
            'email' => 'custom@test.com',
            'art' => 'Admin'
        ]);
        
        // Assert
        $this->assertEquals('CustomName', $payload['name']);
        $this->assertEquals('custom@test.com', $payload['email']);
        $this->assertEquals('Admin', $payload['art']);
        $this->assertEquals('Password123!', $payload['password']); // Не переопределено
    }
    
    /**
     * @test
     * Тест уникальности email при множественном вызове
     */
    public function it_should_generate_unique_emails() {
        // Act
        $payload1 = PayloadHelper::createUser();
        $payload2 = PayloadHelper::createUser();
        $payload3 = PayloadHelper::createUser();
        
        // Assert
        $this->assertNotEquals($payload1['email'], $payload2['email']);
        $this->assertNotEquals($payload2['email'], $payload3['email']);
        $this->assertNotEquals($payload1['email'], $payload3['email']);
    }
    
    /**
     * @test
     * Тест уникальности username
     */
    public function it_should_generate_unique_usernames() {
        // Act
        $payload1 = PayloadHelper::createUser();
        $payload2 = PayloadHelper::createUser();
        
        // Assert
        $this->assertNotEquals($payload1['username'], $payload2['username']);
    }
    
    /**
     * @test
     * Тест валидности email формата
     */
    public function it_should_generate_valid_email_format() {
        // Act
        $payload = PayloadHelper::createUser();
        
        // Assert
        $this->assertTrue(filter_var($payload['email'], FILTER_VALIDATE_EMAIL) !== false);
    }
}