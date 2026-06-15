<?php
declare(strict_types=1);

class ControladorAdmin extends Controlador
{
    public function panel(): void
    {
        $this->exigirAdministrador();

        $estadisticas = [
            'usuarios' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM usuarios'),
            'equipos'  => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM equipos'),
            'partidos' => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM partidos'),
            'campos'   => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM campos'),
            'ligas'    => (int) BaseDeDatos::valor('SELECT COUNT(*) FROM ligas'),
        ];

        $usuarios = BaseDeDatos::todos(
            'SELECT id, nombre, email, rol, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC'
        );

        $this->ver('admin/panel', [
            'titulo'        => 'Panel de administración',
            'estadisticas'  => $estadisticas,
            'usuarios'      => $usuarios,
        ]);
    }
}
