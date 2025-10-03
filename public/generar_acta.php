<?php
session_start();
require_once '../config/database.php';
require_once '../fpdf/fpdf.php'; // Asegúrate de que la ruta a FPDF sea correcta

if (!isset($_GET['id_asignacion'])) {
    die("Error: No se especificó el ID de la asignación.");
}

$id_asignacion = (int)$_GET['id_asignacion'];

// --- CONSULTA SQL CORREGIDA CON TODOS LOS JOINS ---
$sql = "SELECT 
            a.fecha_entrega, a.observaciones_entrega,
            e.codigo_inventario, e.numero_serie, e.caracteristicas,
            t.nombre AS tipo_nombre,
            ma.nombre AS marca_nombre,
            mo.nombre AS modelo_nombre,
            emp.dni, emp.nombres, emp.apellidos,
            c.nombre AS cargo_nombre,
            ar.nombre AS area_nombre
        FROM asignaciones a
        JOIN equipos e ON a.id_equipo = e.id
        JOIN empleados emp ON a.id_empleado = emp.id
        JOIN tipos_equipo t ON e.id_tipo_equipo = t.id
        JOIN marcas ma ON e.id_marca = ma.id
        JOIN modelos mo ON e.id_modelo = mo.id
        LEFT JOIN cargos c ON emp.id_cargo = c.id
        LEFT JOIN areas ar ON emp.id_area = ar.id
        WHERE a.id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_asignacion);
$stmt->execute();
$resultado = $stmt->get_result();
$datos = $resultado->fetch_assoc();
$stmt->close();

if (!$datos) {
    die("Error: Asignación no encontrada.");
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Acta de Entrega de Equipo de Cómputo', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
    }
}

// --- GENERACIÓN DEL PDF CON LOS DATOS CORREGIDOS ---
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 11);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Fecha de Entrega: ' . date('d/m/Y H:i', strtotime($datos['fecha_entrega'])), 0, 1, 'R');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Datos del Empleado', 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(45, 10, 'Nombres y Apellidos:', 1, 0);
$pdf->Cell(0, 10, $datos['nombres'] . ' ' . $datos['apellidos'], 1, 1);
$pdf->Cell(45, 10, 'DNI:', 1, 0);
$pdf->Cell(0, 10, $datos['dni'], 1, 1);
$pdf->Cell(45, 10, 'Área:', 1, 0);
$pdf->Cell(0, 10, $datos['area_nombre'], 1, 1);
$pdf->Cell(45, 10, 'Cargo:', 1, 0);
$pdf->Cell(0, 10, $datos['cargo_nombre'], 1, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Datos del Equipo Entregado', 1, 1, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(45, 10, 'Código de Inventario:', 1, 0);
$pdf->Cell(0, 10, $datos['codigo_inventario'], 1, 1);
$pdf->Cell(45, 10, 'Tipo de Equipo:', 1, 0);
$pdf->Cell(0, 10, $datos['tipo_nombre'], 1, 1);
$pdf->Cell(45, 10, 'Marca y Modelo:', 1, 0);
$pdf->Cell(0, 10, $datos['marca_nombre'] . ' ' . $datos['modelo_nombre'], 1, 1);
$pdf->Cell(45, 10, 'Número de Serie:', 1, 0);
$pdf->Cell(0, 10, $datos['numero_serie'], 1, 1);
$pdf->Cell(45, 8, 'Características:', 'TL');
$pdf->MultiCell(0, 8, $datos['caracteristicas'], 'TR');
$pdf->Cell(45, 8, 'Observaciones:', 'TLB');
$pdf->MultiCell(0, 8, $datos['observaciones_entrega'], 'TRB');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 5, 'Por medio de la presente, confirmo haber recibido de la empresa el equipo antes descrito, el cual se me entrega como herramienta de trabajo. Me responsabilizo de darle un uso correcto, mantenerlo en buen estado, no instalar programas no autorizados y comunicar cualquier falla. Asimismo, me comprometo a devolverlo cuando la empresa lo requiera o al finalizar mi relacion laboral.', 0, 'J');
$pdf->Ln(30);

$pdf->Cell(95, 10, '_________________________', 0, 0, 'C');
$pdf->Cell(95, 10, '_________________________', 0, 1, 'C');
$pdf->Cell(95, 10, 'Firma del Empleado', 0, 0, 'C');
$pdf->Cell(95, 10, 'Responsable de TI', 0, 1, 'C');

$pdf->Output('I', 'Acta_Entrega_' . $datos['codigo_inventario'] . '.pdf');