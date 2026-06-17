<?php
declare(strict_types=1);

class Equipo
{
    public static function listar(): array
    {
        return BaseDeDatos::todos(
            'SELECT e.*, u.nombre AS nombre_capitan,
                    (SELECT COUNT(*) FROM miembros_equipo m WHERE m.id_equipo = e.id) AS total_miembros
             FROM equipos e
             JOIN usuarios u ON u.id = e.id_capitan
             ORDER BY e.nombre'
        );
    }

    public static function buscarPorId(int $id): ?array
    {
        return BaseDeDatos::uno(
            'SELECT e.*, u.nombre AS nombre_capitan, u.email AS email_capitan
             FROM equipos e
             JOIN usuarios u ON u.id = e.id_capitan
             WHERE e.id = :id',
            ['id' => $id]
        );
    }

    public static function buscarPorNombre(string $nombre): ?array
    {
        return BaseDeDatos::uno(
            'SELECT * FROM equipos WHERE nombre = :nombre',
            ['nombre' => $nombre]
        );
    }

    public static function crear(string $nombre, string $ciudad, string $descripcion, int $idCapitan): int
    {
        BaseDeDatos::ejecutar(
            'INSERT INTO equipos (nombre, ciudad, descripcion, id_capitan)
             VALUES (:nombre, :ciudad, :descripcion, :id_capitan)',
            [
                'nombre'      => $nombre,
                'ciudad'      => $ciudad !== '' ? $ciudad : null,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'id_capitan'  => $idCapitan,
            ]
        );
        return BaseDeDatos::ultimoId();
    }

    public static function actualizar(int $id, string $nombre, string $ciudad, string $descripcion): void
    {
        BaseDeDatos::ejecutar(
            'UPDATE equipos SET nombre = :nombre, ciudad = :ciudad, descripcion = :descripcion
             WHERE id = :id',
            [
                'nombre'      => $nombre,
                'ciudad'      => $ciudad !== '' ? $ciudad : null,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'id' => $id,
            ]
        );
    }

    public static function eliminar(int $id): void
    {
        BaseDeDatos::ejecutar('DELETE FROM equipos WHERE id = :id', ['id' => $id]);
    }

    public static function esCapitan(int $idEquipo, int $idUsuario): bool
    {
        return BaseDeDatos::valor(
            'SELECT 1 FROM equipos WHERE id = :id AND id_capitan = :uid',
            ['id' => $idEquipo, 'uid' => $idUsuario]
        ) !== null;
    }

    public static function equipoCapitaneadoPorUsuario(int $idUsuario): ?array
    {
        return BaseDeDatos::uno(
            'SELECT * FROM equipos WHERE id_capitan = :uid ORDER BY fecha_creacion ASC LIMIT 1',
            ['uid' => $idUsuario]
        );
    }

    public static function capitaneaAlgunEquipo(int $idUsuario): bool
    {
        return self::equipoCapitaneadoPorUsuario($idUsuario) !== null;
    }

    // Listado simple para selects de formularios.
    public static function nombres(): array
    {
        return BaseDeDatos::todos('SELECT id, nombre FROM equipos ORDER BY nombre');
    }

    public static function nombresCapitaneadosPor(int $idUsuario): array
    {
        return BaseDeDatos::todos(
            'SELECT id, nombre FROM equipos WHERE id_capitan = :uid ORDER BY nombre',
            ['uid' => $idUsuario]
        );
    }
}
