<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS & headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

// Include JWT utilities
require 'jwt_utils.php'; // Ensure the path is correct

// Get the Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
    exit;
}

// Extract the Bearer token
$authHeader = $headers['Authorization'];
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token format"]);
    exit;
}

// Validate the JWT token
if (!is_jwt_valid($jwt)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid or expired token"]);
    exit;
}

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Optional: Get student_id if provided in query string
$studentId = isset($_GET['student_id']) ? (int)$conn->real_escape_string($_GET['student_id']) : null;

// Prepare SQL query
if ($studentId) {
    $stmt = $conn->prepare("SELECT * FROM additional_Details WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);
} else {
    $stmt = $conn->prepare("SELECT * FROM additional_Details");
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();
$data = [];

// Fetch and store data
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (isset($row['student_id'])) {
            $row['student_id'] = (int)$row['student_id'];
        }
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

// Close resources
$stmt->close();
$conn->close();
?>
