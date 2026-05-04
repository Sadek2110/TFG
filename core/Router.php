<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['GET', $path, $controller, $method];
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['POST', $path, $controller, $method];
    }

    public function delete(string $path, string $controller, string $method): void
    {
        $this->routes[] = ['DELETE', $path, $controller, $method];
    }

    public function dispatch(): void
    {
        try {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            // Support method override for PUT/DELETE via POST
            if ($requestMethod === 'POST' && isset($_POST['_method'])) {
                $requestMethod = strtoupper($_POST['_method']);
            }

            $base = parse_url(APP_URL, PHP_URL_PATH);
            $path = '/' . ltrim(substr($requestUri, strlen($base)), '/');
            $path = $path === '' ? '/' : $path;

            foreach ($this->routes as [$method, $route, $controller, $action]) {
                if ($method !== $requestMethod) continue;

                $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches);
                    $this->run($controller, $action, $matches);
                    return;
                }
            }

            http_response_code(404);
            echo $this->renderError(404, 'Página no encontrada');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo $this->renderError(500, 'Error interno del servidor');
        }
    }

    private function run(string $controller, string $action, array $params): void
    {
        $controller = basename($controller);
        $file = APP_PATH . '/controllers/' . $controller . '.php';
        if (!file_exists($file)) {
            error_log("Controller not found: {$controller}");
            http_response_code(404);
            echo $this->renderError(404, 'Página no encontrada');
            return;
        }
        require_once $file;
        $instance = new $controller();
        $instance->$action(...$params);
    }

    private function renderError(int $code, string $message): string
    {
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
        <title>' . $code . ' — FastPlay</title>
        <script src="https://cdn.tailwindcss.com"></script></head>
        <body class="bg-[#060d09] text-white flex items-center justify-center min-h-screen">
        <div class="text-center">
          <div class="text-8xl font-black text-green-500 mb-4">' . $code . '</div>
          <p class="text-gray-400 text-xl mb-8">' . htmlspecialchars($message) . '</p>
          <a href="' . APP_URL . '/" class="bg-green-600 hover:bg-green-500 text-white font-bold px-8 py-3 rounded-full transition-colors">
            Volver al inicio
          </a>
        </div></body></html>';
    }
}
