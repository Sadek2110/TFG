<?php
declare(strict_types=1);

require_once RUTA_APP . '/modelos/Campo.php';
require_once RUTA_APP . '/nucleo/Validador.php';

class ControladorCampos extends Controlador
{
    public function listar(): void
    {
        $campos = $this->completarCamposDemo(Campo::listar());
        $this->ver('campos/listar', [
            'titulo' => 'Campos',
            'campos' => $campos,
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
            'foto'       => trim((string) ($_POST['foto']       ?? '')),
        ];

        $validador = (new Validador($datos))
            ->obligatorio('nombre', 'El nombre')
            ->longitudMaxima('nombre', 80, 'El nombre')
            ->longitudMaxima('direccion', 120, 'La dirección')
            ->longitudMaxima('ciudad', 80, 'La ciudad')
            ->longitudMaxima('foto', 255, 'La foto')
            ->enLista('superficie', ['', 'Hierba natural', 'Hierba artificial', 'Tierra', 'Cemento'], 'La superficie');

        if (!$validador->valido()) {
            $_SESSION['errores'] = $validador->errores();
            guardar_viejos($datos);
            Sesion::flash('error', 'Revisa los campos del formulario.');
            $this->redirigir('/campos/crear');
        }

        Campo::crear($datos['nombre'], $datos['direccion'], $datos['ciudad'], $datos['superficie'], $datos['foto']);
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

    private function completarCamposDemo(array $campos): array
    {
        $catalogo = [
            ['nombre' => 'Estadio Murube', 'direccion' => 'Av. de los Reyes Católicos', 'ciudad' => 'Ceuta', 'superficie' => 'Hierba natural', 'foto' => '/imagenes/campos/alfonso-murube.jpg'],
            ['nombre' => 'Polideportivo del Sur', 'direccion' => 'Calle del Mar', 'ciudad' => 'Ceuta', 'superficie' => 'Hierba artificial', 'foto' => '/imagenes/campos/emilio-cozar.jpg'],
            ['nombre' => 'Campo Municipal Norte', 'direccion' => 'Calle Real', 'ciudad' => 'Ceuta', 'superficie' => 'Tierra', 'foto' => '/imagenes/campos/jose-benoliel.jpg'],
            ['nombre' => 'Campo Aiman Mohamed', 'direccion' => 'Avenida África', 'ciudad' => 'Ceuta', 'superficie' => 'Hierba artificial', 'foto' => '/imagenes/campos/aiman-mohamed.webp'],
            ['nombre' => 'Campo José Pirri', 'direccion' => 'Barriada San José', 'ciudad' => 'Ceuta', 'superficie' => 'Cemento', 'foto' => '/imagenes/campos/jose-pirri.jpeg'],
            ['nombre' => 'Campo Tuhami Al-Lal', 'direccion' => 'Calle Independencia', 'ciudad' => 'Ceuta', 'superficie' => 'Hierba natural', 'foto' => '/imagenes/campos/tuhami-al-lal.webp'],
        ];

        $nombresExistentes = array_map(
            static fn (array $campo): string => strtolower((string) ($campo['nombre'] ?? '')),
            $campos
        );

        foreach ($catalogo as $campoDemo) {
            if (count($campos) >= 6) {
                break;
            }
            if (in_array(strtolower($campoDemo['nombre']), $nombresExistentes, true)) {
                continue;
            }
            $campoDemo['id'] = null;
            $campos[] = $campoDemo;
            $nombresExistentes[] = strtolower($campoDemo['nombre']);
        }

        return $campos;
    }
}
