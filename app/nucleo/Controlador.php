<?php
// Controlador base: ofrece helpers comunes a todos los controladores
// (renderizado de vistas, redirecciones, exigir autenticación...).

declare(strict_types=1);

abstract class Controlador
{
    // Renderiza una vista dentro del layout principal.
    // $vista usa el formato 'carpeta/archivo' (sin .php).
    protected function ver(string $vista, array $datos = [], string $layout = 'layout'): void
    {
        $rutaVista = RUTA_VISTAS . '/' . $vista . '.php';
        if (!is_file($rutaVista)) {
            throw new RuntimeException("Vista no encontrada: $vista");
        }

        // Convierte el array en variables locales para la vista.
        extract($datos, EXTR_SKIP);

        // La vista se captura en $contenido y se inyecta en el layout.
        ob_start();
        require $rutaVista;
        $contenido = ob_get_clean();

        require RUTA_VISTAS . '/' . $layout . '.php';
    }

    // Redirección segura mediante cabecera Location.
    protected function redirigir(string $ruta): void
    {
        header('Location: ' . url($ruta));
        exit;
    }

    // Responde con JSON. Usado por los endpoints que consume el cliente
    // mediante fetch (AJAX). Fija el Content-Type y termina la petición.
    protected function json(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function exigirAutenticacion(): void
    {
        if (!Sesion::autenticado()) {
            Sesion::flash('aviso', 'Inicia sesión para continuar.');
            $this->redirigir('/iniciar-sesion');
        }
    }

    protected function exigirAdministrador(): void
    {
        $this->exigirAutenticacion();
        if (!Sesion::esAdministrador()) {
            http_response_code(403);
            $this->ver('errores/403');
            exit;
        }
    }

    // Solo acepta peticiones POST. Las operaciones que cambian datos
    // no deben llegar por GET.
    protected function exigirPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Método no permitido.';
            exit;
        }
        Csrf::exigir();
    }

    protected function noEncontrado(): void
    {
        http_response_code(404);
        $this->ver('errores/404');
        exit;
    }
}
