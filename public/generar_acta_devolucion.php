<?php
session_start();
require_once '../config/database.php';
require_once '../fpdf/fpdf.php';

if (!isset($_GET['id_asignacion'])) {
    die("Error: ID de asignación no especificado.");
}

$id_asignacion = (int)$_GET['id_asignacion'];

// Consulta para obtener todos los datos necesarios para el acta
Require_once '../api_clients/AsignacionApiClient.php';
$asignacionApiClient = new AsignacionApiClient();


try {
    $datos = $asignacionApiClient->obtenerAsignacionDevolucion($id_asignacion);
    if (!$datos) {
        die("Error: Asignación no encontrada.");
    }
} catch (Exception $e) {
    die("Error al obtener los datos de la asignación: " . $e->getMessage());
}

$usuario_ti_recibe = $_SESSION['user_nombre'];

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, mb_convert_encoding('Acta de Devolución de Equipo', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(10);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ','ISO-8859-1', 'UTF-8') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Fecha de Devolucion: ' . date('d/m/Y H:i', strtotime($datos['fechaDevolucion'])), 0, 1, 'R');
$pdf->Ln(5);

// --- INICIO: SECCIÓN DE DATOS DEL EQUIPO (AÑADIDA) ---
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Datos del Equipo Devuelto', 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(45, 10, mb_convert_encoding('Código de Inventario:','ISO-8859-1', 'UTF-8'), 1, 0);
$pdf->Cell(0, 10, mb_convert_encoding($datos['codigoInventario'],'ISO-8859-1', 'UTF-8'), 1, 1);
$pdf->Cell(45, 10, 'Tipo de Equipo:', 1, 0);
$pdf->Cell(0, 10, mb_convert_encoding($datos['tipoNombre'],'ISO-8859-1', 'UTF-8'), 1, 1);
$pdf->Cell(45, 10, 'Marca y Modelo:', 1, 0);
$pdf->Cell(0, 10, mb_convert_encoding($datos['marcaNombre'] . ' ' . $datos['modeloNombre'],'ISO-8859-1', 'UTF-8'), 1, 1);
$pdf->Cell(45, 10, mb_convert_encoding('Número de Serie:','ISO-8859-1', 'UTF-8'), 1, 0);
$pdf->Cell(0, 10, mb_convert_encoding($datos['numeroSerie'],'ISO-8859-1', 'UTF-8'), 1, 1);
$pdf->Ln(10);
// --- FIN: SECCIÓN DE DATOS DEL EQUIPO ---

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, mb_convert_encoding('Detalles de la Devolución','ISO-8859-1', 'UTF-8'), 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(45, 8, 'Equipo Devuelto por:', 1, 0);
$pdf->Cell(0, 8, mb_convert_encoding($datos['apellidos'] . ', ' . $datos['nombres'],'ISO-8859-1', 'UTF-8'), 1, 1);
$pdf->Cell(45, 8, 'Equipo Recibido por:', 1, 0);
$pdf->Cell(0, 8, mb_convert_encoding($usuario_ti_recibe,'ISO-8859-1', 'UTF-8'), 1, 1);
$pdf->Cell(45, 8, 'Observaciones:', 'TLB');
$pdf->MultiCell(0, 8, mb_convert_encoding($datos['observacionesDevolucion'],'ISO-8859-1', 'UTF-8'), 'TRB');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, mb_convert_encoding("Se deja constancia de que el equipo ha sido devuelto por el empleado y recibido por el área de TI en la fecha y con las observaciones indicadas. Ambas partes firman en señal de conformidad.",'ISO-8859-1', 'UTF-8'), 0, 'J');

// --- SECCIÓN DE EVIDENCIA FOTOGRÁFICA ---
Require_once '../api_clients/AsignacionApiClient.php';
$asignacionApiClient = new AsignacionApiClient();

//$stmt_img = $conexion->prepare("SELECT imagen_devolucion_1, imagen_devolucion_2, imagen_devolucion_3 FROM asignaciones WHERE id = ?");
//$stmt_img->bind_param("i", $id_asignacion);
//$stmt_img->execute();
//$imagenes = $stmt_img->get_result()->fetch_assoc();
//$stmt_img->close();

try {
    // Llamada al API
    $imagenes = $asignacionApiClient->obtenerImagenesAsignacion($id_asignacion);

    // Si no hay resultado o no es un array, inicializamos como array vacío
    if (!is_array($imagenes)) {
        $imagenes = [];
    }

    // Filtramos solo las imágenes que existan
    $imagenes_adjuntas = array_filter([
        $imagenes['imagenDevolucion1'] ?? null,
        $imagenes['imagenDevolucion2'] ?? null,
        $imagenes['imagenDevolucion3'] ?? null
    ]);

} catch (Exception $e) {
    die("Error al obtener las imágenes de la asignación: " . $e->getMessage());
}

if (!empty($imagenes_adjuntas)) {
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, mb_convert_encoding('Evidencia Fotográfica Adjunta','ISO-8859-1', 'UTF-8'), 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 5, mb_convert_encoding("Se adjuntan " . count($imagenes_adjuntas) . " imagen(es) como evidencia del estado del equipo al momento de la devolución. Estos archivos se encuentran almacenados en el sistema.",'ISO-8859-1', 'UTF-8'), 0, 'J');
}

$pdf->Ln(15);

$pdf->Cell(95, 10, '_________________________', 0, 0, 'C');
$pdf->Cell(95, 10, '_________________________', 0, 1, 'C');
$pdf->Cell(95, 10, 'Firma del Empleado', 0, 0, 'C');
$pdf->Cell(95, 10, 'Recibido por (TI)', 0, 1, 'C');

$pdf->Output('I', 'Acta_Devolucion_' . $datos['codigoInventario'] . '.pdf');