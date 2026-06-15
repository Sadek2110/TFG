<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Usuario.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorAuth extends Controlador
{
    public function formularioRegistro(): void
    {
        if (Sesion::autenticado()) {
            $this->redirigir('/perfil');
        }
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);
        $this->ver('auth/registro', [
            'titulo'  => 'Crear cuenta',
            'errores' => $errores,
        ]);
        limpiar_viejos();
    }

    public function registrar(): void
    {
        $this->exigirPost();

        $datos = [
            'nombre'      => trim((string) ($_POST['nombre'] ?? '')),
            'email'       => trim((string) ($_POST['email'] ?? '')),
            'contrasena'  => (string) ($_POST['contrasena']  ?? ''),
            'contrasena2' => (string) ($_POST['contrasena2'] ?? ''),
        ];

        $validador = (new Validador($datos))
            ->obligatorio('nombre', 'El nombre')
            ->longitudMaxima('nombre', 80, 'El nombre')
            ->obligatorio('email', 'El correo')
            ->email('email')
            ->obligatorio('contrasena', 'La contraseña')
            ->longitudMinima('contrasena', 8, 'La contraseña')
            ->igualA('contrasena2', 'contrasena', 'La confirmación de la contraseña');

        if ($validador->valido() && Usuario::emailEnUso($datos['email'])) {
            $validador->anadirError('email', 'Ya existe una cuenta con este correo.');
        }

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/registro');
        }

        $id = Usuario::crear($datos['nombre'], $datos['email'], $datos['contrasena']);
        $usuario = Usuario::buscarPorId($id);
        Sesion::iniciar($usuario);
        limpiar_viejos();
        Sesion::flash('exito', '¡Bienvenido a FastPlay, ' . $usuario['nombre'] . '!');
        $this->redirigir('/perfil');
    }

    public function formularioInicio(): void
    {
        if (Sesion::autenticado()) {
            $this->redirigir('/perfil');
        }
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);
        $this->ver('auth/iniciar-sesion', [
            'titulo'  => 'Iniciar sesión',
            'errores' => $errores,
        ]);
        limpiar_viejos();
    }

    public function iniciar(): void
    {
        $this->exigirPost();

        $email      = trim((string) ($_POST['email'] ?? ''));
        $contrasena = (string) ($_POST['contrasena'] ?? '');

        $validador = (new Validador(['email' => $email, 'contrasena' => $contrasena]))
            ->obligatorio('email', 'El correo')
            ->email('email')
            ->obligatorio('contrasena', 'La contraseña');

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos(['email' => $email]);
            $this->redirigir('/iniciar-sesion');
        }

        $usuario = Usuario::verificarCredenciales($email, $contrasena);
        if ($usuario === null) {
            $_SESSION['errores'] = ['email' => 'Correo o contraseña incorrectos.'];
            guardar_viejos(['email' => $email]);
            $this->redirigir('/iniciar-sesion');
        }

        Sesion::iniciar($usuario);
        limpiar_viejos();
        Sesion::flash('exito', 'Sesión iniciada.');
        $this->redirigir('/perfil');
    }

    public function cerrar(): void
    {
        $this->exigirPost();
        Sesion::cerrar();
        // Tras destruir la sesión necesitamos una nueva para mostrar el flash.
        iniciar_sesion();
        Sesion::flash('info', 'Sesión cerrada. ¡Hasta pronto!');
        $this->redirigir('/');
    }
}
