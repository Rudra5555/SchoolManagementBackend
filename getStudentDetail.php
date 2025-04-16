<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Include your JWT helper functions
require 'jwt_utils.php'; // yeh aapki generate_jwt aur is_jwt_valid function wali file hai

// Get Authorization header
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

// Validate token
if (!is_jwt_valid($jwt)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid or expired token"]);
    exit;
}

// Token valid, now connect to DB
$conn = new mysqli("localhost", "root", "1234", "admission");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get student_id if provided
$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;

// Query based on ID
$sql = $id ? "SELECT * FROM student_Details WHERE student_id = '$id'" : "SELECT * FROM student_Details";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['student_id'] = (int)$row['student_id'];
        $row['siblings_Count'] = $row['siblings_Count'] !== null ? (int)$row['siblings_Count'] : null;
        $row['sibling_Age'] = $row['sibling_Age'] !== null ? (int)$row['sibling_Age'] : null;

        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

$conn->close();
?>
