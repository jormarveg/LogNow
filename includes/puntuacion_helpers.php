<?php

function puntuacionVisible($puntuacion, $vacio = 'N/D') {
    if ($puntuacion === null) {
        return $vacio;
    }

    $puntuacion = (float) $puntuacion;

    if (abs($puntuacion - round($puntuacion)) < 0.05) {
        return number_format($puntuacion, 0, ',', '.');
    }

    return number_format($puntuacion, 1, ',', '.');
}

function estrellasHtml($puntuacion) {
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
