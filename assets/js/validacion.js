const regexNick = /^[a-zA-Z0-9_]{3,20}$/;
const regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
const regexPassword = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
const limiteImagenPerfil = 5 * 1024 * 1024;
const formatosImagenPerfil = ['image/jpeg', 'image/png', 'image/webp'];

function mostrarError(input, mensaje) {
    const campo = input.parentElement;
    const span = campo.querySelector('.msg-error');
    input.classList.add('invalido');
    input.classList.remove('valido');

    if (span) {
        span.textContent = mensaje;
    }
}

function mostrarOk(input) {
    const campo = input.parentElement;
    const span = campo.querySelector('.msg-error');
    input.classList.remove('invalido');
    input.classList.add('valido');

    if (span) {
        span.textContent = '';
    }
}

function validarImagenPerfil(input) {
    if (!input.files || input.files.length === 0) {
        mostrarOk(input);
        return true;
    }

    const archivo = input.files[0];

    if (!formatosImagenPerfil.includes(archivo.type)) {
        mostrarError(input, 'La imagen debe ser JPG, PNG o WEBP');
        return false;
    }

    if (archivo.size > limiteImagenPerfil) {
        mostrarError(input, 'La imagen no puede superar los 5 MB');
        return false;
    }

    mostrarOk(input);
    return true;
}

function validarCampo(input) {
    const valor = input.value.trim();
    const id = input.id;

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

    if (id === 'password' || id === 'password_nueva') {
        if (!regexPassword.test(valor)) {
            mostrarError(input, 'Mínimo 8 caracteres, una mayúscula y un número');
            return false;
        }
    }

    if (id === 'password2' || id === 'password_nueva2') {
        const password = id === 'password_nueva2' ? document.getElementById('password_nueva') : document.getElementById('password');
        if (password && valor !== password.value) {
            mostrarError(input, 'Las contraseñas no coinciden');
            return false;
        }
    }

    mostrarOk(input);
    return true;
}

const formRegistro = document.getElementById('form-registro');
if (formRegistro) {
    const inputs = formRegistro.querySelectorAll('input');

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
        let valido = true;

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

const formPasswordPerfil = document.getElementById('form-password-perfil');
if (formPasswordPerfil) {
    const inputs = formPasswordPerfil.querySelectorAll('input[type="password"]');

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

    formPasswordPerfil.addEventListener('submit', function(e) {
        let valido = true;

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

const formEditarPerfil = document.querySelector('.form-editar-perfil');
if (formEditarPerfil) {
    const imagenesPerfil = [
        {
            archivo: document.getElementById('avatar'),
            eliminar: document.getElementById('quitar_avatar')
        },
        {
            archivo: document.getElementById('encabezado'),
            eliminar: document.getElementById('quitar_encabezado')
        }
    ];

    imagenesPerfil.forEach(function(imagen) {
        if (!imagen.archivo) {
            return;
        }

        imagen.archivo.addEventListener('change', function() {
            validarImagenPerfil(imagen.archivo);

            if (imagen.archivo.files.length > 0) {
                if (imagen.eliminar) {
                    imagen.eliminar.checked = false;
                }
            }
        });

        if (imagen.eliminar) {
            imagen.eliminar.addEventListener('change', function() {
                if (imagen.eliminar.checked) {
                    imagen.archivo.value = '';
                    mostrarOk(imagen.archivo);
                }
            });
        }
    });

    formEditarPerfil.addEventListener('submit', function(e) {
        let valido = true;

        imagenesPerfil.forEach(function(imagen) {
            if (imagen.archivo && !validarImagenPerfil(imagen.archivo)) {
                valido = false;
            }
        });

        if (!valido) {
            e.preventDefault();
        }
    });
}

const formLogin = document.getElementById('form-login');
if (formLogin) {
    formLogin.addEventListener('submit', function(e) {
        let valido = true;
        const inputs = formLogin.querySelectorAll('input');

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
