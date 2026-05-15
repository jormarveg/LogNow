<?php

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

function puntuacionPerfilVisible($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    $puntuacion = (float) $puntuacion;

    if (abs($puntuacion - round($puntuacion)) < 0.05) {
        return number_format($puntuacion, 0, ',', '.');
    }

    return number_format($puntuacion, 1, ',', '.');
}

function estrellasPerfil($puntuacion) {
    if ($puntuacion === null) {
        $puntuacion = 0;
    }

    $puntuacion = max(0, min(5, (float) $puntuacion));
    $estrellasCompletas = (int) floor($puntuacion);
    $mediaEstrella = ($puntuacion - $estrellasCompletas) >= 0.5;
    $html = '';

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $estrellasCompletas) {
            $html .= '<i class="fa-solid fa-star"></i>';
        } elseif ($mediaEstrella && $i === $estrellasCompletas + 1) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        } else {
            $html .= '<i class="fa-solid fa-star vacia"></i>';
        }
    }

    return $html;
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

function datosPerfilUsuario(PDO $db, $idUsuario, $estadoFiltro, $paginaBibliotecaActual, $porPaginaBiblioteca = 12) {
    $idUsuario = (int) $idUsuario;
    $paginaBibliotecaActual = max(1, (int) $paginaBibliotecaActual);
    $resumenBiblioteca = cacheResumenBibliotecaUsuario($db, $idUsuario);
    $totalJuegosBiblioteca = cacheContarBibliotecaUsuario($db, $idUsuario, $estadoFiltro);
    $totalPaginasBiblioteca = max(1, (int) ceil($totalJuegosBiblioteca / $porPaginaBiblioteca));

    if ($paginaBibliotecaActual > $totalPaginasBiblioteca) {
        $paginaBibliotecaActual = $totalPaginasBiblioteca;
    }

    $offsetBiblioteca = ($paginaBibliotecaActual - 1) * $porPaginaBiblioteca;
    $histogramaUsuario = cacheHistogramaUsuario($db, $idUsuario);
    $totalPuntuacionesUsuario = array_sum($histogramaUsuario);

    return [
        'resumenBiblioteca' => $resumenBiblioteca,
        'totalJuegosBiblioteca' => $totalJuegosBiblioteca,
        'totalPaginasBiblioteca' => $totalPaginasBiblioteca,
        'paginaBibliotecaActual' => $paginaBibliotecaActual,
        'juegosBiblioteca' => cacheListarBibliotecaUsuario($db, $idUsuario, $estadoFiltro, $porPaginaBiblioteca, $offsetBiblioteca),
        'resenasUsuarioPerfil' => cacheListarResenasUsuario($db, $idUsuario, 5),
        'resenasUsuarioTab' => cacheListarResenasUsuario($db, $idUsuario, 20),
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
