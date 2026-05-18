<?php

require_once __DIR__ . '/paginacion_helpers.php';

function estadosBiblioteca() {
    return [
        'jugando' => 'Jugando',
        'completado' => 'Completado',
        'pendiente' => 'Pendiente',
        'abandonado' => 'Abandonado'
    ];
}

function estadosBibliotecaFicha() {
    return [
        'completado' => ['icono' => 'fa-check', 'texto' => 'Complet.'],
        'jugando' => ['icono' => 'fa-gamepad', 'texto' => 'Jugando'],
        'pendiente' => ['icono' => 'fa-calendar-days', 'texto' => 'Pendiente'],
        'abandonado' => ['icono' => 'fa-ban', 'texto' => 'Aband.']
    ];
}

function estadoBibliotecaValido($estado) {
    return isset(estadosBiblioteca()[$estado]);
}

function textoEstadoBiblioteca($estado) {
    return estadosBiblioteca()[$estado] ?? 'Sin estado';
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

function urlBibliotecaEstado($baseUrl, $estado = '') {
    $params = [];

    if ($estado !== '') {
        $params['estado'] = $estado;
    }

    $params['p'] = 1;

    $query = http_build_query($params);

    if ($query === '') {
        return $baseUrl;
    }

    return $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $query;
}

function urlBibliotecaPagina($baseUrl, $estado = '', $pagina = 1) {
    $params = ['p' => max(1, (int) $pagina)];

    if ($estado !== '') {
        $params['estado'] = $estado;
    }

    $query = http_build_query($params);

    return $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $query;
}
