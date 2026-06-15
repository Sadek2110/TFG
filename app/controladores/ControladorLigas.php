<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Liga.php';
require_once RUTA_APP . '/modelos/Equipo.php';
require_once RUTA_APP . '/modelos/Partido.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorLigas extends Controlador
{
    public function listar(): void
    {
        $this->ver('ligas/listar', [
            'titulo' => 'Ligas',
            'ligas'  => Liga::listar(),
        ]);
    }

    public function detalle(string $id): void
    {
        $liga = Liga::buscarPorId((int) $id);
        if ($liga === null) {
            $this->noEncontrado();
        }
        $this->ver('ligas/detalle', [
            'titulo'        => $liga['nombre'] . ' ' . $liga['temporada'],
            'liga'          => $liga,
            'clasificacion' => Liga::clasificacion((int) $liga['id']),
            'partidos'      => Partido::listar((int) $liga['id']),
            'equipos'       => Liga::equiposInscritos((int) $liga['id']),
            'puedeInscribir'=> Sesion::esAdministrador(),
            'equiposLibres' => $this->equiposNoInscritos((int) $liga['id']),
        ]);
    }

    public function formularioCrear(): void
    {
        $this->exigirAdministrador();
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);
        $this->ver('ligas/formulario', [
            'titulo'  => 'Nueva liga',
            'errores' => $errores,
        ]);
        limpiar_viejos();
    }

    public function crear(): void
    {
        $this->exigirAdministrador();
        $this->exigirPost();

        $datos = [
            'nombre'      => trim((string) ($_POST['nombre']      ?? '')),
            'temporada'   => trim((string) ($_POST['temporada']   ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        ];

        $validador = (new Validador($datos))
            ->obligatorio('nombre', 'El nombre')
            ->longitudMaxima('nombre', 80, 'El nombre')
            ->obligatorio('temporada', 'La temporada')
            ->longitudMaxima('temporada', 20, 'La temporada')
            ->longitudMaxima('descripcion', 500, 'La descripción');

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/ligas/crear');
        }

        $idLiga = Liga::crear($datos['nombre'], $datos['temporada'], $datos['descripcion']);
        limpiar_viejos();
        Sesion::flash('exito', 'Liga creada.');
        $this->redirigir('/ligas/' . $idLiga);
    }

    public function inscribirEquipo(string $id): void
    {
        $this->exigirAdministrador();
        $this->exigirPost();

        $idLiga   = (int) $id;
        $idEquipo = (int) ($_POST['id_equipo'] ?? 0);

        if ($idEquipo === 0 || Liga::buscarPorId($idLiga) === null || Equipo::buscarPorId($idEquipo) === null) {
            Sesion::flash('error', 'Equipo o liga inválidos.');
            $this->redirigir('/ligas/' . $idLiga);
        }

        Liga::inscribirEquipo($idLiga, $idEquipo);
        Sesion::flash('exito', 'Equipo inscrito en la liga.');
        $this->redirigir('/ligas/' . $idLiga);
    }

    // Equipos que aún no están en la liga, para el desplegable de inscripción.
    private function equiposNoInscritos(int $idLiga): array
    {
        return BaseDeDatos::todos(
            'SELECT e.id, e.nombre
             FROM equipos e
             WHERE e.id NOT IN (SELECT id_equipo FROM ligas_equipos WHERE id_liga = :l)
             ORDER BY e.nombre',
            ['l' => $idLiga]
        );
    }
}
