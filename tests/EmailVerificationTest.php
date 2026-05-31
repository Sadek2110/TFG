<?php

use PHPUnit\Framework\TestCase;

class EmailVerificationTest extends TestCase
{
    private Usuario $usuario;

    protected function setUp(): void
    {
        test_reset();
        $this->usuario = new Usuario();
    }

    public function test_registration_generates_token_and_unverified_status(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'             => 'Pepito Pérez',
            'email'            => 'pepito@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($user);
        $this->assertSame(0, (int) ($user['email_verified'] ?? 0));
        $this->assertNotEmpty($user['verification_token']);
        $this->assertSame(32, strlen(hex2bin($user['verification_token'])));
    }

    public function test_google_registration_is_auto_verified(): void
    {
        [$user, $errors] = $this->usuario->registerOrLoginWithGoogle([
            'id'    => 'google-id-12345',
            'email' => 'google-user@test.com',
            'name'  => 'Google User',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($user);
        $this->assertSame(1, (int) ($user['email_verified'] ?? 0));
        $this->assertNull($user['verification_token']);
    }

    public function test_google_registration_returns_is_new_flag(): void
    {
        [$user, $errors, $isNew] = $this->usuario->registerOrLoginWithGoogle([
            'id'    => 'google-id-99999',
            'email' => 'google-new-user@test.com',
            'name'  => 'Google New User',
        ]);

        $this->assertEmpty($errors);
        $this->assertTrue($isNew);

        // Login again with the same credentials, should return isNew as false
        [$userAgain, $errorsAgain, $isNewAgain] = $this->usuario->registerOrLoginWithGoogle([
            'id'    => 'google-id-99999',
            'email' => 'google-new-user@test.com',
            'name'  => 'Google New User',
        ]);

        $this->assertEmpty($errorsAgain);
        $this->assertFalse($isNewAgain);
    }

    public function test_valid_token_verifies_user(): void
    {
        [$user] = $this->usuario->register([
            'name'             => 'Test User',
            'email'            => 'verify-me@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $token = $user['verification_token'];
        $this->assertNotEmpty($token);

        $row = Database::one('SELECT id FROM users WHERE verification_token = ?', [$token]);
        $this->assertNotNull($row);
        $this->assertSame((int) $user['id'], (int) $row['id']);

        Database::run('UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?', [$user['id']]);

        $updated = $this->usuario->find((int) $user['id']);
        $this->assertSame(1, (int) $updated['email_verified']);
        $this->assertNull($updated['verification_token']);
    }

    public function test_invalid_token_does_not_verify_user(): void
    {
        [$user] = $this->usuario->register([
            'name'             => 'Test User 2',
            'email'            => 'verify-me-2@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $invalidToken = 'invalid-token-12345';
        $row = Database::one('SELECT id FROM users WHERE verification_token = ?', [$invalidToken]);
        $this->assertNull($row);

        $fresh = $this->usuario->find((int) $user['id']);
        $this->assertSame(0, (int) $fresh['email_verified']);
        $this->assertNotEmpty($fresh['verification_token']);
    }
}
