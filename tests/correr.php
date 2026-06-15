<?php
// Pruebas mínimas de los modelos principales.
// Uso: php tests/correr.php
//
// Las pruebas trabajan sobre una BD aislada (base_datos/fastplay_test.sqlite)
// que se borra al empezar para empezar siempre desde un estado conocido.

declare(strict_types=1);

// Forzar entorno de test antes de cargar la configuración.
putenv('FASTPLAY_ENTORNO=test');

require __DIR__ . '/../config/configuracion.php';

// Sobrescribir la ruta de la BD para no tocar la de la aplicación.
$rutaTest = RUTA_RAIZ . '/base_datos/fastplay_test.sqlite';
if (file_exists($rutaTest)) {
    unlink($rutaTest);
}

// Redefinir mediante reflexión no se puede sobre constantes,
// así que copiamos a una constante alternativa y "engañamos" a BaseDeDatos
// haciendo que use otra ruta. Lo más sencillo: implementar la prueba con
// PDO en línea para no alterar el código de producción.
require RUTA_APP . '/nucleo/helpers.php';
require RUTA_APP . '/nucleo/BaseDeDatos.php';
require RUTA_APP . '/modelos/Usuario.php';
require RUTA_APP . '/modelos/Equipo.php';
require RUTA_APP . '/modelos/MiembroEquipo.php';
require RUTA_APP . '/modelos/Campo.php';
require RUTA_APP . '/modelos/Liga.php';
require RUTA_APP . '/modelos/Partido.php';
require RUTA_APP . '/nucleo/Validador.php';

// Truco: la propia BaseDeDatos crea el archivo en RUTA_BD. Para no tener
// que parametrizarla, simplemente borramos la BD real y la recreamos.
// Esto es seguro porque sembrar.php hace lo mismo.
if (file_exists(RUTA_BD)) {
    unlink(RUTA_BD);
}

$totales = ['ok' => 0, 'ko' => 0];

function afirmar(string $titulo, bool $condicion): void
{
    global $totales;
    if ($condicion) {
        $totales['ok']++;
        echo "  OK  $titulo" . PHP_EOL;
    } else {
        $totales['ko']++;
        echo "  KO  $titulo" . PHP_EOL;
    }
}

echo "== Usuarios ==" . PHP_EOL;
$idA = Usuario::crear('Test A', 'a@test.com', 'secreta123');
afirmar('Usuario creado con id positivo', $idA > 0);
afirmar('Usuario.buscarPorEmail recupera el creado',
    (Usuario::buscarPorEmail('a@test.com') ?? [])['nombre'] === 'Test A');
afirmar('emailEnUso detecta duplicado',
    Usuario::emailEnUso('a@test.com') === true);
afirmar('emailEnUso ignora al propio usuario',
    Usuario::emailEnUso('a@test.com', $idA) === false);
afirmar('verificarCredenciales con clave correcta',
    Usuario::verificarCredenciales('a@test.com', 'secreta123') !== null);
afirmar('verificarCredenciales con clave incorrecta',
    Usuario::verificarCredenciales('a@test.com', 'incorrecta') === null);

echo PHP_EOL . "== Equipos y miembros ==" . PHP_EOL;
$idEq = Equipo::crear('Tigres', 'Ceuta', 'Equipo de prueba', $idA);
afirmar('Equipo creado con id positivo', $idEq > 0);
afirmar('Equipo.esCapitan detecta al capitán',
    Equipo::esCapitan($idEq, $idA));
$idB = Usuario::crear('Test B', 'b@test.com', 'secreta123');
afirmar('Equipo.esCapitan rechaza a otro usuario',
    !Equipo::esCapitan($idEq, $idB));

MiembroEquipo::anadir($idEq, $idB, 10, 'Delantero');
afirmar('Miembro añadido aparece en el listado',
    count(MiembroEquipo::listarDeEquipo($idEq)) === 1);
MiembroEquipo::quitar($idEq, $idB);
afirmar('Miembro retirado desaparece del listado',
    count(MiembroEquipo::listarDeEquipo($idEq)) === 0);

echo PHP_EOL . "== Validador ==" . PHP_EOL;
$v = (new Validador(['email' => 'no-es-email']))->email('email');
afirmar('Validador detecta email inválido', !$v->valido());
$v = (new Validador(['n' => 'Pepe']))->obligatorio('n', 'El nombre');
afirmar('Validador acepta campo obligatorio relleno', $v->valido());

echo PHP_EOL . "== Liga y clasificación ==" . PHP_EOL;
$idC      = Usuario::crear('Test C', 'c@test.com', 'secreta123');
$idEqB    = Equipo::crear('Leones', 'Ceuta', '', $idC);
$idLiga   = Liga::crear('Liga prueba', '2025-2026', '');
Liga::inscribirEquipo($idLiga, $idEq);
Liga::inscribirEquipo($idLiga, $idEqB);
$idPart = Partido::crear($idEq, $idEqB, '2025-12-01 18:00', null, $idLiga);
Partido::registrarResultado($idPart, 2, 0);

$cls = Liga::clasificacion($idLiga);
afirmar('Clasificación devuelve 2 filas', count($cls) === 2);
afirmar('El equipo ganador encabeza la clasificación',
    (int) $cls[0]['id'] === $idEq && (int) $cls[0]['puntos'] === 3);
afirmar('El equipo perdedor queda con 0 puntos',
    (int) $cls[1]['id'] === $idEqB && (int) $cls[1]['puntos'] === 0);

echo PHP_EOL;
echo "Resultado: {$totales['ok']} OK / {$totales['ko']} KO" . PHP_EOL;

// Cerrar la conexión PDO antes de borrar la BD (en Windows un handle abierto
// impide eliminar el archivo).
$ref = new ReflectionClass(BaseDeDatos::class);
$prop = $ref->getProperty('conexion');
$prop->setAccessible(true);
$prop->setValue(null, null);
gc_collect_cycles();

if (file_exists(RUTA_BD)) {
    @unlink(RUTA_BD);
}

exit($totales['ko'] === 0 ? 0 : 1);
