<?php
declare(strict_types=1);

class ControladorInicio extends Controlador
{
    public function mostrar(): void
    {
        $this->ver('inicio/index', [
            'titulo' => 'FastPlay - Fútbol amateur organizado',
        ]);
    }
}
