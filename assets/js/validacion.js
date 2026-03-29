var regexNick = /^[a-zA-Z0-9_]{3,20}$/;
var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
var regexPassword = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

function mostrarError(input, mensaje) {
    var campo = input.parentElement;
    var span = campo.querySelector('.msg-error');
    input.classList.add('invalido');
    input.classList.remove('valido');
    span.textContent = mensaje;
}

function mostrarOk(input) {
    var campo = input.parentElement;
    var span = campo.querySelector('.msg-error');
    input.classList.remove('invalido');
    input.classList.add('valido');
    span.textContent = '';
}

function limpiarValidacion(input) {
    var campo = input.parentElement;
    var span = campo.querySelector('.msg-error');
    input.classList.remove('invalido', 'valido');
    span.textContent = '';
}

function validarCampo(input) {
    var valor = input.value.trim();
    var id = input.id;

    if (valor === '') {
        mostrarError(input, 'Este campo es obligatorio');
        return false;
    }

    if (id === 'nick') {
        if (!regexNick.test(valor)) {
            mostrarError(input, 'Entre 3 y 20 caracteres: letras, números y _');
            return false;
        }
    }

    if (id === 'email') {
        if (!regexEmail.test(valor)) {
            mostrarError(input, 'Introduce un email válido');
            return false;
        }
    }

    if (id === 'password') {
        if (!regexPassword.test(valor)) {
            mostrarError(input, 'Mínimo 8 caracteres, una mayúscula y un número');
            return false;
        }
    }

    if (id === 'password2') {
        var password = document.getElementById('password');
        if (password && valor !== password.value) {
            mostrarError(input, 'Las contraseñas no coinciden');
            return false;
        }
    }

    mostrarOk(input);
    return true;
}

var formRegistro = document.getElementById('form-registro');
if (formRegistro) {
    var inputs = formRegistro.querySelectorAll('input');

    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (input.value.trim() !== '') {
                validarCampo(input);
            }
        });

        input.addEventListener('input', function() {
            if (input.classList.contains('invalido')) {
                validarCampo(input);
            }
        });
    });

    formRegistro.addEventListener('submit', function(e) {
        var valido = true;

        inputs.forEach(function(input) {
            if (!validarCampo(input)) {
                valido = false;
            }
        });

        if (!valido) {
            e.preventDefault();
        }
    });
}

var formLogin = document.getElementById('form-login');
if (formLogin) {
    formLogin.addEventListener('submit', function(e) {
        var valido = true;
        var inputs = formLogin.querySelectorAll('input');

        inputs.forEach(function(input) {
            if (input.value.trim() === '') {
                mostrarError(input, 'Este campo es obligatorio');
                valido = false;
            } else {
                mostrarOk(input);
            }
        });

        if (!valido) {
            e.preventDefault();
        }
    });
}
