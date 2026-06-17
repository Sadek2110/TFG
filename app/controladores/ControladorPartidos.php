<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Partido.php';
require_once RUTA_APP . '/modelos/Equipo.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorPartidos extends Controlador
{
    public function listar(): void
    {
        $partidos = Partido::listar();
        $fechaSeleccionada = $this->fechaSeleccionada();
        $partidosDia = array_values(array_filter($partidos, static function (array $partido) use ($fechaSeleccionada): bool {
            return substr((string) $partido['fecha_partido'], 0, 10) === $fechaSeleccionada;
        }));
        $idUsuario = Sesion::idUsuario();
        $puedeSolicitar = Sesion::esAdministrador()
            || ($idUsuario !== null && Equipo::capitaneaAlgunEquipo($idUsuario));

        $this->ver('partidos/listar', [
            'titulo'            => 'Partidos',
            'partidos'          => $partidos,
            'calendario'        => $this->calendario($partidos, $fechaSeleccionada),
            'fechaSeleccionada' => $fechaSeleccionada,
            'partidosDia'       => $partidosDia,
            'puedeSolicitar'    => $puedeSolicitar,
        ]);
    }

    public function detalle(string $id): void
    {
        $partido = Partido::buscarPorId((int) $id);
        if ($partido === null) {
            $this->noEncontrado();
        }
        $idUsuario = Sesion::idUsuario();
        $puedeResultado = Sesion::esAdministrador()
            || ($idUsuario !== null && (
                    (int) $partido['capitan_local']     === $idUsuario
                 || (int) $partido['capitan_visitante'] === $idUsuario
                ));
        $puedeEliminar = Sesion::esAdministrador()
            || ($idUsuario !== null && (int) $partido['capitan_local'] === $idUsuario);

        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);

        $this->ver('partidos/detalle', [
            'titulo'         => 'Partido #' . $partido['id'],
            'partido'        => $partido,
            'puedeResultado' => $puedeResultado,
            'puedeEliminar'  => $puedeEliminar,
            'errores'        => $errores,
        ]);
        limpiar_viejos();
    }

    public function formularioCrear(): void
    {
        $this->exigirAutenticacion();
        if (!Sesion::esAdministrador() && !Equipo::capitaneaAlgunEquipo(Sesion::idUsuario())) {
            http_response_code(403);
            $this->ver('errores/403');
            exit;
        }

        $equipos = Equipo::nombres();
        if (count($equipos) < 2) {
            Sesion::flash('aviso', 'Necesitas al menos dos equipos registrados para crear un partido.');
            $this->redirigir('/partidos');
        }

        require_once RUTA_APP . '/modelos/Campo.php';
        require_once RUTA_APP . '/modelos/Liga.php';

        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);

        $this->ver('partidos/formulario', [
            'titulo'  => 'Crear partido',
            'equipos' => $equipos,
            'campos'  => Campo::nombres(),
            'ligas'   => Liga::nombres(),
            'errores' => $errores,
        ]);
        limpiar_viejos();
    }

    public function crear(): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        if (!Sesion::esAdministrador() && !Equipo::capitaneaAlgunEquipo(Sesion::idUsuario())) {
            http_response_code(403);
            $this->ver('errores/403');
            exit;
        }

        $datos = [
            'id_equipo_local'     => (string) ($_POST['id_equipo_local']     ?? ''),
            'id_equipo_visitante' => (string) ($_POST['id_equipo_visitante'] ?? ''),
            'fecha_partido'       => (string) ($_POST['fecha_partido']       ?? ''),
            'id_campo'            => (string) ($_POST['id_campo']            ?? ''),
            'id_liga'             => (string) ($_POST['id_liga']             ?? ''),
        ];

        $validador = (new Validador($datos))
            ->obligatorio('id_equipo_local',     'El equipo local')
            ->obligatorio('id_equipo_visitante', 'El equipo visitante')
            ->obligatorio('fecha_partido',       'La fecha del partido')
            ->fecha('fecha_partido',             'La fecha del partido')
            ->entero('id_equipo_local',          'El equipo local')
            ->entero('id_equipo_visitante',      'El equipo visitante')
            ->entero('id_campo',                 'El campo')
            ->entero('id_liga',                  'La liga');

        if ($validador->valido() && $datos['id_equipo_local'] === $datos['id_equipo_visitante']) {
            $validador->anadirError('id_equipo_visitante', 'El equipo local y el visitante no pueden ser el mismo.');
        }

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/partidos/crear');
        }

        $idLocal     = (int) $datos['id_equipo_local'];
        $idVisitante = (int) $datos['id_equipo_visitante'];
        $idCampo     = $datos['id_campo'] !== '' ? (int) $datos['id_campo'] : null;
        $idLiga      = $datos['id_liga']  !== '' ? (int) $datos['id_liga']  : null;

        // Solo el capitán de uno de los equipos (o un admin) puede crear el partido.
        if (!Sesion::esAdministrador()) {
            $idUsuario = Sesion::idUsuario();
            if (!Equipo::esCapitan($idLocal, $idUsuario) && !Equipo::esCapitan($idVisitante, $idUsuario)) {
                http_response_code(403);
                $this->ver('errores/403');
                exit;
            }
        }

        $idPartido = Partido::crear(
            $idLocal,
            $idVisitante,
            str_replace('T', ' ', $datos['fecha_partido']),
            $idCampo,
            $idLiga
        );

        limpiar_viejos();
        Sesion::flash('exito', 'Partido programado.');
        $this->redirigir('/partidos/' . $idPartido);
    }

    public function registrarResultado(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();

        $partido = Partido::buscarPorId((int) $id);
        if ($partido === null) {
            $this->noEncontrado();
        }

        $idUsuario = Sesion::idUsuario();
        $puede = Sesion::esAdministrador()
            || (int) $partido['capitan_local']     === $idUsuario
            || (int) $partido['capitan_visitante'] === $idUsuario;
        if (!$puede) {
            http_response_code(403);
            $this->ver('errores/403');
            exit;
        }

        $datos = [
            'goles_local'     => (string) ($_POST['goles_local']     ?? ''),
            'goles_visitante' => (string) ($_POST['goles_visitante'] ?? ''),
        ];

        $validador = (new Validador($datos))
            ->obligatorio('goles_local',     'Los goles del equipo local')
            ->obligatorio('goles_visitante', 'Los goles del equipo visitante')
            ->entero('goles_local',     'Los goles del equipo local',     0, 99)
            ->entero('goles_visitante', 'Los goles del equipo visitante', 0, 99);

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            Sesion::flash('error', 'Revisa los goles introducidos.');
            $this->redirigir('/partidos/' . $partido['id']);
        }

        Partido::registrarResultado(
            (int) $partido['id'],
            (int) $datos['goles_local'],
            (int) $datos['goles_visitante']
        );
        Sesion::flash('exito', 'Resultado registrado.');
        $this->redirigir('/partidos/' . $partido['id']);
    }

    public function eliminar(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();

        $partido = Partido::buscarPorId((int) $id);
        if ($partido === null) {
            $this->noEncontrado();
        }
        $idUsuario = Sesion::idUsuario();
        $puede = Sesion::esAdministrador() || (int) $partido['capitan_local'] === $idUsuario;
        if (!$puede) {
            http_response_code(403);
            $this->ver('errores/403');
            exit;
        }

        Partido::eliminar((int) $partido['id']);
        Sesion::flash('info', 'Partido eliminado.');
        $this->redirigir('/partidos');
    }

    private function fechaSeleccionada(): string
    {
        $fecha = (string) ($_GET['fecha'] ?? date('Y-m-d'));
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $fecha);
        if ($dt === false || $dt->format('Y-m-d') !== $fecha) {
            return date('Y-m-d');
        }
        return $fecha;
    }

    private function calendario(array $partidos, string $fechaSeleccionada): array
    {
        $base = new DateTimeImmutable($fechaSeleccionada);
        $inicioMes = $base->modify('first day of this month');
        $finMes = $base->modify('last day of this month');
        $inicio = $inicioMes->modify('-' . ((int) $inicioMes->format('N') - 1) . ' days');
        $fin = $finMes->modify('+' . (7 - (int) $finMes->format('N')) . ' days');
        $hoy = date('Y-m-d');

        $porFecha = [];
        foreach ($partidos as $partido) {
            $fecha = substr((string) $partido['fecha_partido'], 0, 10);
            $porFecha[$fecha][] = $partido;
        }

        $semanas = [];
        $cursor = $inicio;
        while ($cursor <= $fin) {
            $semana = [];
            for ($i = 0; $i < 7; $i++) {
                $fecha = $cursor->format('Y-m-d');
                $eventos = $porFecha[$fecha] ?? [];
                $jugado = false;
                foreach ($eventos as $evento) {
                    if (($evento['estado'] ?? '') === 'finalizado' || $fecha < $hoy) {
                        $jugado = true;
                        break;
                    }
                }
                $semana[] = [
                    'fecha' => $fecha,
                    'dia' => $cursor->format('j'),
                    'mes_actual' => $cursor->format('m') === $base->format('m'),
                    'hoy' => $fecha === $hoy,
                    'seleccionado' => $fecha === $fechaSeleccionada,
                    'tiene_partido' => count($eventos) > 0,
                    'jugado' => $jugado,
                    'total' => count($eventos),
                ];
                $cursor = $cursor->modify('+1 day');
            }
            $semanas[] = $semana;
        }

        return [
            'titulo' => $base->format('m/Y'),
            'anterior' => $base->modify('first day of previous month')->format('Y-m-d'),
            'siguiente' => $base->modify('first day of next month')->format('Y-m-d'),
            'semanas' => $semanas,
        ];
    }
}
