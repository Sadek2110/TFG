<?php
// Endpoints JSON que consume el cliente vía fetch (AJAX). No renderizan HTML:
// devuelven datos para que el JavaScript reconstruya el DOM en el navegador.

declare(strict_types=1);

class ControladorApi extends Controlador
{
    // GET /api/contexto
    // Devuelve un resumen del estado del sitio adaptado al ROL de quien
    // pregunta (visitante, jugador, capitán o administrador). Es la fuente
    // de datos del panel contextual de la portada (public/js/panel-contextual.js).
    public function contexto(): void
    {
        $usuario = Sesion::usuario();
        $ahora   = new DateTimeImmutable('now');

        if ($usuario === null) {
            $this->json([
                'rol'           => 'visitante',
                'nombre'        => null,
                'titulo'        => 'Bienvenido a FastPlay',
                'mensaje'       => 'Regístrate para crear equipos, organizar partidos y unirte a ligas.',
                'resumen'       => $this->resumenPublico(),
                'acciones'      => [
                    ['texto' => 'Crear cuenta',    'url' => url('/registro')],
                    ['texto' => 'Iniciar sesión',  'url' => url('/iniciar-sesion')],
                    ['texto' => 'Ver equipos',     'url' => url('/equipos')],
                ],
                'hora_servidor' => $ahora->format(DateTimeInterface::ATOM),
            ]);
        }

        $id  = (int) $usuario['id'];
        $rol = ($usuario['rol'] === 'administrador') ? 'administrador' : $this->rolFuncional($id);

        $datos = match ($rol) {
            'administrador' => $this->contextoAdministrador(),
            'capitan'       => $this->contextoCapitan($id),
            default         => $this->contextoJugador($id),
        };

        $this->json($datos + [
            'rol'           => $rol,
            'nombre'        => $usuario['nombre'],
            'hora_servidor' => $ahora->format(DateTimeInterface::ATOM),
        ]);
    }

    // Un "capitán" no es un rol de la tabla usuarios: es un jugador que
    // administra al menos un equipo. Lo derivamos en tiempo de consulta.
    private function rolFuncional(int $idUsuario): string
    {
        $equipos = (int) BaseDeDatos::valor(
            'SELECT COUNT(*) FROM equipos WHERE id_capitan = ?',
            [$idUsuario]
        );
        return $equipos > 0 ? 'capitan' : 'jugador';
    }

    private function resumenPublico(): array
    {
        return [
            ['etiqueta' => 'Equipos',  'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM equipos')],
            ['etiqueta' => 'Partidos', 'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM partidos')],
            ['etiqueta' => 'Ligas',    'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM ligas')],
        ];
    }

    private function contextoAdministrador(): array
    {
        return [
            'titulo'   => 'Panel de control',
            'mensaje'  => 'Tienes acceso completo: usuarios, equipos, ligas, campos y partidos.',
            'resumen'  => [
                ['etiqueta' => 'Usuarios', 'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM usuarios')],
                ['etiqueta' => 'Equipos',  'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM equipos')],
                ['etiqueta' => 'Partidos', 'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM partidos')],
                ['etiqueta' => 'Ligas',    'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM ligas')],
            ],
            'acciones' => [
                ['texto' => 'Ir al panel admin', 'url' => url('/admin')],
                ['texto' => 'Nuevo campo',       'url' => url('/campos/crear')],
                ['texto' => 'Nueva liga',        'url' => url('/ligas/crear')],
            ],
        ];
    }

    private function contextoCapitan(int $idUsuario): array
    {
        $misEquipos = (int) BaseDeDatos::valor(
            'SELECT COUNT(*) FROM equipos WHERE id_capitan = ?',
            [$idUsuario]
        );
        $proximos = (int) BaseDeDatos::valor(
            "SELECT COUNT(*) FROM partidos p
             JOIN equipos e ON e.id IN (p.id_equipo_local, p.id_equipo_visitante)
             WHERE e.id_capitan = ? AND p.estado = 'programado'",
            [$idUsuario]
        );

        return [
            'titulo'   => 'Tu zona de capitán',
            'mensaje'  => 'Gestiona tus equipos y organiza nuevos partidos.',
            'resumen'  => [
                ['etiqueta' => 'Tus equipos',        'valor' => $misEquipos],
                ['etiqueta' => 'Partidos previstos', 'valor' => $proximos],
            ],
            'acciones' => [
                ['texto' => 'Nuevo equipo',   'url' => url('/equipos/crear')],
                ['texto' => 'Nuevo partido',  'url' => url('/partidos/crear')],
                ['texto' => 'Mis equipos',    'url' => url('/equipos')],
            ],
        ];
    }

    private function contextoJugador(int $idUsuario): array
    {
        $misEquipos = (int) BaseDeDatos::valor(
            'SELECT COUNT(*) FROM miembros_equipo WHERE id_usuario = ?',
            [$idUsuario]
        );

        return [
            'titulo'   => 'Tu actividad',
            'mensaje'  => 'Únete a un equipo o crea el tuyo para empezar a competir.',
            'resumen'  => [
                ['etiqueta' => 'Equipos en los que juegas', 'valor' => $misEquipos],
                ['etiqueta' => 'Ligas activas',             'valor' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM ligas')],
            ],
            'acciones' => [
                ['texto' => 'Crear mi equipo', 'url' => url('/equipos/crear')],
                ['texto' => 'Ver partidos',    'url' => url('/partidos')],
                ['texto' => 'Mi perfil',       'url' => url('/perfil')],
            ],
        ];
    }
}
