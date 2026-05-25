const regexNick = /^[a-zA-Z0-9_]{3,20}$/;
const regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
const regexPassword = /^(?=.*[A-Z])(?=.*\d).{8,}$/;
const limiteImagenPerfil = 5 * 1024 * 1024; // 5MB
const formatosImagenPerfil = ['image/jpeg', 'image/png', 'image/webp'];

// Muestra mensaje de error para el input recibido
function mostrarError(input, mensaje) {
    const campo = input.parentElement;
    const span = campo.querySelector('.msg-error');
    input.classList.add('invalido');
    input.classList.remove('valido');

    if (span) {
        span.textContent = mensaje;
    }
}

//Muestra mensaje de éxito para el input recibido
function mostrarOk(input) {
    const campo = input.parentElement;
    const span = campo.querySelector('.msg-error');
    input.classList.remove('invalido');
    input.classList.add('valido');

    if (span) {
        span.textContent = '';
    }
}

// Valida si el archivo de imagen es del formato y tamaño adecuados
function validarImagenPerfil(input) {
    // el usuario no ha seleccionado imagen, eso sirve
    if (!input.files || input.files.length === 0) {
        mostrarOk(input);
        return true;
    }

    const archivo = input.files[0];

    // comprobamos formato
    if (!formatosImagenPerfil.includes(archivo.type)) {
        mostrarError(input, 'La imagen debe ser JPG, PNG o WEBP');
        return false;
    }

    // comprobamos tamaño menor a limiteImagenPerfil
    if (archivo.size > limiteImagenPerfil) {
        mostrarError(input, 'La imagen no puede superar los 5 MB');
        return false;
    }

    // aquí todo Ok
    mostrarOk(input);
    return true;
}

// Función que valida inputs con los regex y muestra error si necesario
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

// comprueba que existe el form, así permite cargar el mismo JS de 
// validación en varias páginas sin error
const formRegistro = document.getElementById('form-registro');
if (formRegistro) {
    const inputs = formRegistro.querySelectorAll('input');

    // con blur se valida cuando el usuario sale del campo (solo si ha escrito algo)
    inputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (input.value.trim() !== '') {
                validarCampo(input);
            }
        });

        // vuelve a validar si el campo ya estaba marcado como inválido
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

        // si algún campo no es válido, no se envía el form
        if (!valido) {
            e.preventDefault();
        }
    });
}

// valida el formulario de cambiar contraseña en el perfil de usuario
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

// valida el formulario de editar perfil
const formEditarPerfil = document.querySelector('.form-editar-perfil');
if (formEditarPerfil) {
    // evita duplicar el mismo código para avatar y encabezado
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

            // si hay imagen seleccionada, se desmarca el checkbox de eliminar imagen
            if (imagen.archivo.files.length > 0) {
                if (imagen.eliminar) {
                    imagen.eliminar.checked = false;
                }
            }
        });

        // si se marca el checkbox de eliminar imagen, se vacía el input de imagen
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

// valida el formulario de login, no lo envía si hay campos vacíos
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
