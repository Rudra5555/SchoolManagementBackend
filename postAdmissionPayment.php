<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// DB connection
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['user']) || !isset($_FILES['photo'])) {
        echo json_encode(["status" => "error", "message" => "Missing JSON data or file"]);
        exit;
    }

    $data = json_decode($_POST['user'], true);
    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
        exit;
    }

    // Handle multiple payment types
    // $allowed_payment_types = [];
    $payment_types = $data['payment_type'] ?? [];

    if (!is_array($payment_types) || empty($payment_types)) {
        echo json_encode(["status" => "error", "message" => "Invalid or missing payment_type"]);
        exit;
    }

    // Convert payment types to a comma-separated string (if array)
    $payment_type_string = implode(", ", $payment_types);

    // Image validation
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tmpName = $_FILES['photo']['tmp_name'];
    $originalName = basename($_FILES['photo']['name']);
    $fileType = mime_content_type($tmpName);
    $fileSize = $_FILES['photo']['size'];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(["status" => "error", "message" => "Only JPG and PNG images allowed"]);
        exit;
    }

    if ($fileSize > $maxSize) {
        echo json_encode(["status" => "error", "message" => "Image size exceeds 2MB"]);
        exit;
    }

    $timestamp = time();
    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
    $fileBaseName = pathinfo($originalName, PATHINFO_FILENAME);
    $safeNameWithTimestamp = $fileBaseName . "_" . $timestamp . "." . $fileExtension;
    $destination = $uploadDir . $safeNameWithTimestamp;

    if (!move_uploaded_file($tmpName, $destination)) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }

    $photo = "http://localhost/schoolManagementBackend/SchoolManagementBackend/" . $destination;

    // Extract other fields
    $user_name = $data['user_name'] ?? '';
    $bank_name = $data['bank_name'] ?? '';
    $ifsc_code = $data['ifsc_code'] ?? '';
    $account_number = $data['account_number'] ?? '';
    $branch_name = $data['branch_name'] ?? '';
    $contact_number = $data['contact_number'] ?? '';

    // Insert into DB
    $sql = "INSERT INTO payment_details (user_name,
        bank_name, ifsc_code, account_number, branch_name, payment_type, contact_number, image_path
    ) VALUES (?,?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssss",
        $user_name,
        $bank_name,
        $ifsc_code,
        $account_number,
        $branch_name,
        $payment_type_string,
        $contact_number,
        $photo
    );

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Admission Payment inserted successfully",
            "photo_url" => $photo,
            "file_name" => $safeNameWithTimestamp
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST method allowed"]);
}

$conn->close();
?>
