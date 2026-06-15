-- Esquema de la base de datos de FastPlay (SQLite).
-- Tablas en español. Las claves foráneas requieren PRAGMA foreign_keys = ON
-- en la conexión PDO.

CREATE TABLE usuarios (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre          TEXT    NOT NULL,
    email           TEXT    NOT NULL UNIQUE,
    contrasena_hash TEXT    NOT NULL,
    rol             TEXT    NOT NULL DEFAULT 'jugador'
                    CHECK (rol IN ('jugador', 'administrador')),
    fecha_creacion  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE equipos (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre         TEXT    NOT NULL UNIQUE,
    ciudad         TEXT,
    descripcion    TEXT,
    id_capitan     INTEGER NOT NULL,
    fecha_creacion TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_capitan) REFERENCES usuarios(id) ON DELETE RESTRICT
);

CREATE TABLE miembros_equipo (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    id_equipo  INTEGER NOT NULL,
    id_usuario INTEGER NOT NULL,
    dorsal     INTEGER,
    posicion   TEXT,
    fecha_alta TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_equipo, id_usuario),
    FOREIGN KEY (id_equipo)  REFERENCES equipos(id)  ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE campos (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre     TEXT    NOT NULL,
    direccion  TEXT,
    ciudad     TEXT,
    superficie TEXT
);

CREATE TABLE ligas (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre         TEXT    NOT NULL,
    temporada      TEXT    NOT NULL,
    descripcion    TEXT,
    fecha_creacion TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (nombre, temporada)
);

CREATE TABLE ligas_equipos (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    id_liga   INTEGER NOT NULL,
    id_equipo INTEGER NOT NULL,
    UNIQUE (id_liga, id_equipo),
    FOREIGN KEY (id_liga)   REFERENCES ligas(id)   ON DELETE CASCADE,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id) ON DELETE CASCADE
);

CREATE TABLE partidos (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    id_liga             INTEGER,
    id_campo            INTEGER,
    id_equipo_local     INTEGER NOT NULL,
    id_equipo_visitante INTEGER NOT NULL,
    fecha_partido       TEXT    NOT NULL,
    goles_local         INTEGER,
    goles_visitante     INTEGER,
    estado              TEXT    NOT NULL DEFAULT 'programado'
                        CHECK (estado IN ('programado', 'finalizado', 'cancelado')),
    fecha_creacion      TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (id_equipo_local <> id_equipo_visitante),
    FOREIGN KEY (id_liga)             REFERENCES ligas(id)   ON DELETE SET NULL,
    FOREIGN KEY (id_campo)            REFERENCES campos(id)  ON DELETE SET NULL,
    FOREIGN KEY (id_equipo_local)     REFERENCES equipos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_equipo_visitante) REFERENCES equipos(id) ON DELETE CASCADE
);

CREATE INDEX idx_partidos_liga  ON partidos(id_liga);
CREATE INDEX idx_partidos_fecha ON partidos(fecha_partido);
CREATE INDEX idx_miembros_equipo ON miembros_equipo(id_equipo);
