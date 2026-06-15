<?php
// Configuración central de FastPlay.
// Todo lo que no varía por usuario o sesión vive aquí: rutas, BD, sesión, errores.

declare(strict_types=1);

const RUTA_RAIZ      = __DIR__ . '/..';
const RUTA_APP       = RUTA_RAIZ . '/app';
const RUTA_VISTAS    = RUTA_APP  . '/vistas';
const RUTA_BD        = RUTA_RAIZ . '/base_datos/fastplay.sqlite';
const RUTA_ESQUEMA   = RUTA_RAIZ . '/base_datos/esquema.sql';
const RUTA_DEMO      = RUTA_RAIZ . '/base_datos/datos_demo.sql';
const RUTA_SESIONES  = RUTA_RAIZ . '/almacenamiento/sesiones';

// URL base de la aplicación. Se calcula a partir del script para que funcione
// en subdirectorios (XAMPP) y con el servidor embebido (php -S).
function url_base(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // El servidor embebido con router.php reescribe SCRIPT_NAME con la URL
    // pedida, así que solo podemos confiar en él si apunta a un .php real.
    if (!str_ends_with($script, '.php')) {
        return $base = '';
    }
    $directorio = str_replace('\\', '/', dirname($script));
    if ($directorio === '/' || $directorio === '.') {
        $directorio = '';
    }
    return $base = rtrim($directorio, '/');
}

// Construye una URL absoluta del sitio a partir de una ruta interna.
function url(string $ruta = '/'): string
{
    if ($ruta === '' || $ruta[0] !== '/') {
        $ruta = '/' . $ruta;
    }
    return url_base() . $ruta;
}

// Inicializa el entorno: errores, zona horaria, sesión segura.
function iniciar_aplicacion(): void
{
    mb_internal_encoding('UTF-8');
    date_default_timezone_set('Europe/Madrid');

    // En desarrollo se ven todos los errores; en producción se ocultan
    // y se registran en el log de PHP.
    $entorno = getenv('FASTPLAY_ENTORNO') ?: 'desarrollo';
    if ($entorno === 'produccion') {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }

    iniciar_sesion();
}

// Configura y arranca la sesión con cookies endurecidas.
function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (!is_dir(RUTA_SESIONES)) {
        mkdir(RUTA_SESIONES, 0775, true);
    }
    session_save_path(RUTA_SESIONES);

    $segura = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('FASTPLAYSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => url_base() === '' ? '/' : url_base(),
        'domain'   => '',
        'secure'   => $segura,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();
}
