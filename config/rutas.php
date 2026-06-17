<?php
// Tabla de rutas de la aplicación. Cada línea asocia un método HTTP y un
// patrón con un controlador y su acción. Mantenerlo plano facilita ver
// de un vistazo qué hace cada URL.

declare(strict_types=1);

/** @var Enrutador $enrutador */

// Páginas públicas
$enrutador->get('/',                            'ControladorInicio',  'mostrar');

// Autenticación
$enrutador->get('/registro',                    'ControladorAuth',    'formularioRegistro');
$enrutador->post('/registro',                   'ControladorAuth',    'registrar');
$enrutador->get('/iniciar-sesion',              'ControladorAuth',    'formularioInicio');
$enrutador->post('/iniciar-sesion',             'ControladorAuth',    'iniciar');
$enrutador->post('/cerrar-sesion',              'ControladorAuth',    'cerrar');

// Perfil del usuario autenticado
$enrutador->get('/perfil',                      'ControladorPerfil',  'mostrar');
$enrutador->post('/perfil',                     'ControladorPerfil',  'actualizar');

// Equipos
$enrutador->get('/equipos',                     'ControladorEquipos', 'listar');
$enrutador->get('/equipos/crear',               'ControladorEquipos', 'formularioCrear');
$enrutador->post('/equipos/crear',              'ControladorEquipos', 'crear');
$enrutador->get('/equipos/{id}',                'ControladorEquipos', 'detalle');
$enrutador->get('/equipos/{id}/editar',         'ControladorEquipos', 'formularioEditar');
$enrutador->post('/equipos/{id}/editar',        'ControladorEquipos', 'editar');
$enrutador->post('/equipos/{id}/eliminar',      'ControladorEquipos', 'eliminar');
$enrutador->post('/equipos/{id}/anadir-miembro','ControladorEquipos', 'anadirMiembro');
$enrutador->post('/equipos/{id}/quitar-miembro','ControladorEquipos', 'quitarMiembro');

// Partidos
$enrutador->get('/partidos',                    'ControladorPartidos','listar');
$enrutador->get('/partidos/crear',              'ControladorPartidos','formularioCrear');
$enrutador->post('/partidos/crear',             'ControladorPartidos','crear');
$enrutador->get('/partidos/{id}',               'ControladorPartidos','detalle');
$enrutador->post('/partidos/{id}/resultado',    'ControladorPartidos','registrarResultado');
$enrutador->post('/partidos/{id}/eliminar',     'ControladorPartidos','eliminar');

// Campos
$enrutador->get('/campos',                      'ControladorCampos',  'listar');
$enrutador->get('/campos/crear',                'ControladorCampos',  'formularioCrear');
$enrutador->post('/campos/crear',               'ControladorCampos',  'crear');
$enrutador->post('/campos/{id}/eliminar',       'ControladorCampos',  'eliminar');

// Ligas
$enrutador->get('/ligas',                       'ControladorLigas',   'listar');
$enrutador->get('/ligas/crear',                 'ControladorLigas',   'formularioCrear');
$enrutador->post('/ligas/crear',                'ControladorLigas',   'crear');
$enrutador->get('/ligas/{id}',                  'ControladorLigas',   'detalle');
$enrutador->post('/ligas/{id}/inscribir',       'ControladorLigas',   'inscribirEquipo');

// Panel administrador
$enrutador->get('/admin',                       'ControladorAdmin',   'panel');

// API JSON (consumida por el cliente con fetch)
$enrutador->get('/api/contexto',                'ControladorApi',     'contexto');
