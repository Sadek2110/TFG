<?php
// Funciones de ayuda usadas desde vistas y controladores.

declare(strict_types=1);

// Escapa una cadena para imprimirla en HTML sin riesgo de XSS.
function escapar(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Versión corta para usar en las vistas con la sintaxis breve <?= e(...)
function e(?string $valor): string
{
    return escapar($valor);
}

// Devuelve el valor antiguo de un campo (para repintar formularios con errores).
function viejo(string $campo, string $defecto = ''): string
{
    return escapar($_SESSION['viejo'][$campo] ?? $defecto);
}

// Guarda los valores válidos del formulario para repintarlos tras un error.
function guardar_viejos(array $datos, array $omitir = ['contrasena', 'contrasena2']): void
{
    foreach ($omitir as $campo) {
        unset($datos[$campo]);
    }
    $_SESSION['viejo'] = $datos;
}

function limpiar_viejos(): void
{
    unset($_SESSION['viejo']);
}

// Formateo amigable de fecha/hora para la interfaz.
function formatear_fecha(?string $valor, string $formato = 'd/m/Y H:i'): string
{
    if ($valor === null || $valor === '') {
        return '';
    }
    try {
        return (new DateTimeImmutable($valor))->format($formato);
    } catch (Exception) {
        return escapar($valor);
    }
}

// Helper para imprimir el atributo activo en la navegación.
function ruta_activa(string $prefijo): string
{
    $actual = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $base   = url_base();
    if ($base !== '' && str_starts_with((string) $actual, $base)) {
        $actual = substr((string) $actual, strlen($base));
    }
    if ($prefijo === '/') {
        return $actual === '/' || $actual === '' ? 'activo' : '';
    }
    return str_starts_with((string) $actual, $prefijo) ? 'activo' : '';
}
