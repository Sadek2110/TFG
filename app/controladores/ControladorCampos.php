<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Campo.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorCampos extends Controlador
{
    public function listar(): void
    {
        $this->ver('campos/listar', [
            'titulo' => 'Campos',
            'campos' => Campo::listar(),
        ]);
    }

    public function formularioCrear(): void
    {
        $this->exigirAdministrador();
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);
        $this->ver('campos/formulario', [
            'titulo'  => 'Nuevo campo',
            'errores' => $errores,
        ]);
        limpiar_viejos();
    }

    public function crear(): void
    {
        $this->exigirAdministrador();
        $this->exigirPost();

        $datos = [
            'nombre'     => trim((string) ($_POST['nombre']     ?? '')),
            'direccion'  => trim((string) ($_POST['direccion']  ?? '')),
            'ciudad'     => trim((string) ($_POST['ciudad']     ?? '')),
            'superficie' => trim((string) ($_POST['superficie'] ?? '')),
        ];

        $validador = (new Validador($datos))
            ->obligatorio('nombre', 'El nombre')
            ->longitudMaxima('nombre', 80, 'El nombre')
            ->longitudMaxima('direccion', 120, 'La dirección')
            ->longitudMaxima('ciudad', 80, 'La ciudad')
            ->enLista('superficie', ['', 'Hierba natural', 'Hierba artificial', 'Tierra', 'Cemento'], 'La superficie');

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/campos/crear');
        }

        Campo::crear($datos['nombre'], $datos['direccion'], $datos['ciudad'], $datos['superficie']);
        limpiar_viejos();
        Sesion::flash('exito', 'Campo creado.');
        $this->redirigir('/campos');
    }

    public function eliminar(string $id): void
    {
        $this->exigirAdministrador();
        $this->exigirPost();
        Campo::eliminar((int) $id);
        Sesion::flash('info', 'Campo eliminado.');
        $this->redirigir('/campos');
    }
}
