// Validación ligera en cliente. Complementa la validación PHP, que sigue
// siendo la autoritativa: cualquier formulario debe poder ser validado y
// rechazado por el servidor aunque el JavaScript no se ejecute.
//
// Cómo funciona:
//   - Cada formulario con [data-validar] se valida al hacer "submit".
//   - Cada campo con [required], [minlength], [maxlength], [pattern],
//     [type=email] o [type=number] se valida también al perder el foco.
//   - Si hay errores, se cancela el envío y se muestra un mensaje
//     junto al campo correspondiente.

(function () {
    'use strict';

    const REGLAS = {
        email: {
            patron: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            mensaje: 'Introduce un correo válido (ejemplo: nombre@dominio.com).',
        },
    };

    function mensajeError(campo, motivo) {
        const etiqueta =
            (document.querySelector('label[for="' + campo.id + '"]') || {}).textContent ||
            'Este campo';
        switch (motivo) {
            case 'requerido':  return etiqueta.trim() + ' es obligatorio.';
            case 'minimo':     return etiqueta.trim() + ' debe tener al menos ' + campo.minLength + ' caracteres.';
            case 'maximo':     return etiqueta.trim() + ' no puede superar ' + campo.maxLength + ' caracteres.';
            case 'patron':     return etiqueta.trim() + ' no tiene un formato válido.';
            case 'email':      return REGLAS.email.mensaje;
            case 'numero':     return etiqueta.trim() + ' debe ser un número entre ' + campo.min + ' y ' + campo.max + '.';
            default:           return etiqueta.trim() + ' no es válido.';
        }
    }

    function validarCampo(campo) {
        const valor = (campo.value || '').trim();

        if (campo.required && valor === '') {
            return 'requerido';
        }
        if (valor === '') {
            return null; // No obligatorio y vacío -> OK
        }
        if (campo.minLength > 0 && valor.length < campo.minLength) {
            return 'minimo';
        }
        if (campo.maxLength > 0 && valor.length > campo.maxLength) {
            return 'maximo';
        }
        if (campo.type === 'email' && !REGLAS.email.patron.test(valor)) {
            return 'email';
        }
        if (campo.type === 'number') {
            const numero = Number(valor);
            if (Number.isNaN(numero)) return 'numero';
            if (campo.min !== '' && numero < Number(campo.min)) return 'numero';
            if (campo.max !== '' && numero > Number(campo.max)) return 'numero';
        }
        if (campo.pattern && !new RegExp('^(?:' + campo.pattern + ')$').test(valor)) {
            return 'patron';
        }
        return null;
    }

    function mostrarError(campo, mensaje) {
        campo.setAttribute('aria-invalid', 'true');
        const padre = campo.closest('.campo') || campo.parentElement;
        let aviso = padre.querySelector('.campo__error--js');
        if (!aviso) {
            aviso = document.createElement('span');
            aviso.className = 'campo__error campo__error--js';
            padre.appendChild(aviso);
        }
        aviso.textContent = mensaje;
    }

    function limpiarError(campo) {
        campo.setAttribute('aria-invalid', 'false');
        const padre = campo.closest('.campo') || campo.parentElement;
        const aviso = padre.querySelector('.campo__error--js');
        if (aviso) aviso.remove();
    }

    function validarFormulario(formulario) {
        let primerError = null;
        const campos = formulario.querySelectorAll('input, textarea, select');
        campos.forEach(function (campo) {
            if (campo.type === 'hidden' || campo.type === 'submit' || campo.type === 'button') return;
            const motivo = validarCampo(campo);
            if (motivo) {
                mostrarError(campo, mensajeError(campo, motivo));
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

    document.addEventListener('submit', function (evento) {
        const formulario = evento.target;
        if (!(formulario instanceof HTMLFormElement)) return;
        if (!formulario.hasAttribute('data-validar')) return;
        if (!validarFormulario(formulario)) {
            evento.preventDefault();
        }
    });

    document.addEventListener('blur', function (evento) {
        const campo = evento.target;
        if (!campo.matches || !campo.matches('input, textarea, select')) return;
        const formulario = campo.closest('form[data-validar]');
        if (!formulario) return;
        const motivo = validarCampo(campo);
        if (motivo) {
            mostrarError(campo, mensajeError(campo, motivo));
        } else {
            limpiarError(campo);
        }
    }, true);
})();
