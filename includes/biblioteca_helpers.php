<?php

function textoEstadoBiblioteca($estado) {
    $estados = [
        'jugando' => 'Jugando',
        'completado' => 'Completado',
        'pendiente' => 'Pendiente',
        'abandonado' => 'Abandonado'
    ];

    return $estados[$estado] ?? 'Sin estado';
}

function tiempoBiblioteca($horas, $minutos) {
    $horas = (int) $horas;
    $minutos = (int) $minutos;

    if ($horas <= 0 && $minutos <= 0) {
        return 'Sin tiempo registrado';
    }

    if ($horas > 0 && $minutos > 0) {
        return $horas . ' h ' . $minutos . ' min';
    }

    if ($horas > 0) {
        return $horas . ' h';
    }

    return $minutos . ' min';
}

function fechaBibliotecaBonita($fecha) {
    if (!$fecha) {
        return 'Sin fecha';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    return date('j', $marca) . ' ' . $meses[(int) date('n', $marca) - 1] . ' ' . date('Y', $marca);
}

function puntuacionBibliotecaVisible($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    $puntuacion = (float) $puntuacion;

    if (abs($puntuacion - round($puntuacion)) < 0.05) {
        return number_format($puntuacion, 0, ',', '.');
    }

    return number_format($puntuacion, 1, ',', '.');
}

function urlBibliotecaEstado($baseUrl, $estado = '') {
    $params = [];

    if ($estado !== '') {
        $params['estado'] = $estado;
    }

    $query = http_build_query($params);

    if ($query === '') {
        return $baseUrl;
    }

    return $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $query;
}
