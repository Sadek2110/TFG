<?php
// Helpers de sesión: usuario autenticado, mensajes flash y autorización.

declare(strict_types=1);

class Sesion
{
    // Guarda al usuario en la sesión tras un login correcto.
    // Regenera el identificador para evitar fijación de sesión.
    public static function iniciar(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => (int) $usuario['id'],
            'nombre' => (string) $usuario['nombre'],
            'email'  => (string) $usuario['email'],
            'rol'    => (string) $usuario['rol'],
        ];
    }

    public static function cerrar(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                $parametros['secure'],
                $parametros['httponly']
            );
        }
        session_destroy();
    }

    public static function autenticado(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function idUsuario(): ?int
    {
        return isset($_SESSION['usuario']) ? (int) $_SESSION['usuario']['id'] : null;
    }

    public static function esAdministrador(): bool
    {
        return (self::usuario()['rol'] ?? '') === 'administrador';
    }

    // Mensajes que sobreviven una redirección (patrón Post/Redirect/Get).
    public static function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    public static function consumirFlash(): array
    {
        $mensajes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $mensajes;
    }
}
