<?php
require_once 'auth.php';
require_once 'docs.php';
require_once 'user.php';
require_once 'activity.php';
require_once 'fpdf/fpdf.php';

$monthNames = [
    'Januari', 'Februari', 'Maret',
    'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September',
    'Oktober', 'November', 'Desember'
];

$month = new DateTime();
$month->setDate(date('Y'), date('n'), 1);
$monthName = strtoupper($monthNames[$month->format('n') - 1]);

$user_id = $_SESSION['user_id'];
$loggedInUser = getLoggedInUserData();
$workActivities = getWorkActivities($user_id);
$documentationData = getDocumentationData($user_id);

$count = 1;
$count2 = 1;

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN AKTIVITAS KERJA PEMEGANG SERTIFIKAT UJI RADIOGRAFI', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'BULAN : ' . $monthName, 0, 1, 'C');

$pdf->Cell(0, 10, '-------------------------------------------------------------------------------------------------------------------------------', 0, 1);

$pdf->Cell(0, 10, 'Kami yang bertanda tangan di bawah ini', 0, 1);

$pdf->Cell(70, 10, 'Nama Pemegang Sertifikat', 0);
$pdf->Cell(70, 10, ': ' . $loggedInUser['full_name'], 0, 1);
$pdf->Cell(70, 10, 'Kualifikasi', 0);
$pdf->Cell(70, 10, ': ' . $loggedInUser['qualifications'], 0, 1);
$pdf->Cell(70, 10, 'Perusahaan', 0);
$pdf->Cell(70, 10, ': ' . $loggedInUser['company'], 0, 1);
$pdf->Cell(70, 10, 'Nomor Sertifikat', 0);
$pdf->Cell(70, 10, ': ' . $loggedInUser['certificate_number'], 0, 1);
$pdf->Cell(70, 10, 'Tanggal Terbit Sertifikat', 0);
$pdf->Cell(70, 10, ': ' . date('d-m-Y', strtotime($loggedInUser['certificate_issue_date'])), 0, 1);
$pdf->Cell(70, 10, 'Masa Berlaku Sertifikat s.d. Tanggal', 0);
$pdf->Cell(70, 10, ': ' . date('d-m-Y', strtotime($loggedInUser['certificate_validity_period_until_date'])), 0, 1);

$pdf->Cell(0, 10, '-------------------------------------------------------------------------------------------------------------------------------', 0, 1);
$pdf->Cell(0, 10, 'Menyatakan dengan sesungguhnya bahwa telah melakukan pekerjaan Radiografi sebagai berikut', 0, 1);

$pdf->SetFont('arial', '', 11);

$pdf->Cell(7, 10, 'No', 1);
$pdf->Cell(25, 10, 'Tanggal', 1);
$pdf->Cell(25, 10, 'Lokasi', 1);
$pdf->Cell(30, 10, 'Nama Kegiatan', 1);
$pdf->Cell(45, 10, 'Pemilik Project', 1);
$pdf->Cell(55, 10, 'Kordinator Lapangan PT', 1);
$pdf->Ln();

foreach ($workActivities as $activity) {
    $pdf->Cell(7, 10, $count++ . '.', 1);
    $formattedDate = date('d-m-Y', strtotime($activity['date']));
    $pdf->Cell(25, 10, $formattedDate, 1);
    $location = substr($activity['location'], 0, 10);
    $pdf->Cell(25, 10, $location, 1);
    $activityName = substr($activity['activity_name'], 0, 12);
    $pdf->Cell(30, 10, $activityName, 1);
    $employerOrganization = substr($activity['employer_organization'], 0, 17);
    $pdf->Cell(45, 10, $employerOrganization, 1);
    $companyFieldCoordinator = substr($activity['company_field_coordinator'], 0, 20);
    $pdf->Cell(55, 10, $companyFieldCoordinator, 1);
    $pdf->Ln();
}

$pdf->AddPage();
$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 3, 'Foto Kegiatan :', 0, 1);
$pdf->SetFont('arial', '', 11);

foreach ($documentationData as $doc) {
    if ($count2 % 4 == 1) {
        $pdf->Ln();
    }
    $pdf->Image($doc['image_path'], $pdf->GetX() + 6, $pdf->GetY(), 40);
    $pdf->Cell(46, 40, $count2 . '.', 1);

    $count2++;
}

$pdf->Ln();
$pdf->Ln();
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Demikian laporan ini dibuat dengan sesungguhnya', 0, 1);
$pdf->Cell(140, 10, 'Kordinator Lapangan Perusahaan', 0, 0);
$pdf->Cell(50, 10, 'Pemegang Sertifikat', 0, 1);
$pdf->Cell(140, 20, '', 0, 0);
$pdf->Cell(50, 20, '', 0, 1);
$pdf->Cell(70, 10, '...', 0, 0, 'C');
$pdf->Cell(70, 10, '', 0, 0, 'C');
$pdf->Cell(50, 10, '...', 0, 1, 'C');
$pdf->Cell(140, 10, 'Organisasi Pemberi Kerja/Pemilik Project', 0, 0,);
$pdf->Cell(50, 10, '', 0, 1);
$pdf->Cell(140, 30, '1.', 0, 0);
$pdf->Cell(50, 30, '...', 0, 1, 'C');
$pdf->Cell(140, 30, '2.', 0, 0);
$pdf->Cell(50, 30, '...', 0, 1, 'C');
$pdf->Ln();

$pdf->Cell(0, 7, 'Catatan:', 0, 1);
$pdf->Cell(0, 7, '1. Formulir diupload setiap bulan, paling lambat tanggal 5 pada bulan berikutnya', 0, 1);
$pdf->Cell(0, 7, '2. Organisasi Pemberi Kerja/Pemilik Projek sangat dimungkinkan lebih dari 1 organisasi.', 0, 1);
$pdf->Cell(0, 7, '3. Tanda tangan dibubuhi dengan stempel organisasi/perusahaan.', 0, 1);
$pdf->Cell(0, 7, '4. Foto Kegiatan yang diupload adalah foto kegiatan setiap pindah lokasi atau ganti objek', 0, 1);
$pdf->Cell(0, 7, '    yang diradiografi.', 0, 1);


$pdf->Output('I');
