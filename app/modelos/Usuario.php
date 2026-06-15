<?php
// Modelo Usuario: encapsula las consultas y validaciones relacionadas
// con la tabla `usuarios`.

declare(strict_types=1);

class Usuario
{
    public static function buscarPorEmail(string $email): ?array
    {
        return BaseDeDatos::uno(
            'SELECT * FROM usuarios WHERE email = :email',
            ['email' => $email]
        );
    }

    public static function buscarPorId(int $id): ?array
    {
        return BaseDeDatos::uno(
            'SELECT * FROM usuarios WHERE id = :id',
            ['id' => $id]
        );
    }

    public static function crear(string $nombre, string $email, string $contrasena, string $rol = 'jugador'): int
    {
        BaseDeDatos::ejecutar(
            'INSERT INTO usuarios (nombre, email, contrasena_hash, rol)
             VALUES (:nombre, :email, :hash, :rol)',
            [
                'nombre' => $nombre,
                'email'  => strtolower($email),
                'hash'   => password_hash($contrasena, PASSWORD_DEFAULT),
                'rol'    => $rol,
            ]
        );
        return BaseDeDatos::ultimoId();
    }

    public static function actualizarPerfil(int $id, string $nombre, string $email): void
    {
        BaseDeDatos::ejecutar(
            'UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id',
            ['nombre' => $nombre, 'email' => strtolower($email), 'id' => $id]
        );
    }

    public static function actualizarContrasena(int $id, string $contrasena): void
    {
        BaseDeDatos::ejecutar(
            'UPDATE usuarios SET contrasena_hash = :hash WHERE id = :id',
            ['hash' => password_hash($contrasena, PASSWORD_DEFAULT), 'id' => $id]
        );
    }

    public static function emailEnUso(string $email, ?int $exceptoId = null): bool
    {
        if ($exceptoId === null) {
            return BaseDeDatos::valor(
                'SELECT 1 FROM usuarios WHERE email = :email',
                ['email' => strtolower($email)]
            ) !== null;
        }
        return BaseDeDatos::valor(
            'SELECT 1 FROM usuarios WHERE email = :email AND id <> :id',
            ['email' => strtolower($email), 'id' => $exceptoId]
        ) !== null;
    }

    public static function verificarCredenciales(string $email, string $contrasena): ?array
    {
        $usuario = self::buscarPorEmail(strtolower($email));
        if ($usuario === null) {
            return null;
        }
        if (!password_verify($contrasena, $usuario['contrasena_hash'])) {
            return null;
        }
        return $usuario;
    }
}
