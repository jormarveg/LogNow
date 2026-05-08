<?php

function adminFecha($fecha) {
    if (!$fecha) {
        return 'Sin fecha';
    }

    $marca = strtotime($fecha);

    if ($marca === false) {
        return $fecha;
    }

    return date('d/m/Y H:i', $marca);
}

function adminPaginas($paginaActual, $totalPaginas) {
    if ($totalPaginas <= 7) {
        return range(1, $totalPaginas);
    }

    $paginas = [1];
    $inicio = max(2, $paginaActual - 1);
    $fin = min($totalPaginas - 1, $paginaActual + 1);

    if ($inicio > 2) {
        $paginas[] = '...';
    }

    for ($i = $inicio; $i <= $fin; $i++) {
        $paginas[] = $i;
    }

    if ($fin < $totalPaginas - 1) {
        $paginas[] = '...';
    }

    $paginas[] = $totalPaginas;

    return $paginas;
}

function adminPuntuacion($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    return number_format(((float) $puntuacion) / 20, 1, ',', '.');
}
