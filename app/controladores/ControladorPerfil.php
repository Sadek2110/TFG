<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Usuario.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorPerfil extends Controlador
{
    public function mostrar(): void
    {
        $this->exigirAutenticacion();
        $usuario = Usuario::buscarPorId(Sesion::idUsuario());
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);
        $this->ver('perfil/mostrar', [
            'titulo'  => 'Mi perfil',
            'usuario' => $usuario,
            'errores' => $errores,
        ]);
        limpiar_viejos();
    }

    public function actualizar(): void
    {
        $this->exigirAutenticacion();
        $this->exigirPost();

        $idUsuario = Sesion::idUsuario();

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
            ->email('email');

        $cambiaContrasena = $datos['contrasena'] !== '' || $datos['contrasena2'] !== '';
        if ($cambiaContrasena) {
            $validador
                ->longitudMinima('contrasena', 8, 'La contraseña')
                ->igualA('contrasena2', 'contrasena', 'La confirmación de la contraseña');
        }

        if ($validador->valido() && Usuario::emailEnUso($datos['email'], $idUsuario)) {
            $validador->anadirError('email', 'Ese correo ya está siendo utilizado por otra cuenta.');
        }

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/perfil');
        }

        Usuario::actualizarPerfil($idUsuario, $datos['nombre'], $datos['email']);
        if ($cambiaContrasena) {
            Usuario::actualizarContrasena($idUsuario, $datos['contrasena']);
        }

        // Refrescar el usuario en sesión por si cambió nombre/email.
        $usuario = Usuario::buscarPorId($idUsuario);
        Sesion::iniciar($usuario);

        limpiar_viejos();
        Sesion::flash('exito', 'Perfil actualizado.');
        $this->redirigir('/perfil');
    }
}
