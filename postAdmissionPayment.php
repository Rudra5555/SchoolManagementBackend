
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if required data is present
    if (!isset($_POST['user']) || !isset($_FILES['photo'])) {
        echo json_encode(["status" => "error", "message" => "Missing JSON data or file"]);
        exit;
    }

    // Decode JSON data
    $data = json_decode($_POST['user'], true);
    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
        exit;
    }

    // Handle payment_type (if it's a string or array)
    $payment_type_string = is_array($data['payment_type']) ? implode(",", $data['payment_type']) : $data['payment_type'];

    // Handle monthly array (if it exists)
    $monthly_string = isset($data['monthly']) ? implode(",", $data['monthly']) : "";

    // File upload logic
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

    // Check file type and size
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(["status" => "error", "message" => "Only JPG and PNG images allowed"]);
        exit;
    }

    if ($fileSize > $maxSize) {
        echo json_encode(["status" => "error", "message" => "Image size exceeds 2MB"]);
        exit;
    }

    // Generate a safe filename with timestamp
    $timestamp = time();
    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
    $fileBaseName = pathinfo($originalName, PATHINFO_FILENAME);
    $safeNameWithTimestamp = $fileBaseName . "_" . $timestamp . "." . $fileExtension;
    $destination = $uploadDir . $safeNameWithTimestamp;

    // Move the uploaded file
    if (!move_uploaded_file($tmpName, $destination)) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }

    // File URL for response
    $photo = "http://localhost/schoolManagementBackend/SchoolManagementBackend/" . $destination;

    // Assigning data to variables
    $student_name = $data['student_name'] ?? '';
    $section = $data['section'] ?? '';
    $roll_no = $data['roll_no'] ?? '';
    $guardian_name = $data['guardian_name'] ?? '';
    $user_name = $data['user_name'] ?? '';
    $contact_number = $data['contact_number'] ?? '';
    $semester = $data['semester'] ?? '';
    $class = $data['class'] ?? '';
    $year = $data['year'] ?? '';
    $session = $data['session'] ?? '';
    $fee_duration = $data['fee_duration'] ?? '';
    $status = 'pending';
    $payment_status = 'pending';
    $remark = $data['remark'] ?? '';
    $description = $data['description'] ?? '';
    $to_date = isset($data['to_date']) && $data['to_date'] !== '' ? $data['to_date'] : null;
    $from_date = isset($data['from_date']) && $data['from_date'] !== '' ? $data['from_date'] : null;

    // Insert query
    $sql = "INSERT INTO payment_details (
        student_name, section, roll_no, guardian_name, user_name,
        contact_number, semester, class, year, session, fee_duration,
        payment_type, from_date, to_date, monthly, image_path,
        status, remark, description, payment_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    // Bind parameters
    $stmt->bind_param(
        "ssssssssssssssssssss",
        $student_name, $section, $roll_no, $guardian_name, $user_name,
        $contact_number, $semester, $class, $year, $session, $fee_duration,
        $payment_type_string, $to_date, $from_date, $monthly_string, $photo,
        $status, $remark, $description, $payment_status
    );

    // Execute query
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Insertion Failed: " . $stmt->error]);
        $stmt->close();
        exit;
    }

    // Return success response
    echo json_encode([
        "status" => "success",
        "message" => "Payment details inserted successfully",
        "photo_url" => $photo,
        "file_name" => $safeNameWithTimestamp
    ]);

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST method allowed"]);
}

$conn->close();
?>
