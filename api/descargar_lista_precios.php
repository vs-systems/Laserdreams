<?php
require __DIR__ . '/../includes/db.php';

// Include TCPDF
require_once __DIR__ . '/../includes/tcpdf/TCPDF-main/tcpdf.php';

// Extend TCPDF to add watermark and custom header/footer
class MyPDF extends TCPDF
{
    public function Header()
    {
        // Watermark
        $img_file = __DIR__ . '/../assets/img/logo.png';
        if (file_exists($img_file)) {
            // Marca de agua centralizada
            $this->SetAlpha(0.1);
            // x = 55, y = 100, width = 100
            $this->Image($img_file, 55, 100, 100, '', '', '', '', false, 300, '', false, false, 0);
            $this->SetAlpha(1);

            // Logo real en el encabezado
            $this->Image($img_file, 15, 10, 30, '', '', '', '', false, 300, '', false, false, 0);
        }

        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Lista de Precios - Laserdreams', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);

        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(80, 80, 150);
        $this->Cell(0, 10, 'Consultas y Ventas WhatsApp: +54 9 223 577-2165', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetTextColor(0, 0, 0);
        $this->Ln(10);

        $this->Line(15, 35, 195, 35);
    }

    public function Footer()
    {
        $this->SetY(-18);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, '* Los valores expresados son netos. En caso de requerir comprobante fiscal, adicionar el 21% correspondiente a impuestos.', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 8, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' | Laserdreams - Equipamiento técnico e iluminación', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Fetch all active products joined with category and brand
$sql = "
SELECT p.*, c.nombre as categoria_nombre, m.nombre as marca_nombre,
       m.tipo_dolar, m.recargo_dolar_pesos, m.recargo_bancario_porcentaje
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
WHERE p.activo = 1
ORDER BY c.nombre ASC, m.nombre ASC, p.titulo ASC
";

$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PDF Setup
$pdf = new MyPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('Laserdreams');
$pdf->SetAuthor('Laserdreams');
$pdf->SetTitle('Lista de Precios Laserdreams');
$pdf->SetMargins(15, 40, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->AddPage();

$current_category = null;
$current_brand = null;
$fill = false;

foreach ($productos as $p) {
    if ($current_category !== $p['categoria_nombre']) {
        $current_category = $p['categoria_nombre'];
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetFillColor(230, 230, 250); // Light Violet
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(0, 9, 'Categoría: ' . ($current_category ?: 'Sin Categoría'), 0, 1, 'L', true);
        $pdf->SetTextColor(0, 0, 0);
        $current_brand = null; // Reset brand per category
    }

    if ($current_brand !== $p['marca_nombre']) {
        $current_brand = $p['marca_nombre'];

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 8, '  Marca: ' . ($current_brand ?: 'Genérica'), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);

        // Print Table Header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(35, 7, 'Código', 1, 0, 'C', true);
        $pdf->Cell(110, 7, 'Descripción', 1, 0, 'C', true);
        $pdf->Cell(35, 7, 'Precio Neto', 1, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 9);
    }

    $precio_final = calcular_precio_final(
        $p['precio_venta_usd'],
        $p['tipo_dolar'] ?? 'blue',
        $p['recargo_dolar_pesos'] ?? 0,
        $p['recargo_bancario_porcentaje'] ?? 0
    );

    $codigo = !empty($p['codigo']) ? $p['codigo'] : '-';
    $titulo = $p['titulo'];

    // Truncate title if too long to fit in 110 width
    if (strlen($titulo) > 60) {
        $titulo = substr($titulo, 0, 57) . '...';
    }

    // Check if we need a page break (TCPDF handles this, but custom logic helps avoid orphan rows)
    if ($pdf->GetY() > 260) {
        $pdf->AddPage();
    }

    $pdf->SetFillColor(248, 248, 248);
    $pdf->Cell(35, 7, $codigo, 1, 0, 'C', $fill);
    $pdf->Cell(110, 7, $titulo, 1, 0, 'L', $fill);

    // Bold green price format
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Cell(35, 7, '$' . number_format($precio_final, 0, ',', '.'), 1, 1, 'R', $fill);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 9);

    $fill = !$fill;
}

// Clean output buffer before sending PDF (prevents errors if whitespace exists)
ob_clean();
$pdf->Output('Laserdreams_Lista_Precios.pdf', 'D'); // D for Force Download
?>