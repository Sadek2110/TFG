<?php
// Script para regenerar la base de datos con datos de demostración.
// Uso (desde la raíz del proyecto):
//     php base_datos/sembrar.php
//
// Borra la BD existente, deja que BaseDeDatos vuelva a aplicar el esquema
// y crea usuarios, equipos, campos, ligas y partidos de ejemplo.

declare(strict_types=1);

require __DIR__ . '/../config/configuracion.php';
require RUTA_APP . '/nucleo/helpers.php';
require RUTA_APP . '/nucleo/BaseDeDatos.php';
require RUTA_APP . '/modelos/Usuario.php';
require RUTA_APP . '/modelos/Equipo.php';
require RUTA_APP . '/modelos/MiembroEquipo.php';
require RUTA_APP . '/modelos/Campo.php';
require RUTA_APP . '/modelos/Liga.php';
require RUTA_APP . '/modelos/Partido.php';

// Reiniciar la BD para tener un estado conocido.
if (file_exists(RUTA_BD)) {
    unlink(RUTA_BD);
}

$pdo = BaseDeDatos::conexion();
echo "Base de datos inicializada en " . RUTA_BD . PHP_EOL;

// Usuarios.
$idAdmin = Usuario::crear('Administrador', 'admin@fastplay.test',  'admin1234', 'administrador');
$idAna   = Usuario::crear('Ana Pérez',     'ana@fastplay.test',    'jugador1');
$idBob   = Usuario::crear('Bob García',    'bob@fastplay.test',    'jugador1');
$idEva   = Usuario::crear('Eva Romero',    'eva@fastplay.test',    'jugador1');
$idLuis  = Usuario::crear('Luis Mora',     'luis@fastplay.test',   'jugador1');
$idMarta = Usuario::crear('Marta Ruiz',    'marta@fastplay.test',  'jugador1');

// Campos.
$idCampo1 = Campo::crear('Estadio Murube', 'Av. de los Reyes Católicos', 'Ceuta', 'Hierba natural', '/imagenes/campos/alfonso-murube.jpg');
$idCampo2 = Campo::crear('Polideportivo del Sur', 'Calle del Mar', 'Ceuta', 'Hierba artificial', '/imagenes/campos/emilio-cozar.jpg');
$idCampo3 = Campo::crear('Campo Municipal Norte', 'Calle Real', 'Ceuta', 'Tierra', '/imagenes/campos/jose-benoliel.jpg');
$idCampo4 = Campo::crear('Campo Aiman Mohamed', 'Avenida África', 'Ceuta', 'Hierba artificial', '/imagenes/campos/aiman-mohamed.webp');
$idCampo5 = Campo::crear('Campo José Pirri', 'Barriada San José', 'Ceuta', 'Cemento', '/imagenes/campos/jose-pirri.jpeg');
$idCampo6 = Campo::crear('Campo Tuhami Al-Lal', 'Calle Independencia', 'Ceuta', 'Hierba natural', '/imagenes/campos/tuhami-al-lal.webp');

// Equipos (capitanes: Ana, Bob, Eva).
$idTigres   = Equipo::crear('Tigres FC',     'Ceuta', 'Equipo del barrio del puerto.',    $idAna);
$idLeones   = Equipo::crear('Leones del Sur', 'Ceuta', 'Equipo joven con muchas ganas.',  $idBob);
$idAguilas  = Equipo::crear('Águilas Doradas','Ceuta', 'Veteranos con historia en la zona.', $idEva);

// Cada capitán entra como miembro de su equipo.
MiembroEquipo::anadir($idTigres,  $idAna, 1, 'Portero', true);
MiembroEquipo::anadir($idLeones,  $idBob, 1, 'Portero', true);
MiembroEquipo::anadir($idAguilas, $idEva, 1, 'Defensa', true);

// Algunos miembros adicionales.
MiembroEquipo::anadir($idTigres,  $idLuis, 9, 'Delantero', true);
MiembroEquipo::anadir($idLeones,  $idMarta, 7, 'Medio centro', true);

// Liga.
$idLiga = Liga::crear('Liga Ceutí Amateur', '2025-2026', 'Primera edición de la liga local.');
Liga::inscribirEquipo($idLiga, $idTigres);
Liga::inscribirEquipo($idLiga, $idLeones);
Liga::inscribirEquipo($idLiga, $idAguilas);

// Partidos (uno finalizado, uno programado).
$idPartido1 = Partido::crear($idTigres, $idLeones,  '2025-10-10 18:00', $idCampo1, $idLiga);
$idPartido2 = Partido::crear($idLeones, $idAguilas, '2025-10-17 19:30', $idCampo2, $idLiga);
$idPartido3 = Partido::crear($idAguilas, $idTigres, '2026-09-25 20:00', $idCampo1, $idLiga);

Partido::registrarResultado($idPartido1, 3, 1);
Partido::registrarResultado($idPartido2, 2, 2);
// El tercero queda programado.

echo "Datos de demostración cargados:" . PHP_EOL;
echo "  - 6 usuarios (1 admin, 5 jugadores)" . PHP_EOL;
echo "  - 3 equipos, 6 campos, 1 liga" . PHP_EOL;
echo "  - 3 partidos (2 finalizados + 1 programado)" . PHP_EOL;
echo PHP_EOL;
echo "Usuarios de prueba (contraseñas en texto plano para la demo):" . PHP_EOL;
echo "  admin@fastplay.test / admin1234     (rol administrador)" . PHP_EOL;
echo "  ana@fastplay.test   / jugador1      (capitana de Tigres FC)" . PHP_EOL;
echo "  bob@fastplay.test   / jugador1      (capitán de Leones del Sur)" . PHP_EOL;
echo "  eva@fastplay.test   / jugador1      (capitana de Águilas Doradas)" . PHP_EOL;
echo "  luis@fastplay.test  / jugador1      (jugador raso)" . PHP_EOL;
echo "  marta@fastplay.test / jugador1      (jugadora rasa)" . PHP_EOL;
