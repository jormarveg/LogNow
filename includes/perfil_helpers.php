<?php

require_once __DIR__ . '/puntuacion_helpers.php';
require_once __DIR__ . '/paginacion_helpers.php';

function urlUsuarioPublico($nick) {
    return '/usuario.php?nick=' . rawurlencode((string) $nick);
}

function urlPerfilTab($baseUrl, $tab, $extra = []) {
    $params = $tab === 'perfil' ? [] : ['tab' => $tab];
    $params = array_merge($params, $extra);

    foreach ($params as $clave => $valor) {
        if ($valor === '' || $valor === null) {
            unset($params[$clave]);
        }
    }

    $query = http_build_query($params);

    if ($query === '') {
        return $baseUrl;
    }

    return $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . $query;
}

function fechaPerfilBonita($fecha, $abreviada = false) {
    if (!$fecha) {
        return '';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    $meses = $abreviada
        ? ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic']
        : ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    return date('j', $marca) . ' ' . $meses[(int) date('n', $marca) - 1] . ' ' . date('Y', $marca);
}

function partesTextoPerfilResena($texto, $limite = 110) {
    $texto = trim((string) $texto);

    if (mb_strlen($texto, 'UTF-8') <= $limite) {
        return [$texto, ''];
    }

    $corto = mb_substr($texto, 0, $limite, 'UTF-8');
    $resto = mb_substr($texto, $limite, null, 'UTF-8');
    $ultimoEspacio = mb_strrpos($corto, ' ', 0, 'UTF-8');

    if ($ultimoEspacio !== false && $ultimoEspacio > 60) {
        $resto = mb_substr($corto, $ultimoEspacio + 1, null, 'UTF-8') . $resto;
        $corto = mb_substr($corto, 0, $ultimoEspacio, 'UTF-8');
    }

    return [rtrim($corto) . '...', ltrim($resto)];
}

function alturaBarraPerfil($valor, $maximo) {
    if ($maximo <= 0) {
        return 12;
    }

    return max(12, (int) round(($valor / $maximo) * 100));
}

function datosPerfilUsuario(PDO $db, $idUsuario, $estadoFiltro, $paginaBibliotecaActual, $porPaginaBiblioteca = 12, $paginaResenasActual = 1, $porPaginaResenas = 6) {
    $idUsuario = (int) $idUsuario;
    $paginaBibliotecaActual = max(1, (int) $paginaBibliotecaActual);
    $paginaResenasActual = max(1, (int) $paginaResenasActual);
    $resumenBiblioteca = cacheResumenBibliotecaUsuario($db, $idUsuario);
    $totalJuegosBiblioteca = cacheContarBibliotecaUsuario($db, $idUsuario, $estadoFiltro);
    $totalPaginasBiblioteca = max(1, (int) ceil($totalJuegosBiblioteca / $porPaginaBiblioteca));
    $totalResenasUsuario = cacheContarResenasUsuario($db, $idUsuario);
    $totalPaginasResenas = max(1, (int) ceil($totalResenasUsuario / $porPaginaResenas));

    if ($paginaBibliotecaActual > $totalPaginasBiblioteca) {
        $paginaBibliotecaActual = $totalPaginasBiblioteca;
    }

    if ($paginaResenasActual > $totalPaginasResenas) {
        $paginaResenasActual = $totalPaginasResenas;
    }

    $offsetBiblioteca = ($paginaBibliotecaActual - 1) * $porPaginaBiblioteca;
    $offsetResenas = ($paginaResenasActual - 1) * $porPaginaResenas;
    $histogramaUsuario = cacheHistogramaUsuario($db, $idUsuario);
    $totalPuntuacionesUsuario = array_sum($histogramaUsuario);

    return [
        'resumenBiblioteca' => $resumenBiblioteca,
        'totalJuegosBiblioteca' => $totalJuegosBiblioteca,
        'totalPaginasBiblioteca' => $totalPaginasBiblioteca,
        'paginaBibliotecaActual' => $paginaBibliotecaActual,
        'juegosBiblioteca' => cacheListarBibliotecaUsuario($db, $idUsuario, $estadoFiltro, $porPaginaBiblioteca, $offsetBiblioteca),
        'resenasUsuarioPerfil' => cacheListarResenasUsuario($db, $idUsuario, 5),
        'resenasUsuarioTab' => cacheListarResenasUsuario($db, $idUsuario, $porPaginaResenas, $offsetResenas),
        'totalResenasUsuario' => $totalResenasUsuario,
        'totalPaginasResenas' => $totalPaginasResenas,
        'paginaResenasActual' => $paginaResenasActual,
        'favoritosUsuario' => cacheFavoritosUsuario($db, $idUsuario, 6),
        'jugadosEsteAno' => cacheJuegosUsuarioEsteAno($db, $idUsuario),
        'histogramaUsuario' => $histogramaUsuario,
        'totalPuntuacionesUsuario' => $totalPuntuacionesUsuario,
        'maximoHistograma' => max($histogramaUsuario ?: [0]),
        'contadorFiltros' => [
            '' => $resumenBiblioteca['total'],
            'jugando' => $resumenBiblioteca['jugando'],
            'completado' => $resumenBiblioteca['completados'],
            'pendiente' => $resumenBiblioteca['pendientes'],
            'abandonado' => $resumenBiblioteca['abandonados']
        ],
        'filtros' => [
            '' => 'Todos',
            'jugando' => 'Jugando',
            'completado' => 'Completados',
            'pendiente' => 'Pendientes',
            'abandonado' => 'Abandonados'
        ]
    ];
}
