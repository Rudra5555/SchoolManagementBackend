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
    if (!isset($_POST['user'])) {
        echo json_encode(["status" => "error", "message" => "Missing form data"]);
        exit;
    }

    $data = json_decode($_POST['user'], true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
        exit;
    }

    // Extract fields from JSON
    $admission_granted_in_class = $data['admission_granted_in_class'] ?? null;
    $sec = $data['sec'] ?? null;
    $roll_no = $data['roll_no'] ?? null;
    $admission_no = $data['admission_no'] ?? null;
    $remarks = $data['remarks'] ?? null;

    // Prepare SQL insert
    $sql = "INSERT INTO Filled_details (
        admission_granted_in_class, sec, roll_no, admission_no, remarks
    ) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssss", $admission_granted_in_class, $sec, $roll_no, $admission_no, $remarks);

    // Execute missing tha
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Data inserted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}

$conn->close();
?>
