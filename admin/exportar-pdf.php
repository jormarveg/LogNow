<?php
require __DIR__ . '/includes/proteger.php';
require __DIR__ . '/includes/funciones.php';
require __DIR__ . '/../lib/FPDF/fpdf.php';

function textoPdf($texto) {
    return mb_convert_encoding((string) $texto, 'ISO-8859-1', 'UTF-8');
}

$busqueda = trim($_GET['q'] ?? '');
$rolFiltro = $_GET['rol'] ?? '';
$estadoFiltro = $_GET['estado'] ?? '';

if (!in_array($rolFiltro, ['usuario', 'admin'], true)) {
    $rolFiltro = '';
}

if (!in_array($estadoFiltro, ['activos', 'inactivos'], true)) {
    $estadoFiltro = '';
}

$where = [];
$params = [];

if ($busqueda !== '') {
    $where[] = '(u.nombre LIKE ? OR u.nick LIKE ? OR u.email LIKE ?)';
    $textoBusqueda = '%' . $busqueda . '%';
    $params[] = $textoBusqueda;
    $params[] = $textoBusqueda;
    $params[] = $textoBusqueda;
}

if ($rolFiltro !== '') {
    $where[] = 'u.rol = ?';
    $params[] = $rolFiltro;
}

if ($estadoFiltro === 'activos') {
    $where[] = 'u.activo = 1';
} elseif ($estadoFiltro === 'inactivos') {
    $where[] = 'u.activo = 0';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmtUsuarios = $db->prepare("SELECT u.nombre, u.nick, u.email, u.rol, u.activo, u.registro
                              FROM USUARIO u
                              $whereSql
                              ORDER BY u.registro DESC, u.id DESC");
$stmtUsuarios->execute($params);
$usuarios = $stmtUsuarios->fetchAll();

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 10, 'LogNow! - Usuarios', 1, 1, 'C');
        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 5, textoPdf('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('L');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 7, textoPdf('Listado generado el ') . date('d/m/Y H:i'), 0, 1);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(42, 8, 'Nombre', 1, 0, 'C');
$pdf->Cell(28, 8, 'Nick', 1, 0, 'C');
$pdf->Cell(82, 8, 'Email', 1, 0, 'C');
$pdf->Cell(24, 8, 'Rol', 1, 0, 'C');
$pdf->Cell(24, 8, 'Estado', 1, 0, 'C');
$pdf->Cell(48, 8, 'Registro', 1, 1, 'C');

$pdf->SetFont('Arial', '', 9);

foreach ($usuarios as $usuario) {
    if ($pdf->GetY() > 185) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(42, 8, 'Nombre', 1, 0, 'C');
        $pdf->Cell(28, 8, 'Nick', 1, 0, 'C');
        $pdf->Cell(82, 8, 'Email', 1, 0, 'C');
        $pdf->Cell(24, 8, 'Rol', 1, 0, 'C');
        $pdf->Cell(24, 8, 'Estado', 1, 0, 'C');
        $pdf->Cell(48, 8, 'Registro', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 9);
    }

    $estado = $usuario['activo'] ? 'Activo' : 'Inactivo';
    $pdf->Cell(42, 7, textoPdf($usuario['nombre']), 1);
    $pdf->Cell(28, 7, textoPdf($usuario['nick']), 1);
    $pdf->Cell(82, 7, textoPdf($usuario['email']), 1);
    $pdf->Cell(24, 7, textoPdf($usuario['rol']), 1, 0, 'C');
    $pdf->Cell(24, 7, textoPdf($estado), 1, 0, 'C');
    $pdf->Cell(48, 7, textoPdf(adminFecha($usuario['registro'])), 1, 1);
}

if (!$usuarios) {
    $pdf->Cell(0, 8, textoPdf('No hay usuarios con esos filtros.'), 1, 1, 'C');
}

$pdf->Output('D', 'usuarios-lognow.pdf');
