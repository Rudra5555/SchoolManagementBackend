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
require 'jwt_utils.php'; // <-- make sure this path is correct

// Get the Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
    exit;
}

// Extract Bearer token
$authHeader = $headers['Authorization'];
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token format"]);
    exit;
}

// Validate the JWT
if (!is_jwt_valid($jwt)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid or expired token"]);
    exit;
}

// DB connection
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Check for optional parent_id parameter
$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;

// Build SQL query
$sql = $id
    ? "SELECT * FROM parent_Details WHERE parent_id = '$id'"
    : "SELECT * FROM parent_Details";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['student_id'] = (int)$row['student_id'];
        $row['father_Age'] = (int)$row['father_Age'];
        $row['father_Annual_Income'] = (int)$row['father_Annual_Income'];
        $row['mother_Age'] = (int)$row['mother_Age'];
        $row['mother_Annual_Income'] = (int)$row['mother_Annual_Income'];
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

$conn->close();
?>
