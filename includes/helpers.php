<?php

function urlAvatarUsuario($avatar) {
    $avatar = trim((string) $avatar);

    return $avatar !== '' ? $avatar : '/assets/img/profile/user.webp';
}

function urlEncabezadoUsuario($encabezado) {
    $encabezado = trim((string) $encabezado);

    return $encabezado !== '' ? $encabezado : '/assets/img/profile/banner.webp';
}

function urlPortadaJuego($portada, $texto = 'Sin portada') {
    $portada = trim((string) $portada);

    return $portada !== '' ? $portada : '/assets/img/placeholder-cover.webp';
}
