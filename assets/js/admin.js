// Archivo que controla los modales del panel de administración

const modalAdminConfirmacion = document.getElementById('modalAdminConfirmacion');
const botonImportarAdmin = document.querySelector('.boton-importar-admin');

// muestra indicación de que los juegos se están importando
if (botonImportarAdmin) {
    let importacionAdminActiva = false;

    botonImportarAdmin.addEventListener('click', function(e) {
        if (importacionAdminActiva) {
            e.preventDefault();
            return;
        }

        importacionAdminActiva = true;
        botonImportarAdmin.textContent = 'Importando...';
        botonImportarAdmin.classList.add('cargando');
        botonImportarAdmin.setAttribute('aria-disabled', 'true');
    });
}

if (modalAdminConfirmacion) {
    const tituloModalAdmin = document.getElementById('tituloModalAdmin');
    const textoModalAdmin = document.getElementById('textoModalAdmin');
    const botonCancelarAdmin = modalAdminConfirmacion.querySelector('.boton-cancelar-admin');
    const botonConfirmarAdmin = modalAdminConfirmacion.querySelector('.boton-confirmar-admin');
    const fondoModalAdmin = modalAdminConfirmacion.querySelector('.modal-admin-fondo');
    let formAdminActivo = null;
    let botonAdminActivo = null;

    function abrirModalAdmin(form, boton) {
        formAdminActivo = form;
        botonAdminActivo = boton;
        // rellena los textos del modal desde atributos data-* del formulario
        tituloModalAdmin.textContent = form.dataset.titulo || 'Confirmar acción';
        textoModalAdmin.textContent = form.dataset.texto || '';
        botonConfirmarAdmin.textContent = form.dataset.confirmar || 'Confirmar';
        modalAdminConfirmacion.hidden = false;
        botonConfirmarAdmin.focus();
    }

    function cerrarModalAdmin() {
        modalAdminConfirmacion.hidden = true;
        formAdminActivo = null;

        if (botonAdminActivo) {
            botonAdminActivo.focus();
            botonAdminActivo = null;
        }
    }

    document.querySelectorAll('.abrir-confirmacion-admin').forEach(function(boton) {
        boton.addEventListener('click', function() {
            abrirModalAdmin(boton.closest('form'), boton);
        });
    });

    botonCancelarAdmin.addEventListener('click', cerrarModalAdmin);
    fondoModalAdmin.addEventListener('click', cerrarModalAdmin);

    botonConfirmarAdmin.addEventListener('click', function() {
        if (formAdminActivo) {
            formAdminActivo.submit();
        }
    });
// cierra con escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalAdminConfirmacion.hidden) {
            cerrarModalAdmin();
        }
    });
}
