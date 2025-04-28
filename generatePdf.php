<?php
//require('fpdf.php');
require('tfpdf.php');
require('downloadPdf.php');
require('util.php');
// require('db_connection.php'); // Make sure this file exists and contains $conn
$servername = "localhost";
$username = "root";
$password = "1234";
$database = "Admission"; // ← Replace this

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Display errors for debugging
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Required fields
    $requiredFields = ['transaction_id', 'admission_no', 'student_name', 'session', 'class', 'payment_date', 'fees'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "$field is required"]);
            exit;
        }
    }

    $transaction_id = $input['transaction_id'];
    $admission_no = $input['admission_no'];
    $student_name = $input['student_name'];
    $session = $input['session'];
    $class = $input['class'];
    $payment_date = $input['payment_date'];
    $fees = $input['fees'];

    $total = array_sum($fees);

    // Save to DB
    $stmt = $conn->prepare("INSERT INTO fee_receipts (transaction_id, admission_no, student_name, session, class, payment_date, total_paid) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssd", $transaction_id, $admission_no, $student_name, $session, $class, $payment_date, $total);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
        exit;
    }

    // === Generate PDF ===
   // $pdf = new FPDF();
    $pdf = new tFPDF();
    
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,8,"Christ Church Girls' High School(H.S)",0,1,'C');
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(0,6,"30, Jessore Road, Dumdum, Kolkata - 700028",0,1,'C');
    $pdf->Cell(0,6,"Phone: 033-2559-2030",0,1,'C');
    $pdf->Cell(0,6,"Email: christchurchschool@ccghs.in / christchurchschoold@gmail.com",0,1,'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,"Fee Receipt",0,1,'C');
    $pdf->Ln(3);

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(33,8,'Transaction ID',1);
    $pdf->Cell(33,8,'Payment Date',1);
    $pdf->Cell(30,8,'Admission No',1);
    $pdf->Cell(35,8,'Student Name',1);
    $pdf->Cell(25,8,'Class',1);
    $pdf->Cell(34,8,'Session',1);
    $pdf->Ln();
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(33,8,$transaction_id,1);
    $pdf->Cell(33,8,$payment_date,1);
    $pdf->Cell(30,8,$admission_no,1);
    $pdf->Cell(35,8,$student_name,1);
    $pdf->Cell(25,8,$class,1);
    $pdf->Cell(34,8,$session,1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);
    $pdf->SetX(20);
    $pdf->Cell(150,10,'DEC - 2024','TB',0,'L');
    $pdf->Ln(10);

    // $pdf->SetFont('Arial','B',11);
    $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
    $pdf->SetFont('DejaVu','',14);
    $pdf->SetX(20);
    $pdf->Cell(100,8,'Fee Type','TB',0);
    $pdf->Cell(50,8,'Amount (₹)','TB',0);
    $pdf->Ln();

    //$pdf->SetFont('Arial','',11);
    //setlocale(LC_MONETARY, 'en_IN.UTF-8');
    $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
    $pdf->SetFont('DejaVu','',14);
    foreach ($fees as $type => $amount) {
        $pdf->SetX(20);
        $pdf->Cell(100,8,$type,'TB',0);
        $formatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
        $formattedAmount = $formatter->formatCurrency($amount, 'INR');
        $pdf->Cell(50,8,'₹ ' . number_format($amount, 2),'TB',0);
        $pdf->Ln();
    }

    // $pdf->SetFont('Arial','B',11);
    $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
    $pdf->SetFont('DejaVu','',14);
    $pdf->SetX(20);
    $pdf->Cell(100,8,'Total','TB',0);
    $formatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
    $totalAmount = $formatter->formatCurrency($total, 'INR');
    $pdf->Cell(50,8,'₹  ' . number_format($amount, 2),'TB',0);
    $pdf->Ln(10);

    // $pdf->SetFont('Arial','B',12);
    $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
    $pdf->SetFont('DejaVu','',14);
    $pdf->SetX(70);
    $pdf->Cell(55,10,'Total Fees Paid:' . '₹  ' . number_format($amount, 2),1,0,'C');
    $pdf->Ln(15);

    ob_clean(); // Prevents PDF corruption
  //  $pdf->Output('I', 'fee_receipt.pdf');
   $filename = 'myfile.pdf'; // Create "downloads" folder if not exists
   $pdf->Output('F', $filename);
   $baseURL = getBaseURL();
   $url = $baseURL.$filename; 
    echo json_encode(['url' => $url]);

  //  echo '<a href="'.$filename.'" download>Download PDF</a>';
} else {
    echo json_encode(['error' => 'Invalid Request']);
}
?>
