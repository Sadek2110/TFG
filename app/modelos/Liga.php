<?php
declare(strict_types=1);

class Liga
{
    public static function listar(): array
    {
        return BaseDeDatos::todos(
            'SELECT l.*,
                    (SELECT COUNT(*) FROM ligas_equipos le WHERE le.id_liga = l.id) AS total_equipos
             FROM ligas l
             ORDER BY l.temporada DESC, l.nombre'
        );
    }

    public static function nombres(): array
    {
        return BaseDeDatos::todos('SELECT id, nombre, temporada FROM ligas ORDER BY temporada DESC, nombre');
    }

    public static function buscarPorId(int $id): ?array
    {
        return BaseDeDatos::uno('SELECT * FROM ligas WHERE id = :id', ['id' => $id]);
    }

    public static function crear(string $nombre, string $temporada, string $descripcion): int
    {
        BaseDeDatos::ejecutar(
            'INSERT INTO ligas (nombre, temporada, descripcion)
             VALUES (:nombre, :temporada, :descripcion)',
            [
                'nombre'      => $nombre,
                'temporada'   => $temporada,
                'descripcion' => $descripcion !== '' ? $descripcion : null,
            ]
        );
        return BaseDeDatos::ultimoId();
    }

    public static function inscribirEquipo(int $idLiga, int $idEquipo): void
    {
        BaseDeDatos::ejecutar(
            'INSERT OR IGNORE INTO ligas_equipos (id_liga, id_equipo) VALUES (:l, :e)',
            ['l' => $idLiga, 'e' => $idEquipo]
        );
    }

    public static function equiposInscritos(int $idLiga): array
    {
        return BaseDeDatos::todos(
            'SELECT e.id, e.nombre
             FROM equipos e
             JOIN ligas_equipos le ON le.id_equipo = e.id
             WHERE le.id_liga = :id
             ORDER BY e.nombre',
            ['id' => $idLiga]
        );
    }

    // Calcula la clasificación a partir de los partidos finalizados.
    public static function clasificacion(int $idLiga): array
    {
        $sql = "
        WITH part AS (
            SELECT id_equipo_local AS id_equipo,
                   goles_local      AS goles_a_favor,
                   goles_visitante  AS goles_en_contra
            FROM partidos
            WHERE id_liga = :l AND estado = 'finalizado'
            UNION ALL
            SELECT id_equipo_visitante,
                   goles_visitante,
                   goles_local
            FROM partidos
            WHERE id_liga = :l AND estado = 'finalizado'
        )
        SELECT e.id, e.nombre,
               COUNT(p.id_equipo) AS jugados,
               COALESCE(SUM(CASE WHEN p.goles_a_favor >  p.goles_en_contra THEN 1 ELSE 0 END), 0) AS ganados,
               COALESCE(SUM(CASE WHEN p.goles_a_favor =  p.goles_en_contra THEN 1 ELSE 0 END), 0) AS empatados,
               COALESCE(SUM(CASE WHEN p.goles_a_favor <  p.goles_en_contra THEN 1 ELSE 0 END), 0) AS perdidos,
               COALESCE(SUM(p.goles_a_favor),   0) AS goles_favor,
               COALESCE(SUM(p.goles_en_contra), 0) AS goles_contra,
               COALESCE(SUM(CASE WHEN p.goles_a_favor >  p.goles_en_contra THEN 3
                                 WHEN p.goles_a_favor =  p.goles_en_contra THEN 1
                                 ELSE 0 END), 0) AS puntos
        FROM equipos e
        JOIN ligas_equipos le ON le.id_equipo = e.id AND le.id_liga = :l
        LEFT JOIN part p      ON p.id_equipo  = e.id
        GROUP BY e.id, e.nombre
        ORDER BY puntos DESC,
                 (goles_favor - goles_contra) DESC,
                 goles_favor DESC,
                 e.nombre ASC
        ";
        return BaseDeDatos::todos($sql, ['l' => $idLiga]);
    }
}
