<?php
declare(strict_types=1);

class Partido
{
    public static function listar(?int $idLiga = null): array
    {
        $sql = "SELECT p.*,
                       el.nombre AS nombre_local,
                       ev.nombre AS nombre_visitante,
                       c.nombre  AS nombre_campo,
                       l.nombre  AS nombre_liga
                FROM partidos p
                JOIN equipos el ON el.id = p.id_equipo_local
                JOIN equipos ev ON ev.id = p.id_equipo_visitante
                LEFT JOIN campos c ON c.id = p.id_campo
                LEFT JOIN ligas l  ON l.id = p.id_liga ";
        $parametros = [];
        if ($idLiga !== null) {
            $sql .= ' WHERE p.id_liga = :liga ';
            $parametros['liga'] = $idLiga;
        }
        $sql .= ' ORDER BY p.fecha_partido DESC';
        return BaseDeDatos::todos($sql, $parametros);
    }

    public static function buscarPorId(int $id): ?array
    {
        return BaseDeDatos::uno(
            "SELECT p.*,
                    el.nombre AS nombre_local,    el.id_capitan AS capitan_local,
                    ev.nombre AS nombre_visitante, ev.id_capitan AS capitan_visitante,
                    c.nombre AS nombre_campo,
                    l.nombre AS nombre_liga
             FROM partidos p
             JOIN equipos el ON el.id = p.id_equipo_local
             JOIN equipos ev ON ev.id = p.id_equipo_visitante
             LEFT JOIN campos c ON c.id = p.id_campo
             LEFT JOIN ligas l  ON l.id = p.id_liga
             WHERE p.id = :id",
            ['id' => $id]
        );
    }

    public static function crear(
        int $idEquipoLocal,
        int $idEquipoVisitante,
        string $fechaPartido,
        ?int $idCampo,
        ?int $idLiga
    ): int {
        BaseDeDatos::ejecutar(
            'INSERT INTO partidos
                (id_equipo_local, id_equipo_visitante, fecha_partido, id_campo, id_liga)
             VALUES (:local, :visitante, :fecha, :campo, :liga)',
            [
                'local'     => $idEquipoLocal,
                'visitante' => $idEquipoVisitante,
                'fecha'     => $fechaPartido,
                'campo'     => $idCampo,
                'liga'      => $idLiga,
            ]
        );
        return BaseDeDatos::ultimoId();
    }

    public static function registrarResultado(int $id, int $golesLocal, int $golesVisitante): void
    {
        BaseDeDatos::ejecutar(
            "UPDATE partidos
             SET goles_local = :gl,
                 goles_visitante = :gv,
                 estado = 'finalizado'
             WHERE id = :id",
            ['gl' => $golesLocal, 'gv' => $golesVisitante, 'id' => $id]
        );
    }

    public static function eliminar(int $id): void
    {
        BaseDeDatos::ejecutar('DELETE FROM partidos WHERE id = :id', ['id' => $id]);
    }
}
