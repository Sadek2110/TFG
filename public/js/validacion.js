// Validación en cliente con expresiones regulares.
//
// Complementa la validación de PHP, que sigue siendo la autoritativa: el
// servidor debe poder rechazar cualquier formulario aunque el JavaScript no
// se ejecute. Aquí solo damos feedback inmediato y accesible.
//
// Qué se demuestra (DWEC):
//   - Catálogo de expresiones regulares (email, contraseña, nombre, ciudad,
//     dorsal, teléfono…) reutilizable por nombre con [data-regla].
//   - Eventos: submit (con preventDefault), blur e input.
//   - Accesibilidad: aria-invalid + aria-describedby apuntando al mensaje.
//   - try/catch: compilar el patrón de un campo puede lanzar si la expresión
//     es inválida; se captura para no romper el resto del formulario.

(function () {
    'use strict';

    // Catálogo de reglas con regex con nombre. Un campo las pide con
    // data-regla="email", data-regla="dorsal", etc.
    const REGLAS = {
        email: {
            patron: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            mensaje: 'Introduce un correo válido (ejemplo: nombre@dominio.com).',
        },
        contrasena: {
            patron: /^(?=.*[A-Za-z])(?=.*\d).{8,}$/,
            mensaje: 'La contraseña debe tener al menos 8 caracteres e incluir letras y números.',
        },
        nombre: {
            patron: /^[\p{L}][\p{L}\s'’.-]{1,59}$/u,
            mensaje: 'Usa solo letras, espacios, guiones o apóstrofos (2-60 caracteres).',
        },
        ciudad: {
            patron: /^[\p{L}][\p{L}\s'’.-]{1,79}$/u,
            mensaje: 'Introduce una ciudad válida.',
        },
        dorsal: {
            patron: /^([1-9]|[1-9][0-9])$/,
            mensaje: 'El dorsal debe ser un número entre 1 y 99.',
        },
        telefono: {
            patron: /^[+]?[\d\s]{9,15}$/,
            mensaje: 'Introduce un teléfono válido (9 a 15 dígitos).',
        },
    };

    function etiquetaDe(campo) {
        const etiqueta = document.querySelector('label[for="' + campo.id + '"]');
        return (etiqueta ? etiqueta.textContent : '').trim() || 'Este campo';
    }

    // Devuelve un mensaje de error, o null si el campo es válido.
    function validarCampo(campo) {
        const valor = (campo.value || '').trim();
        const nombreEtiqueta = etiquetaDe(campo);

        if (campo.required && valor === '') {
            return nombreEtiqueta + ' es obligatorio.';
        }
        if (valor === '') {
            return null; // Opcional y vacío -> válido.
        }
        if (campo.minLength > 0 && valor.length < campo.minLength) {
            return nombreEtiqueta + ' debe tener al menos ' + campo.minLength + ' caracteres.';
        }
        if (campo.maxLength > 0 && valor.length > campo.maxLength) {
            return nombreEtiqueta + ' no puede superar ' + campo.maxLength + ' caracteres.';
        }

        // Regla con nombre del catálogo (data-regla="...").
        const nombreRegla = campo.dataset.regla;
        if (nombreRegla && REGLAS[nombreRegla] && !REGLAS[nombreRegla].patron.test(valor)) {
            return REGLAS[nombreRegla].mensaje;
        }

        // Reglas implícitas por tipo de campo.
        if (campo.type === 'email' && !REGLAS.email.patron.test(valor)) {
            return REGLAS.email.mensaje;
        }
        if (campo.type === 'number') {
            const numero = Number(valor);
            const min = campo.min !== '' ? Number(campo.min) : -Infinity;
            const max = campo.max !== '' ? Number(campo.max) : Infinity;
            if (Number.isNaN(numero) || numero < min || numero > max) {
                return nombreEtiqueta + ' debe ser un número entre ' + campo.min + ' y ' + campo.max + '.';
            }
        }

        // Patrón del atributo HTML pattern. Compilar puede lanzar si la
        // expresión es inválida: lo capturamos para no tumbar el formulario.
        if (campo.pattern) {
            try {
                const regex = new RegExp('^(?:' + campo.pattern + ')$');
                if (!regex.test(valor)) {
                    return nombreEtiqueta + ' no tiene un formato válido.';
                }
            } catch (error) {
                console.warn('Patrón inválido en el campo', campo.name, error);
            }
        }

        return null;
    }

    function idError(campo) {
        return 'error-' + (campo.id || campo.name || Math.random().toString(36).slice(2));
    }

    function mostrarError(campo, mensaje) {
        campo.setAttribute('aria-invalid', 'true');
        const padre = campo.closest('.campo') || campo.parentElement;
        let aviso = padre.querySelector('.campo__error--js');
        if (!aviso) {
            aviso = document.createElement('span');
            aviso.className = 'campo__error campo__error--js';
            aviso.id = idError(campo);
            padre.appendChild(aviso);
        }
        // Enlaza el campo con su mensaje para los lectores de pantalla.
        campo.setAttribute('aria-describedby', aviso.id);
        aviso.textContent = mensaje;
    }

    function limpiarError(campo) {
        campo.setAttribute('aria-invalid', 'false');
        campo.removeAttribute('aria-describedby');
        const padre = campo.closest('.campo') || campo.parentElement;
        const aviso = padre.querySelector('.campo__error--js');
        if (aviso) aviso.remove();
    }

    function esValidable(campo) {
        return campo.matches && campo.matches('input, textarea, select') &&
            !['hidden', 'submit', 'button', 'reset'].includes(campo.type);
    }

    function validarFormulario(formulario) {
        let primerError = null;
        formulario.querySelectorAll('input, textarea, select').forEach(function (campo) {
            if (!esValidable(campo)) return;
            const mensaje = validarCampo(campo);
            if (mensaje) {
                mostrarError(campo, mensaje);
                if (!primerError) primerError = campo;
            } else {
                limpiarError(campo);
            }
        });
        if (primerError) {
            primerError.focus();
            return false;
        }
        return true;
    }

    // submit: cancela el envío si hay errores.
    document.addEventListener('submit', function (evento) {
        const formulario = evento.target;
        if (!(formulario instanceof HTMLFormElement)) return;
        if (!formulario.hasAttribute('data-validar')) return;
        if (!validarFormulario(formulario)) {
            evento.preventDefault();
        }
    });

    // blur: valida un campo al perder el foco (captura porque blur no burbujea).
    document.addEventListener('blur', function (evento) {
        const campo = evento.target;
        if (!esValidable(campo)) return;
        if (!campo.closest('form[data-validar]')) return;
        const mensaje = validarCampo(campo);
        if (mensaje) mostrarError(campo, mensaje);
        else limpiarError(campo);
    }, true);

    // input: en cuanto un campo en error vuelve a ser válido, se limpia.
    document.addEventListener('input', function (evento) {
        const campo = evento.target;
        if (!esValidable(campo)) return;
        if (!campo.closest('form[data-validar]')) return;
        if (campo.getAttribute('aria-invalid') === 'true' && !validarCampo(campo)) {
            limpiarError(campo);
        }
    });
})();
