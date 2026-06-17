<?php
// Acceso a la base de datos SQLite con PDO.
// La clase encapsula la conexión y ofrece helpers cortos para evitar
// repetir prepare/execute/fetch en los modelos.

declare(strict_types=1);

class BaseDeDatos
{
    private static ?PDO $conexion = null;

    // Devuelve la conexión PDO, creándola la primera vez.
    // Si el archivo SQLite no existe, lo crea y aplica el esquema.
    public static function conexion(): PDO
    {
        if (self::$conexion !== null) {
            return self::$conexion;
        }

        $nuevoArchivo = !file_exists(RUTA_BD);
        if ($nuevoArchivo) {
            $directorio = dirname(RUTA_BD);
            if (!is_dir($directorio)) {
                mkdir($directorio, 0775, true);
            }
        }

        $pdo = new PDO('sqlite:' . RUTA_BD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        // Sin esta línea SQLite ignora las claves foráneas.
        $pdo->exec('PRAGMA foreign_keys = ON');

        self::$conexion = $pdo;

        if ($nuevoArchivo) {
            self::aplicarEsquema();
            if (file_exists(RUTA_DEMO)) {
                self::ejecutarArchivo(RUTA_DEMO);
            }
        } else {
            self::actualizarEsquemaLigero();
        }

        return self::$conexion;
    }

    // Ejecuta una consulta preparada y devuelve el PDOStatement.
    public static function ejecutar(string $sql, array $parametros = []): PDOStatement
    {
        $sentencia = self::conexion()->prepare($sql);
        $sentencia->execute($parametros);
        return $sentencia;
    }

    public static function uno(string $sql, array $parametros = []): ?array
    {
        $fila = self::ejecutar($sql, $parametros)->fetch();
        return $fila === false ? null : $fila;
    }

    public static function todos(string $sql, array $parametros = []): array
    {
        return self::ejecutar($sql, $parametros)->fetchAll();
    }

    public static function valor(string $sql, array $parametros = []): mixed
    {
        $valor = self::ejecutar($sql, $parametros)->fetchColumn();
        return $valor === false ? null : $valor;
    }

    public static function ultimoId(): int
    {
        return (int) self::conexion()->lastInsertId();
    }

    private static function aplicarEsquema(): void
    {
        self::ejecutarArchivo(RUTA_ESQUEMA);
    }

    private static function actualizarEsquemaLigero(): void
    {
        if (!self::columnaExiste('miembros_equipo', 'titular')) {
            self::conexion()->exec('ALTER TABLE miembros_equipo ADD COLUMN titular INTEGER NOT NULL DEFAULT 0');
        }

        if (!self::columnaExiste('campos', 'foto')) {
            self::conexion()->exec('ALTER TABLE campos ADD COLUMN foto TEXT');
        }
    }

    private static function columnaExiste(string $tabla, string $columna): bool
    {
        $columnas = self::conexion()->query('PRAGMA table_info(' . $tabla . ')')->fetchAll();
        foreach ($columnas as $info) {
            if (($info['name'] ?? '') === $columna) {
                return true;
            }
        }
        return false;
    }

    private static function ejecutarArchivo(string $ruta): void
    {
        $sql = file_get_contents($ruta);
        if ($sql === false) {
            throw new RuntimeException('No se puede leer ' . $ruta);
        }
        self::conexion()->exec($sql);
    }
}
