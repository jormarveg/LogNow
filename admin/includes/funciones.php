<?php

require_once __DIR__ . '/../../includes/paginacion_helpers.php';

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

function adminPuntuacion($puntuacion) {
    if ($puntuacion === null) {
        return 'N/D';
    }

    return number_format(((float) $puntuacion) / 20, 1, ',', '.');
}
