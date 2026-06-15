<?php
declare(strict_types=1);

class Campo
{
    public static function listar(): array
    {
        return BaseDeDatos::todos('SELECT * FROM campos ORDER BY nombre');
    }

    public static function nombres(): array
    {
        return BaseDeDatos::todos('SELECT id, nombre FROM campos ORDER BY nombre');
    }

    public static function buscarPorId(int $id): ?array
    {
        return BaseDeDatos::uno('SELECT * FROM campos WHERE id = :id', ['id' => $id]);
    }

    public static function crear(string $nombre, string $direccion, string $ciudad, string $superficie): int
    {
        BaseDeDatos::ejecutar(
            'INSERT INTO campos (nombre, direccion, ciudad, superficie)
             VALUES (:nombre, :direccion, :ciudad, :superficie)',
            [
                'nombre'     => $nombre,
                'direccion'  => $direccion  !== '' ? $direccion  : null,
                'ciudad'     => $ciudad     !== '' ? $ciudad     : null,
                'superficie' => $superficie !== '' ? $superficie : null,
            ]
        );
        return BaseDeDatos::ultimoId();
    }

    public static function eliminar(int $id): void
    {
        BaseDeDatos::ejecutar('DELETE FROM campos WHERE id = :id', ['id' => $id]);
    }
}
