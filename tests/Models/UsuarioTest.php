<?php

use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    private Usuario $usuario;

    protected function setUp(): void
    {
        test_reset();
        $this->usuario = new Usuario();
    }

    // ===== register() =====
    public function test_register_creates_user(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Juan Pérez',
            'email'    => 'juan@test.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
            'age'      => '25',
            'city'     => 'Madrid',
            'position' => 'Delantero',
            'phone'    => '+34600123456',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($user);
        $this->assertSame('juan@test.com', $user['email']);
        $this->assertSame('player', $user['role']);
        $this->assertArrayNotHasKey('password_hash', $user);
    }

    public function test_register_with_minimal_data(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Ana',
            'email'    => 'ana@test.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($user);
    }

    public function test_register_short_name_fails(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'A',
            'email'    => 'a@test.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
        ]);

        $this->assertNull($user);
        $this->assertArrayHasKey('name', $errors);
    }

    public function test_register_invalid_email(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Test',
            'email'    => 'not-an-email',
            'password' => 'password123',
            'password_confirm' => 'password123',
        ]);

        $this->assertNull($user);
        $this->assertArrayHasKey('email', $errors);
    }

    public function test_register_password_too_short(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Test',
            'email'    => 'test@test.com',
            'password' => '12345',
            'password_confirm' => '12345',
        ]);

        $this->assertNull($user);
        $this->assertArrayHasKey('password', $errors);
    }

    public function test_register_password_mismatch(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Test',
            'email'    => 'test@test.com',
            'password' => 'password123',
            'password_confirm' => 'different',
        ]);

        $this->assertNull($user);
        $this->assertArrayHasKey('password_confirm', $errors);
    }

    public function test_register_age_out_of_range(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Test',
            'email'    => 'test@test.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
            'age'      => '10',
        ]);

        $this->assertNull($user);
        $this->assertArrayHasKey('age', $errors);
    }

    public function test_register_age_zero_is_ok(): void
    {
        [$user, $errors] = $this->usuario->register([
            'name'     => 'Test',
            'email'    => 'test@test.com',
            'password' => 'password123',
            'password_confirm' => 'password123',
            'age'      => '0',
        ]);

        $this->assertEmpty($errors);
        $this->assertNotNull($user);
    }

    public function test_register_duplicate_email(): void
    {
        $this->usuario->register([
            'name' => 'First', 'email' => 'dup@test.com',
            'password' => 'password123', 'password_confirm' => 'password123',
        ]);
        [$user, $errors] = $this->usuario->register([
            'name' => 'Second', 'email' => 'dup@test.com',
            'password' => 'password123', 'password_confirm' => 'password123',
        ]);

        $this->assertNull($user);
        $this->assertArrayHasKey('email', $errors);
    }

    public function test_register_normalizes_email_to_lowercase(): void
    {
        [$user] = $this->usuario->register([
            'name' => 'Test', 'email' => 'UPPER@TEST.COM',
            'password' => 'password123', 'password_confirm' => 'password123',
        ]);
        $this->assertSame('upper@test.com', $user['email']);
    }

    // ===== login() =====
    public function test_login_with_correct_credentials(): void
    {
        $this->usuario->register([
            'name' => 'Login Test', 'email' => 'login@test.com',
            'password' => 'password123', 'password_confirm' => 'password123',
        ]);

        $user = $this->usuario->login('login@test.com', 'password123');
        $this->assertNotNull($user);
        $this->assertSame('login@test.com', $user['email']);
        $this->assertArrayNotHasKey('password_hash', $user);
    }

    public function test_login_with_wrong_password(): void
    {
        $this->usuario->register([
            'name' => 'Login Test', 'email' => 'login@test.com',
            'password' => 'password123', 'password_confirm' => 'password123',
        ]);

        $user = $this->usuario->login('login@test.com', 'wrongpassword');
        $this->assertNull($user);
    }

    public function test_login_with_empty_credentials(): void
    {
        $this->assertNull($this->usuario->login('', ''));
        $this->assertNull($this->usuario->login('email@test.com', ''));
    }

    public function test_login_case_insensitive_email(): void
    {
        $this->usuario->register([
            'name' => 'Test', 'email' => 'case@test.com',
            'password' => 'password123', 'password_confirm' => 'password123',
        ]);

        $user = $this->usuario->login('CASE@TEST.COM', 'password123');
        $this->assertNotNull($user);
        $this->assertSame('case@test.com', $user['email']);
    }

    // ===== find() =====
    public function test_find_returns_user(): void
    {
        $u = test_create_user('Find Me', 'find@test.com');
        $found = $this->usuario->find((int) $u['id']);
        $this->assertNotNull($found);
        $this->assertSame('Find Me', $found['name']);
        $this->assertArrayNotHasKey('password_hash', $found);
    }

    public function test_find_returns_null_for_missing(): void
    {
        $this->assertNull($this->usuario->find(9999));
    }

    // ===== updateProfile() =====
    public function test_update_profile(): void
    {
        $u = test_create_user('Old Name');
        $errors = $this->usuario->updateProfile((int) $u['id'], [
            'name' => 'New Name', 'age' => '30', 'city' => 'Barcelona',
            'position' => 'Mediocampo', 'phone' => '+34600999888',
        ]);

        $this->assertEmpty($errors);
        $updated = $this->usuario->find((int) $u['id']);
        $this->assertSame('New Name', $updated['name']);
        $this->assertSame(30, (int) $updated['age']);
        $this->assertSame('Barcelona', $updated['city']);
    }

    public function test_update_profile_invalid_position(): void
    {
        $u = test_create_user();
        $errors = $this->usuario->updateProfile((int) $u['id'], [
            'name' => 'Test', 'position' => 'Arquero',
        ]);
        $this->assertArrayHasKey('position', $errors);
    }

    // ===== changePassword() =====
    public function test_change_password(): void
    {
        test_create_user('Pass Test', 'pass@test.com', 'oldpass123');
        $u = $this->usuario->login('pass@test.com', 'oldpass123');
        $errors = $this->usuario->changePassword((int) $u['id'], 'oldpass123', 'newpass456', 'newpass456');
        $this->assertEmpty($errors);

        $login = $this->usuario->login('pass@test.com', 'newpass456');
        $this->assertNotNull($login);
    }

    public function test_change_password_wrong_current(): void
    {
        test_create_user('Pass Test', 'pass@test.com', 'oldpass123');
        $u = $this->usuario->login('pass@test.com', 'oldpass123');
        $errors = $this->usuario->changePassword((int) $u['id'], 'wrongcurrent', 'newpass456', 'newpass456');
        $this->assertArrayHasKey('current', $errors);
    }

    public function test_change_password_short_new(): void
    {
        test_create_user('Pass Test', 'pass@test.com', 'oldpass123');
        $u = $this->usuario->login('pass@test.com', 'oldpass123');
        $errors = $this->usuario->changePassword((int) $u['id'], 'oldpass123', 'short', 'short');
        $this->assertArrayHasKey('new', $errors);
    }

    public function test_change_password_mismatch(): void
    {
        test_create_user('Pass Test', 'pass@test.com', 'oldpass123');
        $u = $this->usuario->login('pass@test.com', 'oldpass123');
        $errors = $this->usuario->changePassword((int) $u['id'], 'oldpass123', 'newpass456', 'different');
        $this->assertArrayHasKey('confirm', $errors);
    }

    // ===== Rate limit =====
    public function test_rate_limit_blocks_after_5_failures(): void
    {
        test_create_user('Rate Test', 'rate@test.com', 'goodpass');

        for ($i = 0; $i < 5; $i++) {
            $result = $this->usuario->login('rate@test.com', 'badpass', '10.0.0.1');
            $this->assertNull($result, "Attempt $i should fail");
        }

        $result = $this->usuario->login('rate@test.com', 'goodpass', '10.0.0.1');
        $this->assertNull($result, 'Should be rate-limited');
    }

    // ===== all() =====
    public function test_all_returns_users(): void
    {
        test_create_user('A', 'a@test.com');
        test_create_user('B', 'b@test.com');
        $all = $this->usuario->all();
        $this->assertCount(2, $all);
    }

    // ===== setRole() =====
    public function test_set_role(): void
    {
        $u = test_create_user();
        $this->usuario->setRole((int) $u['id'], 'admin');
        $updated = $this->usuario->find((int) $u['id']);
        $this->assertSame('admin', $updated['role']);
    }

    public function test_set_role_invalid(): void
    {
        $u = test_create_user();
        $this->usuario->setRole((int) $u['id'], 'superadmin');
        $updated = $this->usuario->find((int) $u['id']);
        $this->assertSame('player', $updated['role']);
    }

    // ===== delete() =====
    public function test_delete_user(): void
    {
        $u = test_create_user();
        $this->usuario->delete((int) $u['id']);
        $this->assertNull($this->usuario->find((int) $u['id']));
    }

    // ===== dashboardStats() =====
    public function test_dashboard_stats_empty(): void
    {
        $u = test_create_user();
        $stats = $this->usuario->dashboardStats((int) $u['id']);
        $this->assertCount(4, $stats);
        $this->assertSame(0, $stats[0]['v']);
    }

    // ===== achievements() =====
    public function test_achievements_empty(): void
    {
        $u = test_create_user();
        $achs = $this->usuario->achievements((int) $u['id']);
        $this->assertIsArray($achs);
        $this->assertEmpty($achs);
    }
}
