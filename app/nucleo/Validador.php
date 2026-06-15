<?php
// Validador minimalista. Cada regla devuelve null si pasa o un mensaje
// de error si falla. Pensado para formularios pequeños.

declare(strict_types=1);

class Validador
{
    private array $errores = [];
    private array $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function obligatorio(string $campo, string $etiqueta): self
    {
        if (trim((string) ($this->datos[$campo] ?? '')) === '') {
            $this->errores[$campo] = "$etiqueta es obligatorio.";
        }
        return $this;
    }

    public function longitudMinima(string $campo, int $minimo, string $etiqueta): self
    {
        $valor = (string) ($this->datos[$campo] ?? '');
        if ($valor !== '' && mb_strlen($valor) < $minimo) {
            $this->errores[$campo] = "$etiqueta debe tener al menos $minimo caracteres.";
        }
        return $this;
    }

    public function longitudMaxima(string $campo, int $maximo, string $etiqueta): self
    {
        $valor = (string) ($this->datos[$campo] ?? '');
        if ($valor !== '' && mb_strlen($valor) > $maximo) {
            $this->errores[$campo] = "$etiqueta no puede superar $maximo caracteres.";
        }
        return $this;
    }

    public function email(string $campo, string $etiqueta = 'Correo electrónico'): self
    {
        $valor = (string) ($this->datos[$campo] ?? '');
        if ($valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[$campo] = "$etiqueta no tiene un formato válido.";
        }
        return $this;
    }

    public function igualA(string $campo, string $otro, string $etiqueta): self
    {
        $a = (string) ($this->datos[$campo] ?? '');
        $b = (string) ($this->datos[$otro]  ?? '');
        if ($a !== $b) {
            $this->errores[$campo] = "$etiqueta no coincide.";
        }
        return $this;
    }

    public function entero(string $campo, string $etiqueta, ?int $minimo = null, ?int $maximo = null): self
    {
        $valor = $this->datos[$campo] ?? '';
        if ($valor === '' || $valor === null) {
            return $this;
        }
        if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
            $this->errores[$campo] = "$etiqueta debe ser un número entero.";
            return $this;
        }
        $entero = (int) $valor;
        if ($minimo !== null && $entero < $minimo) {
            $this->errores[$campo] = "$etiqueta no puede ser menor que $minimo.";
        } elseif ($maximo !== null && $entero > $maximo) {
            $this->errores[$campo] = "$etiqueta no puede ser mayor que $maximo.";
        }
        return $this;
    }

    public function enLista(string $campo, array $opciones, string $etiqueta): self
    {
        $valor = (string) ($this->datos[$campo] ?? '');
        if ($valor !== '' && !in_array($valor, $opciones, true)) {
            $this->errores[$campo] = "$etiqueta no es válido.";
        }
        return $this;
    }

    public function fecha(string $campo, string $etiqueta): self
    {
        $valor = (string) ($this->datos[$campo] ?? '');
        if ($valor === '') {
            return $this;
        }
        $formatos = ['Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d H:i:s', 'Y-m-d'];
        foreach ($formatos as $formato) {
            $fecha = DateTimeImmutable::createFromFormat($formato, $valor);
            if ($fecha !== false && $fecha->format($formato) === $valor) {
                return $this;
            }
        }
        $this->errores[$campo] = "$etiqueta no tiene un formato válido.";
        return $this;
    }

    public function anadirError(string $campo, string $mensaje): self
    {
        $this->errores[$campo] = $mensaje;
        return $this;
    }

    public function valido(): bool
    {
        return empty($this->errores);
    }

    public function errores(): array
    {
        return $this->errores;
    }
}
