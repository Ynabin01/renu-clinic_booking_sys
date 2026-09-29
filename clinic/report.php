<?php
session_start();
require '../config/database.php';
if (empty($_SESSION['clinic_admin'])) {
    http_response_code(403);
    exit('Access denied.');
}

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-t');
$status = $_GET['status'] ?? 'All';
$allowed = ['All', 'Pending', 'Confirmed', 'Cancelled', 'Completed'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) || $from > $to)
    exit('Invalid report date range.');
if (!in_array($status, $allowed, true))
    $status = 'All';

$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
               p.name AS patient_name, p.phone AS patient_phone,
               c.clinic_name, d.doctor_name, d.specialization
        FROM appointments a
        JOIN patients p ON p.patient_id=a.patient_id
        JOIN clinics c ON c.clinic_id=a.clinic_id
        LEFT JOIN doctors d ON d.doctor_id=a.doctor_id
        WHERE a.appointment_date BETWEEN ? AND ?";
$params = [$from, $to];
if ($status !== 'All') {
    $sql .= ' AND a.status=?';
    $params[] = $status;
}
$sql .= ' ORDER BY a.appointment_date, a.appointment_time, c.clinic_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

function cleanPdfText($text)
{
    $text = (string) $text;
    if (function_exists('iconv'))
        $text = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
    $text = preg_replace('/[^\x20-\x7E]/', '', $text);
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}
function wrapPdfLine($text, $width = 88)
{
    $text = preg_replace('/\s+/', ' ', trim((string) $text));
    if ($text === '')
        return [''];
    return str_split($text, $width);
}
function makePdf($pages)
{
    $objects = [];
    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $kids = [];
    $objectNo = 3;
    foreach ($pages as $lines) {
        $pageNo = $objectNo;
        $contentNo = $objectNo + 1;
        $kids[] = $pageNo . ' 0 R';
        $content = "BT\n/F1 9 Tf\n45 790 Td\n12 TL\n";
        foreach ($lines as $line)
            $content .= '(' . cleanPdfText($line) . ") Tj\nT*\n";
        $content .= "ET";
        $objects[$pageNo] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 7 0 R >> >> /Contents ' . $contentNo . ' 0 R >>';
        $objects[$contentNo] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        $objectNo += 2;
    }
    $fontNo = $objectNo;
    $objects[$fontNo] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
    $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($pages) . ' >>';
    ksort($objects, SORT_NUMERIC);

    $pdf = "%PDF-1.4\n%ClinicReport\n";
    $offsets = [];
    $maxObject = max(array_keys($objects));
    for ($i = 1; $i <= $maxObject; $i++) {
        if (!isset($objects[$i]))
            continue;
        $offsets[$i] = strlen($pdf);
        $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . ($maxObject + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= $maxObject; $i++)
        $pdf .= sprintf('%010d 00000 n \n', $offsets[$i] ?? 0);
    $pdf .= "trailer\n<< /Size " . ($maxObject + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
    return $pdf;
}

$lines = [];
$lines[] = 'ONLINE CLINIC APPOINTMENT REPORT';
$lines[] = 'Date range: ' . $from . ' to ' . $to;
$lines[] = 'Status filter: ' . $status;
$lines[] = 'Generated: ' . date('Y-m-d H:i');
$lines[] = 'Total appointments: ' . count($rows);
$lines[] = str_repeat('-', 88);

if (!$rows) {
    $lines[] = 'No appointments were found for the selected date range and status.';
} else {
    foreach ($rows as $r) {
        $doctor = $r['doctor_name'] ? $r['doctor_name'] . ' (' . $r['specialization'] . ')' : 'Not selected';
        $lines[] = 'Appointment #' . $r['appointment_id'] . ' | ' . $r['appointment_date'] . ' | ' . substr($r['appointment_time'], 0, 5);
        $lines = array_merge($lines, wrapPdfLine('Patient: ' . $r['patient_name'] . ' | Phone: ' . $r['patient_phone']));
        $lines = array_merge($lines, wrapPdfLine('Clinic: ' . $r['clinic_name']));
        $lines = array_merge($lines, wrapPdfLine('Doctor: ' . $doctor . ' | Status: ' . $r['status']));
        $lines[] = str_repeat('-', 88);
    }
}

$pages = array_chunk($lines, 55);
$pdf = makePdf($pages ?: [['No data.']]);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="clinic_report_' . $from . '_to_' . $to . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, max-age=0, must-revalidate');
echo $pdf;
exit;
