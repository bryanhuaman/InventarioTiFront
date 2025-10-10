<?php
session_start();
require_once '../config/database.php';
require_once '../fpdf/fpdf.php'; // Asegúrate de que la ruta a FPDF sea correcta

if (!isset($_GET['id_asignacion'])) {
    die("Error: No se especificó el ID de la asignación.");
}

$id_asignacion = (int)$_GET['id_asignacion'];

// --- CONSULTA  ---
Require_once '../api_clients/AsignacionApiClient.php';
$asignacionApiClient = new AsignacionApiClient();


try {
    $datos = $asignacionApiClient->obtenerAsignacionDetalle($id_asignacion);
    if (!$datos) {
        die("Error: Asignación no encontrada.");
    }
} catch (Exception $e) {
    die("Error al obtener los datos de la asignación: " . $e->getMessage());
}



class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Acta de Entrega de Equipo de Computo', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// --- GENERACIÓN DEL PDF CON LOS DATOS CORREGIDOS ---
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Fecha de Entrega: ' . date('d/m/Y H:i', strtotime($datos['fechaEntrega'])), 0, 1, 'R');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Datos del Empleado', 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(45, 10, 'Nombres y Apellidos:', 1, 0);
$pdf->Cell(0, 10, $datos['nombres'] . ' ' . $datos['apellidos'], 1, 1);
$pdf->Cell(45, 10, 'DNI:', 1, 0);
$pdf->Cell(0, 10, $datos['dni'], 1, 1);
$pdf->Cell(45, 10, 'Area:', 1, 0);
$pdf->Cell(0, 10, $datos['areaNombre'], 1, 1);
$pdf->Cell(45, 10, 'Cargo:', 1, 0);
$pdf->Cell(0, 10, $datos['cargoNombre'], 1, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Datos del Equipo Entregado', 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(45, 10, 'Codigo de Inventario:', 1, 0);
$pdf->Cell(0, 10, $datos['codigoInventario'], 1, 1);
$pdf->Cell(45, 10, 'Tipo de Equipo:', 1, 0);
$pdf->Cell(0, 10, $datos['tipoNombre'], 1, 1);
$pdf->Cell(45, 10, 'Marca y Modelo:', 1, 0);
$pdf->Cell(0, 10, $datos['marcaNombre'] . ' ' . $datos['modeloNombre'], 1, 1);
$pdf->Cell(45, 10, 'Numero de Serie:', 1, 0);
$pdf->Cell(0, 10, $datos['numeroSerie'], 1, 1);
$pdf->Cell(45, 8, 'Caracteristicas:', 'TL');
$pdf->MultiCell(0, 8, $datos['caracteristicas'], 'TR');
$pdf->Cell(45, 8, 'Observaciones:', 'TLB');
$pdf->MultiCell(0, 8, $datos['observacionesEntrega'], 'TRB');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, 'Por medio de la presente, confirmo haber recibido de la empresa el equipo antes descrito, el cual se me entrega como herramienta de trabajo. Me responsabilizo de darle un uso correcto, mantenerlo en buen estado, no instalar programas no autorizados y comunicar cualquier falla. Asimismo, me comprometo a devolverlo cuando la empresa lo requiera o al finalizar mi relacion laboral.', 0, 'J');
$pdf->Ln(30);

$pdf->Cell(95, 10, '_________________________', 0, 0, 'C');
$pdf->Cell(95, 10, '_________________________', 0, 1, 'C');
$pdf->Cell(95, 10, 'Firma del Empleado', 0, 0, 'C');
$pdf->Cell(95, 10, 'Responsable de TI', 0, 1, 'C');

$pdf->Output('I', 'Acta_Entrega_' . $datos['codigoInventario'] . '.pdf');