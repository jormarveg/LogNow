<?php

// 5MB
const LIMITE_IMAGEN_PERFIL = 5 * 1024 * 1024;

function subirImagenPerfil($archivo, $rutaActual, $carpeta, $prefijo = 'imagen', $limite = LIMITE_IMAGEN_PERFIL) {
    if (!isset($archivo) || !is_array($archivo) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [
            'ok' => true,
            'ruta' => $rutaActual
        ];
    }

    $errorSubida = $archivo['error'] ?? UPLOAD_ERR_OK;

    if ($errorSubida !== UPLOAD_ERR_OK) {
        $mensaje = 'No se ha podido subir la imagen';

        if (in_array($errorSubida, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            $mensaje = 'La imagen no puede superar los 5 MB';
        }

        return [
            'ok' => false,
            'error' => $mensaje
        ];
    }

    if (($archivo['size'] ?? 0) > $limite) {
        return [
            'ok' => false,
            'error' => 'La imagen no puede superar los 5 MB'
        ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    $extensiones = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!$mime || !isset($extensiones[$mime])) {
        return [
            'ok' => false,
            'error' => 'La imagen debe ser JPG, PNG o WEBP'
        ];
    }

    $carpeta = trim($carpeta, '/');
    $directorioBase = __DIR__ . '/../uploads';
    $directorio = $directorioBase . '/' . $carpeta;

    if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
        return [
            'ok' => false,
            'error' => 'No se ha podido preparar la carpeta de imágenes'
        ];
    }

    if (!is_writable($directorio)) {
        return [
            'ok' => false,
            'error' => 'La carpeta de imágenes no tiene permisos de escritura'
        ];
    }

    $nombre = $prefijo . '_' . uniqid() . '.' . $extensiones[$mime];
    $rutaAbsoluta = $directorio . '/' . $nombre;
    $rutaRelativa = '/uploads/' . $carpeta . '/' . $nombre;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
        return [
            'ok' => false,
            'error' => 'No se ha podido guardar la imagen'
        ];
    }

    $rutaActual = trim((string) $rutaActual);

    if ($rutaActual !== '' && str_starts_with($rutaActual, '/uploads/' . $carpeta . '/')) {
        $anterior = __DIR__ . '/..' . $rutaActual;

        if (is_file($anterior)) {
            unlink($anterior);
        }
    }

    return [
        'ok' => true,
        'ruta' => $rutaRelativa
    ];
}

function borrarImagenPerfil($rutaActual, $carpeta) {
    $rutaActual = trim((string) $rutaActual);
    $carpeta = trim((string) $carpeta, '/');

    if ($rutaActual === '' || !str_starts_with($rutaActual, '/uploads/' . $carpeta . '/')) {
        return;
    }

    $rutaAbsoluta = __DIR__ . '/..' . $rutaActual;

    if (is_file($rutaAbsoluta)) {
        unlink($rutaAbsoluta);
    }
}

function subirAvatar($archivo, $avatarActual = '') {
    return subirImagenPerfil($archivo, $avatarActual, 'avatars', 'user-pic', LIMITE_IMAGEN_PERFIL);
}

function subirEncabezado($archivo, $encabezadoActual = '') {
    return subirImagenPerfil($archivo, $encabezadoActual, 'covers', 'user-header', LIMITE_IMAGEN_PERFIL);
}
