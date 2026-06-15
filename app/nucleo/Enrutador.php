<?php
// Enrutador simple basado en una tabla explícita de rutas.
// Cada ruta indica método HTTP, patrón con marcadores {parametro}
// y el par controlador/acción que debe atenderla.

declare(strict_types=1);

class Enrutador
{
    /** @var array<int, array{metodo:string,patron:string,controlador:string,accion:string,regex:string,parametros:string[]}> */
    private array $rutas = [];

    public function get(string $patron, string $controlador, string $accion): void
    {
        $this->anadir('GET', $patron, $controlador, $accion);
    }

    public function post(string $patron, string $controlador, string $accion): void
    {
        $this->anadir('POST', $patron, $controlador, $accion);
    }

    private function anadir(string $metodo, string $patron, string $controlador, string $accion): void
    {
        // Convierte /equipos/{id} en una expresión regular con grupos nombrados.
        $parametros = [];
        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function ($coincidencia) use (&$parametros) {
                $parametros[] = $coincidencia[1];
                return '([^/]+)';
            },
            $patron
        );

        $this->rutas[] = [
            'metodo'       => $metodo,
            'patron'       => $patron,
            'controlador'  => $controlador,
            'accion'       => $accion,
            'regex'        => '#^' . $regex . '$#',
            'parametros'   => $parametros,
        ];
    }

    // Despacha la petición actual a su controlador/acción.
    public function despachar(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($metodo === 'HEAD') {
            $metodo = 'GET';
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = url_base();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        foreach ($this->rutas as $ruta) {
            if ($ruta['metodo'] !== $metodo) {
                continue;
            }
            if (!preg_match($ruta['regex'], $uri, $coincidencias)) {
                continue;
            }
            array_shift($coincidencias);
            $this->invocar($ruta['controlador'], $ruta['accion'], $coincidencias);
            return;
        }

        $this->responder404();
    }

    private function invocar(string $nombreControlador, string $accion, array $parametros): void
    {
        $archivo = RUTA_APP . '/controladores/' . $nombreControlador . '.php';
        if (!is_file($archivo)) {
            throw new RuntimeException("Controlador no encontrado: $nombreControlador");
        }
        require_once $archivo;

        if (!class_exists($nombreControlador)) {
            throw new RuntimeException("Clase no encontrada: $nombreControlador");
        }

        $controlador = new $nombreControlador();
        if (!method_exists($controlador, $accion)) {
            throw new RuntimeException("Acción no encontrada: $nombreControlador::$accion");
        }

        call_user_func_array([$controlador, $accion], $parametros);
    }

    private function responder404(): void
    {
        http_response_code(404);
        ob_start();
        require RUTA_VISTAS . '/errores/404.php';
        $contenido = ob_get_clean();
        $titulo = 'Página no encontrada';
        require RUTA_VISTAS . '/layout.php';
    }
}
