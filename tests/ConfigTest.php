<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    // ===== e() =====
    public function test_e_escapes_html(): void
    {
        $this->assertSame('&lt;script&gt;', e('<script>'));
    }

    public function test_e_null_returns_empty(): void
    {
        $this->assertSame('', e(null));
    }

    public function test_e_empty_string(): void
    {
        $this->assertSame('', e(''));
    }

    public function test_e_preserves_safe_text(): void
    {
        $this->assertSame('Hola mundo', e('Hola mundo'));
    }

    public function test_e_ampersand(): void
    {
        $this->assertSame('a &amp; b', e('a & b'));
    }

    public function test_e_quotes(): void
    {
        $this->assertSame('&quot;test&quot;', e('"test"'));
        $this->assertSame("&#039;test&#039;", e("'test'"));
    }

    // ===== url() =====
    public function test_url_empty_path(): void
    {
        $this->assertSame(BASE_URL . '/', url(''));
    }

    public function test_url_with_path(): void
    {
        $this->assertSame(BASE_URL . '/auth/login', url('auth/login'));
    }

    public function test_url_leading_slash_trimmed(): void
    {
        $this->assertSame(BASE_URL . '/dashboard', url('/dashboard'));
    }

    // ===== asset() =====
    public function test_asset_returns_url(): void
    {
        $result = asset('css/app.css');
        $this->assertStringStartsWith(ASSETS_URL . '/css/app.css', $result);
    }

    // ===== v_required() =====
    public function test_v_required_non_empty_string(): void
    {
        $this->assertTrue(v_required('Hola'));
    }

    public function test_v_required_empty_string(): void
    {
        $this->assertFalse(v_required(''));
    }

    public function test_v_required_whitespace_string(): void
    {
        $this->assertFalse(v_required('   '));
    }

    public function test_v_required_zero_is_truthy(): void
    {
        $this->assertFalse(v_required(0));
    }

    public function test_v_required_one_is_truthy(): void
    {
        $this->assertTrue(v_required(1));
    }

    public function test_v_required_empty_array(): void
    {
        $this->assertFalse(v_required([]));
    }

    public function test_v_required_non_empty_array(): void
    {
        $this->assertTrue(v_required([1]));
    }

    public function test_v_required_null(): void
    {
        $this->assertFalse(v_required(null));
    }

    // ===== v_email() =====
    public function test_v_email_valid(): void
    {
        $this->assertTrue(v_email('test@example.com'));
    }

    public function test_v_email_invalid(): void
    {
        $this->assertFalse(v_email('not-an-email'));
    }

    public function test_v_email_empty(): void
    {
        $this->assertFalse(v_email(''));
    }

    public function test_v_email_subdomain(): void
    {
        $this->assertTrue(v_email('user@sub.example.co.uk'));
    }

    public function test_v_email_plus_sign(): void
    {
        $this->assertTrue(v_email('user+tag@example.com'));
    }

    public function test_v_email_without_at(): void
    {
        $this->assertFalse(v_email('userexample.com'));
    }

    public function test_v_email_int(): void
    {
        $this->assertFalse(v_email(123));
    }

    // ===== v_min_len() =====
    public function test_v_min_len_sufficient(): void
    {
        $this->assertTrue(v_min_len('abcdefgh', 8));
    }

    public function test_v_min_len_exact(): void
    {
        $this->assertTrue(v_min_len('12345678', 8));
    }

    public function test_v_min_len_insufficient(): void
    {
        $this->assertFalse(v_min_len('123', 8));
    }

    public function test_v_min_len_empty(): void
    {
        $this->assertFalse(v_min_len('', 1));
    }

    public function test_v_min_len_non_string(): void
    {
        $this->assertFalse(v_min_len(12345678, 8));
    }

    public function test_v_min_len_unicode(): void
    {
        $this->assertTrue(v_min_len('áéíóúñ', 5));
        $this->assertFalse(v_min_len('áéí', 5));
    }

    public function test_v_min_len_zero_min(): void
    {
        $this->assertTrue(v_min_len('', 0));
    }

    // ===== v_int_range() =====
    public function test_v_int_range_in_range(): void
    {
        $this->assertTrue(v_int_range(5, 1, 10));
    }

    public function test_v_int_range_at_min(): void
    {
        $this->assertTrue(v_int_range(1, 1, 10));
    }

    public function test_v_int_range_at_max(): void
    {
        $this->assertTrue(v_int_range(10, 1, 10));
    }

    public function test_v_int_range_below_min(): void
    {
        $this->assertFalse(v_int_range(0, 1, 10));
    }

    public function test_v_int_range_above_max(): void
    {
        $this->assertFalse(v_int_range(11, 1, 10));
    }

    public function test_v_int_range_string_numeric(): void
    {
        $this->assertTrue(v_int_range('5', 1, 10));
    }

    public function test_v_int_range_negative(): void
    {
        $this->assertTrue(v_int_range(-3, -5, 0));
        $this->assertFalse(v_int_range(-6, -5, 0));
    }

    // ===== csrf_token() =====
    public function test_csrf_token_generates_token(): void
    {
        $token = csrf_token();
        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));
    }

    public function test_csrf_token_returns_same_token(): void
    {
        $token1 = csrf_token();
        $token2 = csrf_token();
        $this->assertSame($token1, $token2);
    }

    // ===== csrf_field() =====
    public function test_csrf_field_contains_token(): void
    {
        $field = csrf_field();
        $token = csrf_token();
        $this->assertStringContainsString($token, $field);
        $this->assertStringContainsString('<input type="hidden"', $field);
    }

    // ===== verify_csrf() =====
    public function test_verify_csrf_valid(): void
    {
        $token = csrf_token();
        $_POST['_csrf'] = $token;
        $this->assertTrue(verify_csrf());
    }

    public function test_verify_csrf_invalid(): void
    {
        csrf_token(); // genera token
        $_POST['_csrf'] = 'invalid-token';
        $this->assertFalse(verify_csrf());
    }

    public function test_verify_csrf_missing_token(): void
    {
        $this->assertFalse(verify_csrf());
    }

    public function test_verify_csrf_from_header(): void
    {
        $token = csrf_token();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
        $this->assertTrue(verify_csrf());
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    public function test_verify_csrf_no_stored_token(): void
    {
        unset($_SESSION['_csrf']);
        $_POST['_csrf'] = 'anything';
        $this->assertFalse(verify_csrf());
    }

    // ===== flash() y flash_pull() =====
    public function test_flash_and_flash_pull(): void
    {
        flash('success', 'Todo bien');
        flash('error', 'Algo falló');

        $pulled = flash_pull();
        $this->assertCount(2, $pulled);
        $this->assertSame('success', $pulled[0]['type']);
        $this->assertSame('Todo bien', $pulled[0]['msg']);
        $this->assertSame('error', $pulled[1]['type']);
        $this->assertSame('Algo falló', $pulled[1]['msg']);
    }

    public function test_flash_pull_clears(): void
    {
        flash('info', 'test');
        flash_pull();
        $this->assertEmpty(flash_pull());
    }

    public function test_flash_pull_empty(): void
    {
        $this->assertSame([], flash_pull());
    }

    // ===== old() / flash_old() / old_clear() =====
    public function test_old_returns_value(): void
    {
        $_SESSION['_old'] = ['name' => 'Juan'];
        $this->assertSame('Juan', old('name'));
    }

    public function test_old_returns_default(): void
    {
        $this->assertSame('', old('missing'));
        $this->assertSame('default', old('missing', 'default'));
    }

    public function test_old_escapes_html(): void
    {
        $_SESSION['_old'] = ['name' => '<b>Juan</b>'];
        $this->assertSame('&lt;b&gt;Juan&lt;/b&gt;', old('name'));
    }

    public function test_flash_old_stores_data(): void
    {
        flash_old(['name' => 'Ana', 'email' => 'ana@test.com', 'password' => 'secret', '_csrf' => 'token123']);
        $this->assertSame('Ana', $_SESSION['_old']['name'] ?? null);
        $this->assertSame('ana@test.com', $_SESSION['_old']['email'] ?? null);
        $this->assertArrayNotHasKey('password', $_SESSION['_old']);
        $this->assertArrayNotHasKey('_csrf', $_SESSION['_old']);
    }

    public function test_old_clear(): void
    {
        $_SESSION['_old'] = ['name' => 'x'];
        old_clear();
        $this->assertArrayNotHasKey('_old', $_SESSION);
    }

    // ===== is_auth() / is_admin() / current_user() =====
    public function test_is_auth_false_by_default(): void
    {
        $this->assertFalse(is_auth());
    }

    public function test_is_auth_true_after_login(): void
    {
        $_SESSION['user'] = ['id' => 1, 'name' => 'Test'];
        $this->assertTrue(is_auth());
    }

    public function test_current_user_returns_null(): void
    {
        $this->assertNull(current_user());
    }

    public function test_current_user_returns_user(): void
    {
        $user = ['id' => 1, 'name' => 'Test'];
        $_SESSION['user'] = $user;
        $this->assertSame($user, current_user());
    }

    public function test_is_admin_player(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'player'];
        $this->assertFalse(is_admin());
    }

    public function test_is_admin_true(): void
    {
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin'];
        $this->assertTrue(is_admin());
    }

    // ===== login_user() / logout_user() =====
    public function test_login_user_sets_session(): void
    {
        $user = ['id' => 5, 'name' => 'Pepe', 'password_hash' => 'secret_hash'];
        login_user($user);
        $this->assertSame('Pepe', $_SESSION['user']['name']);
        $this->assertArrayNotHasKey('password_hash', $_SESSION['user']);
        $this->assertArrayHasKey('_login_at', $_SESSION);
    }

    public function test_logout_user_clears_session(): void
    {
        $_SESSION['user'] = ['id' => 1];
        logout_user();
        $this->assertEmpty($_SESSION);
    }
}
