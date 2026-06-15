<?php
declare(strict_types=1);

class MiembroEquipo
{
    public static function listarDeEquipo(int $idEquipo): array
    {
        return BaseDeDatos::todos(
            'SELECT m.id, m.id_usuario, m.dorsal, m.posicion, m.fecha_alta,
                    u.nombre, u.email
             FROM miembros_equipo m
             JOIN usuarios u ON u.id = m.id_usuario
             WHERE m.id_equipo = :id
             ORDER BY u.nombre',
            ['id' => $idEquipo]
        );
    }

    public static function existe(int $idEquipo, int $idUsuario): bool
    {
        return BaseDeDatos::valor(
            'SELECT 1 FROM miembros_equipo WHERE id_equipo = :e AND id_usuario = :u',
            ['e' => $idEquipo, 'u' => $idUsuario]
        ) !== null;
    }

    public static function anadir(int $idEquipo, int $idUsuario, ?int $dorsal, string $posicion): void
    {
        BaseDeDatos::ejecutar(
            'INSERT INTO miembros_equipo (id_equipo, id_usuario, dorsal, posicion)
             VALUES (:e, :u, :d, :p)',
            [
                'e' => $idEquipo,
                'u' => $idUsuario,
                'd' => $dorsal,
                'p' => $posicion !== '' ? $posicion : null,
            ]
        );
    }

    public static function quitar(int $idEquipo, int $idUsuario): void
    {
        BaseDeDatos::ejecutar(
            'DELETE FROM miembros_equipo WHERE id_equipo = :e AND id_usuario = :u',
            ['e' => $idEquipo, 'u' => $idUsuario]
        );
    }

    // ¿Está el usuario X en el equipo Y (como miembro o como capitán)?
    public static function perteneceA(int $idUsuario, int $idEquipo): bool
    {
        return self::existe($idEquipo, $idUsuario) || Equipo::esCapitan($idEquipo, $idUsuario);
    }
}
