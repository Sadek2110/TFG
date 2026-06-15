<?php
// Punto de entrada único de FastPlay (Front Controller).
// Carga la configuración, el núcleo y la tabla de rutas, y delega la
// petición al enrutador.

declare(strict_types=1);

require __DIR__ . '/../config/configuracion.php';
require RUTA_APP . '/nucleo/helpers.php';
require RUTA_APP . '/nucleo/BaseDeDatos.php';
require RUTA_APP . '/nucleo/Sesion.php';
require RUTA_APP . '/nucleo/Csrf.php';
require RUTA_APP . '/nucleo/Controlador.php';
require RUTA_APP . '/nucleo/Enrutador.php';

iniciar_aplicacion();

$enrutador = new Enrutador();
require RUTA_RAIZ . '/config/rutas.php';

try {
    $enrutador->despachar();
} catch (Throwable $error) {
    error_log('[FastPlay] ' . $error->getMessage() . ' en ' . $error->getFile() . ':' . $error->getLine());
    http_response_code(500);
    if ((getenv('FASTPLAY_ENTORNO') ?: 'desarrollo') !== 'produccion') {
        echo '<pre>' . escapar((string) $error) . '</pre>';
    } else {
        echo 'Ha ocurrido un error inesperado. Inténtalo más tarde.';
    }
}
