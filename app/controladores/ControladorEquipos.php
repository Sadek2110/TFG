<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Equipo.php';
require_once RUTA_APP . '/modelos/MiembroEquipo.php';
require_once RUTA_APP . '/modelos/Usuario.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorEquipos extends Controlador
{
    public function listar(): void
    {
        $equipos = Equipo::listar();
        $idUsuario = Sesion::idUsuario();
        $equipoCapitaneado = $idUsuario !== null ? Equipo::equipoCapitaneadoPorUsuario($idUsuario) : null;
        $this->ver('equipos/listar', [
            'titulo'            => 'Equipos',
            'equipos'           => $equipos,
            'equipoCapitaneado' => $equipoCapitaneado,
            'puedeCrearEquipo'  => $idUsuario !== null && $equipoCapitaneado === null,
        ]);
    }

    public function detalle(string $id): void
    {
        $equipo = Equipo::buscarPorId((int) $id);
        if ($equipo === null) {
            $this->noEncontrado();
        }
        $miembros = MiembroEquipo::listarDeEquipo((int) $equipo['id']);
        $idUsuario = Sesion::idUsuario();
        $esCapitan = $idUsuario !== null && (int) $equipo['id_capitan'] === $idUsuario;
        $esIntegrante = $idUsuario !== null && MiembroEquipo::existe((int) $equipo['id'], $idUsuario);
        $equipoUsuario = $idUsuario !== null ? MiembroEquipo::equipoDeUsuario($idUsuario) : null;
        $puedeGestionar = $esCapitan || Sesion::esAdministrador();

        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);

        $this->ver('equipos/detalle', [
            'titulo'         => $equipo['nombre'],
            'equipo'         => $equipo,
            'miembros'       => $miembros,
            'puedeGestionar' => $puedeGestionar,
            'esIntegrante'   => $esIntegrante,
            'puedeUnirse'    => $idUsuario !== null && !$esIntegrante && $equipoUsuario === null,
            'equipoUsuario'  => $equipoUsuario,
            'errores'        => $errores,
        ]);
        limpiar_viejos();
    }

    public function formularioCrear(): void
    {
        $this->exigirAutenticacion();
        $this->redirigirSiYaCapitanea();
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);
        $this->ver('equipos/formulario', [
            'titulo'  => 'Crear equipo',
            'equipo'  => null,
            'errores' => $errores,
            'accion'  => url('/equipos/crear'),
        ]);
        limpiar_viejos();
    }

    public function crear(): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        $this->redirigirSiYaCapitanea();

        $datos = $this->datosFormulario();
        $validador = $this->validarEquipo($datos);

        if ($validador->valido() && Equipo::buscarPorNombre($datos['nombre']) !== null) {
            $validador->anadirError('nombre', 'Ya existe un equipo con este nombre.');
        }

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/equipos/crear');
        }

        $idEquipo = Equipo::crear(
            $datos['nombre'],
            $datos['ciudad'],
            $datos['descripcion'],
            Sesion::idUsuario()
        );
        // El capitán es miembro por defecto.
        MiembroEquipo::anadir($idEquipo, Sesion::idUsuario(), null, 'Capitán');
        limpiar_viejos();
        Sesion::flash('exito', 'Equipo creado correctamente.');
        $this->redirigir('/equipos/' . $idEquipo);
    }

    public function unirse(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();

        $equipo = Equipo::buscarPorId((int) $id);
        if ($equipo === null) {
            $this->noEncontrado();
        }

        $idUsuario = Sesion::idUsuario();
        if (MiembroEquipo::existe((int) $equipo['id'], $idUsuario)) {
            Sesion::flash('info', 'Ya formas parte de este equipo.');
            $this->redirigir('/equipos/' . $equipo['id']);
        }

        $equipoActual = MiembroEquipo::equipoDeUsuario($idUsuario);
        if ($equipoActual !== null) {
            Sesion::flash('error', 'Ya perteneces a un equipo. No puedes unirte a otro.');
            $this->redirigir('/equipos/' . $equipo['id']);
        }

        MiembroEquipo::anadir((int) $equipo['id'], $idUsuario, null, '');
        Sesion::flash('exito', 'Te has unido al equipo.');
        $this->redirigir('/equipos/' . $equipo['id']);
    }

    public function formularioEditar(string $id): void
    {
        $this->exigirAutenticacion();
        $equipo = $this->equipoGestionable((int) $id);

        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);

        $this->ver('equipos/formulario', [
            'titulo'  => 'Editar ' . $equipo['nombre'],
            'equipo'  => $equipo,
            'errores' => $errores,
            'accion'  => url('/equipos/' . $equipo['id'] . '/editar'),
        ]);
        limpiar_viejos();
    }

    public function editar(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        $equipo = $this->equipoGestionable((int) $id);

        $datos = $this->datosFormulario();
        $validador = $this->validarEquipo($datos);

        if ($validador->valido()) {
            $otro = Equipo::buscarPorNombre($datos['nombre']);
            if ($otro !== null && (int) $otro['id'] !== (int) $equipo['id']) {
                $validador->anadirError('nombre', 'Ya existe un equipo con este nombre.');
            }
        }

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/equipos/' . $equipo['id'] . '/editar');
        }

        Equipo::actualizar((int) $equipo['id'], $datos['nombre'], $datos['ciudad'], $datos['descripcion']);
        limpiar_viejos();
        Sesion::flash('exito', 'Equipo actualizado.');
        $this->redirigir('/equipos/' . $equipo['id']);
    }

    public function eliminar(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        $equipo = $this->equipoGestionable((int) $id);

        Equipo::eliminar((int) $equipo['id']);
        Sesion::flash('info', 'Equipo eliminado.');
        $this->redirigir('/equipos');
    }

    public function anadirMiembro(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        $equipo = $this->equipoGestionable((int) $id);

        $email    = trim((string) ($_POST['email']    ?? ''));
        $dorsal   = trim((string) ($_POST['dorsal']   ?? ''));
        $posicion = trim((string) ($_POST['posicion'] ?? ''));

        $validador = (new Validador(['email' => $email, 'dorsal' => $dorsal, 'posicion' => $posicion]))
            ->obligatorio('email', 'El correo')
            ->email('email')
            ->entero('dorsal', 'El dorsal', 1, 99);

        if ($validador->valido()) {
            $usuario = Usuario::buscarPorEmail($email);
            if ($usuario === null) {
                $validador->anadirError('email', 'No existe ningún usuario con este correo.');
            } elseif (MiembroEquipo::existe((int) $equipo['id'], (int) $usuario['id'])) {
                $validador->anadirError('email', 'Ese usuario ya pertenece al equipo.');
            } elseif (MiembroEquipo::equipoDeUsuario((int) $usuario['id']) !== null) {
                $validador->anadirError('email', 'Ese usuario ya pertenece a otro equipo.');
            }
        }

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            Sesion::flash('error', current($validador->errores()));
            $this->redirigir('/equipos/' . $equipo['id']);
        }

        MiembroEquipo::anadir(
            (int) $equipo['id'],
            (int) $usuario['id'],
            $dorsal === '' ? null : (int) $dorsal,
            $posicion
        );
        Sesion::flash('exito', 'Miembro añadido al equipo.');
        $this->redirigir('/equipos/' . $equipo['id']);
    }

    public function actualizarMiembro(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        $equipo = $this->equipoGestionable((int) $id);

        $datos = [
            'id_usuario' => (string) ($_POST['id_usuario'] ?? ''),
            'dorsal'     => trim((string) ($_POST['dorsal'] ?? '')),
            'posicion'   => trim((string) ($_POST['posicion'] ?? '')),
            'titular'    => isset($_POST['titular']) ? '1' : '0',
        ];

        $validador = (new Validador($datos))
            ->obligatorio('id_usuario', 'El jugador')
            ->entero('id_usuario', 'El jugador', 1)
            ->entero('dorsal', 'El dorsal', 1, 99)
            ->longitudMaxima('posicion', 40, 'La posiciÃ³n');

        if ($validador->valido() && !MiembroEquipo::existe((int) $equipo['id'], (int) $datos['id_usuario'])) {
            $validador->anadirError('id_usuario', 'El jugador no pertenece a este equipo.');
        }

        if (!$validador->valido()) {
            Sesion::flash('error', current($validador->errores()));
            $this->redirigir('/equipos/' . $equipo['id']);
        }

        MiembroEquipo::actualizar(
            (int) $equipo['id'],
            (int) $datos['id_usuario'],
            $datos['dorsal'] === '' ? null : (int) $datos['dorsal'],
            $datos['posicion'],
            $datos['titular'] === '1'
        );
        Sesion::flash('exito', 'Datos del jugador actualizados.');
        $this->redirigir('/equipos/' . $equipo['id']);
    }

    public function quitarMiembro(string $id): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();
        $equipo = $this->equipoGestionable((int) $id);

        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        if ($idUsuario === 0) {
            Sesion::flash('error', 'Miembro inválido.');
            $this->redirigir('/equipos/' . $equipo['id']);
        }
        if ($idUsuario === (int) $equipo['id_capitan']) {
            Sesion::flash('error', 'No puedes quitar al capitán del equipo.');
            $this->redirigir('/equipos/' . $equipo['id']);
        }

        MiembroEquipo::quitar((int) $equipo['id'], $idUsuario);
        Sesion::flash('info', 'Miembro retirado del equipo.');
        $this->redirigir('/equipos/' . $equipo['id']);
    }

    private function datosFormulario(): array
    {
        return [
            'nombre'      => trim((string) ($_POST['nombre']      ?? '')),
            'ciudad'      => trim((string) ($_POST['ciudad']      ?? '')),
            'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        ];
    }

    private function validarEquipo(array $datos): Validador
    {
        return (new Validador($datos))
            ->obligatorio('nombre', 'El nombre')
            ->longitudMaxima('nombre', 80, 'El nombre')
            ->longitudMaxima('ciudad', 80, 'La ciudad')
            ->longitudMaxima('descripcion', 500, 'La descripción');
    }

    // Comprueba que el equipo existe y que el usuario actual puede gestionarlo.
    private function equipoGestionable(int $id): array
    {
        $equipo = Equipo::buscarPorId($id);
        if ($equipo === null) {
            $this->noEncontrado();
        }
        $idUsuario = Sesion::idUsuario();
        $puede = ($idUsuario !== null && (int) $equipo['id_capitan'] === $idUsuario) || Sesion::esAdministrador();
        if (!$puede) {
            http_response_code(403);
            $this->ver('errores/403');
            exit;
        }
        return $equipo;
    }

    private function redirigirSiYaCapitanea(): void
    {
        $equipo = Equipo::equipoCapitaneadoPorUsuario(Sesion::idUsuario());
        if ($equipo !== null) {
            Sesion::flash('aviso', 'Ya eres capitán de un equipo. Gestiona tu equipo desde aquí.');
            $this->redirigir('/equipos/' . $equipo['id']);
        }
    }
}
