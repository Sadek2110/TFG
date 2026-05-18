<?php
// FastPlay · enrutador (front controller)

class Router
{
    public static function dispatch(string $url): void
    {
        $url = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $segments = $url === '' ? [] : explode('/', $url);

        $controllerSlug = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $controllerSlug) || !preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
            self::notFound();
            return;
        }

        $controllerClass = ucfirst(strtolower($controllerSlug)) . 'Controller';
        $controllerFile = APP_PATH . '/controllers/' . $controllerClass . '.php';

        if (!file_exists($controllerFile)) {
            self::notFound();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            self::notFound();
            return;
        }

        $controller = new $controllerClass();

        // Métodos privados/protected del Controller base ya quedan bloqueados
        // por ReflectionMethod::isPublic(); aquí sólo descartamos prefijo "_".
        if (strpos($action, '_') === 0 || !method_exists($controller, $action)) {
            self::notFound();
            return;
        }

        try {
            $reflection = new ReflectionMethod($controller, $action);
            if (!$reflection->isPublic() || $reflection->isStatic()
                || $reflection->getDeclaringClass()->getName() === 'Controller') {
                self::notFound();
                return;
            }
            $totalParams = $reflection->getNumberOfParameters();
            $requiredParams = $reflection->getNumberOfRequiredParameters();
            $params = array_slice($params, 0, $totalParams);
            if (count($params) < $requiredParams) {
                self::notFound();
                return;
            }
            $reflection->invokeArgs($controller, $params);
        } catch (Throwable $e) {
            self::serverError($e);
        }
    }

    public static function notFound(): void
    {
        http_response_code(404);
        require_once APP_PATH . '/controllers/HomeController.php';
        (new HomeController())->notFound();
    }

    public static function serverError(Throwable $e): void
    {
        http_response_code(500);
        if (!defined('FASTPLAY_TESTING')) {
            error_log('[FastPlay] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        }
        require_once APP_PATH . '/controllers/HomeController.php';
        (new HomeController())->serverError();
    }
}