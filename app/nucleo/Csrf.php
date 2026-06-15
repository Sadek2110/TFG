<?php
// Protección CSRF mediante token por sesión.

declare(strict_types=1);

class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Devuelve el campo oculto listo para incrustar en un formulario.
    public static function campo(): string
    {
        return '<input type="hidden" name="_csrf" value="' . escapar(self::token()) . '">';
    }

    public static function valido(?string $tokenRecibido): bool
    {
        if ($tokenRecibido === null || empty($_SESSION['csrf_token'])) {
            return false;
        }
        // hash_equals protege frente a ataques de temporización.
        return hash_equals($_SESSION['csrf_token'], $tokenRecibido);
    }

    // Aborta la petición si el token no es válido. Solo para métodos no seguros.
    public static function exigir(): void
    {
        if (!self::valido($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Token de seguridad no válido. Recarga la página y vuelve a intentarlo.';
            exit;
        }
    }
}
