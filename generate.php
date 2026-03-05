<?php
require('fpdf/fpdf.php');

/* ---------- EXTEND FPDF FOR ROUNDED RECT ---------- */
class PDF extends FPDF {
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        $op = ($style == 'F') ? 'f' : (($style == 'FD' || $style == 'DF') ? 'B' : 'S');
        $MyArc = 4 / 3 * (sqrt(2) - 1);

        $this->_out(sprintf('%.2F %.2F m', ($x+$r)*$k, ($hp-$y)*$k));
        $this->_out(sprintf('%.2F %.2F l', ($x+$w-$r)*$k, ($hp-$y)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$w-$r+$r*$MyArc)*$k, ($hp-$y)*$k,
            ($x+$w)*$k, ($hp-($y+$r-$r*$MyArc))*$k,
            ($x+$w)*$k, ($hp-($y+$r))*$k));
        $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k, ($hp-($y+$h-$r))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$w)*$k, ($hp-($y+$h-$r+$r*$MyArc))*$k,
            ($x+$w-$r+$r*$MyArc)*$k, ($hp-($y+$h))*$k,
            ($x+$w-$r)*$k, ($hp-($y+$h))*$k));
        $this->_out(sprintf('%.2F %.2F l', ($x+$r)*$k, ($hp-($y+$h))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$r-$r*$MyArc)*$k, ($hp-($y+$h))*$k,
            ($x)*$k, ($hp-($y+$h-$r+$r*$MyArc))*$k,
            ($x)*$k, ($hp-($y+$h-$r))*$k));
        $this->_out(sprintf('%.2F %.2F l', ($x)*$k, ($hp-($y+$r))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x)*$k, ($hp-($y+$r-$r*$MyArc))*$k,
            ($x+$r-$r*$MyArc)*$k, ($hp-$y)*$k,
            ($x+$r)*$k, ($hp-$y)*$k));
        $this->_out($op);
    }
}

/* ---------- DATABASE ---------- */
$conn = new mysqli("localhost", "root", "", "nametag");
$result = $conn->query("SELECT * FROM delage ORDER BY Campus");

/* ---------- PDF (LONG PAPER) ---------- */
$pdf = new PDF('P', 'mm', 'Legal');
$pdf->AddPage();

/* ---------- NAMETAG SIZE ---------- */
$tagWidth  = 95;
$tagHeight = 40;

/* ---------- LAYOUT ---------- */
$marginX = 10;
$marginY = 10;
$gapX = 10;
$gapY = 8;
$cols = 2;

/* ---------- COLORS ---------- */
$colors = [
    [255, 90, 95],
    [255, 180, 0],
    [0, 170, 170],
    [60, 120, 216],
    [160, 90, 200],
];

$campusColors = [];
$colorIndex = 0;

/* ---------- POSITION TRACKING ---------- */
$xPositions = [];
for ($i=0; $i<$cols; $i++) {
    $xPositions[] = $marginX + $i * ($tagWidth + $gapX);
}

$currentXIndex = 0;
$currentY = $marginY;
$pageHeight = $pdf->GetPageHeight();

while ($row = $result->fetch_assoc()) {

    /* ---------- PAGE OVERFLOW CHECK ---------- */
    if ($currentY + $tagHeight + $gapY > $pageHeight - $marginY) {
        $pdf->AddPage();
        $currentY = $marginY;
        $currentXIndex = 0;
    }

    $x = $xPositions[$currentXIndex];
    $y = $currentY;

    if (!isset($campusColors[$row['Campus']])) {
        $campusColors[$row['Campus']] = $colors[$colorIndex++ % count($colors)];
    }
    [$r,$g,$b] = $campusColors[$row['Campus']];

    /* ---------- COLORED EDGE ---------- */
    $pdf->SetFillColor($r,$g,$b);
    $pdf->RoundedRect($x, $y, $tagWidth, $tagHeight, 6, 'F');

    /* ---------- WHITE BODY ---------- */
    $pdf->SetFillColor(255,255,255);
    $pdf->RoundedRect($x+2, $y+2, $tagWidth-4, $tagHeight-4, 5, 'F');

    /* ---------- HELLO BACKGROUND ---------- */
    $pdf->SetFillColor($r,$g,$b);
    $pdf->Rect($x+2, $y+2, $tagWidth-4, 9, 'F');

    /* ---------- TEXT ---------- */
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Arial','B',9);
    $pdf->SetXY($x+2, $y+4);
    $pdf->Cell($tagWidth-4, 5, 'HELLO MY NAME IS', 0, 0, 'C');

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','B',20);
    $pdf->SetXY($x+2, $y+14);
    $pdf->Cell($tagWidth-4, 10, strtoupper($row['FirstName']), 0, 0, 'C');

    $pdf->SetFont('Arial','B',11);
    $pdf->SetXY($x+2, $y+28);
    $pdf->Cell($tagWidth-4, 6, $row['Campus'], 0, 0, 'C');

    /* ---------- MOVE CURSOR ---------- */
    $currentXIndex++;

    if ($currentXIndex >= $cols) {
        $currentXIndex = 0;
        $currentY += $tagHeight + $gapY;
    }
}

$conn->close();

/* ---------- OUTPUT ---------- */
$mode = $_GET['mode'] ?? 'download';
$pdf->Output($mode === 'preview' ? 'I' : 'D', 'nametags_long_paper.pdf');
exit;
?>