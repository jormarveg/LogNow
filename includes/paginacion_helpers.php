<?php

function paginasCompactas($paginaActual, $totalPaginas) {
    $paginaActual = max(1, (int) $paginaActual);
    $totalPaginas = max(1, (int) $totalPaginas);

    if ($paginaActual > $totalPaginas) {
        $paginaActual = $totalPaginas;
    }

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
