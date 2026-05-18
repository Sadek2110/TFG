<?php

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    private function dispatchAndGetOutput(string $url): string
    {
        ob_start();
        Router::dispatch($url);
        return ob_get_clean();
    }

    public function test_dispatch_home_route(): void
    {
        $output = $this->dispatchAndGetOutput('/');
        $this->assertStringContainsString('FastPlay', $output);
    }

    public function test_dispatch_auth_login(): void
    {
        $output = $this->dispatchAndGetOutput('/auth/login');
        $this->assertStringContainsStringIgnoringCase('iniciar', $output);
    }

    public function test_dispatch_auth_register(): void
    {
        $output = $this->dispatchAndGetOutput('/auth/register');
        $this->assertStringContainsStringIgnoringCase('crear', $output);
    }

    public function test_dispatch_dashboard_requires_auth(): void
    {
        // DashboardController::index() calls requireAuth() -> redirect()
        // Router::dispatch catches Throwable and renders 500 page
        $output = $this->dispatchAndGetOutput('/dashboard');
        $this->assertStringContainsString('500', $output);
    }

    public function test_dispatch_home_index(): void
    {
        $output = $this->dispatchAndGetOutput('/home/index');
        $this->assertStringContainsString('FastPlay', $output);
    }

    public function test_dispatch_nonexistent_controller(): void
    {
        $output = $this->dispatchAndGetOutput('/nonexistent');
        $this->assertStringContainsString('404', $output);
    }

    public function test_dispatch_invalid_controller_slug(): void
    {
        $output = $this->dispatchAndGetOutput('/admin../index');
        $this->assertStringContainsString('404', $output);
    }

    public function test_dispatch_non_public_method(): void
    {
        // view() is protected — should 404
        $output = $this->dispatchAndGetOutput('/home/view');
        $this->assertStringContainsString('404', $output);
    }

    public function test_dispatch_underscore_prefixed_method(): void
    {
        $output = $this->dispatchAndGetOutput('/home/_private');
        $this->assertStringContainsString('404', $output);
    }

    public function test_dispatch_nonexistent_action(): void
    {
        $output = $this->dispatchAndGetOutput('/home/nonexistentMethod');
        $this->assertStringContainsString('404', $output);
    }

    public function test_not_found_response_code(): void
    {
        ob_start();
        Router::notFound();
        ob_get_clean();
        $this->assertSame(404, http_response_code());
    }
}
