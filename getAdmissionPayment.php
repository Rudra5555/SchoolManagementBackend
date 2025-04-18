<?php
require_once 'jwt_utils.php'; // Make sure this file has is_jwt_valid() function

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Connection failed"]);
    exit;
}

// Get Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
    exit;
}


$authHeader = $headers['Authorization'];
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token format"]);
    exit;
}


if (!is_jwt_valid($jwt)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid or expired token"]);
    exit;
}


// Get filter parameters
$userId = isset($_GET['user_id']) ? $conn->real_escape_string(trim($_GET['user_id'])) : '';
$userName = isset($_GET['user_name']) ? $conn->real_escape_string(trim($_GET['user_name'])) : '';
$createdAt = isset($_GET['created_at']) ? $conn->real_escape_string(trim($_GET['created_at'])) : '';

// Build WHERE clause dynamically
$whereClauses = [];
if ($userId !== '') {
    $whereClauses[] = "user_id = '$userId'";
}
if ($userName !== '') {
    $whereClauses[] = "user_name LIKE '%$userName%'";
}
if ($createdAt !== '') {
    $whereClauses[] = "DATE(created_at) = '$createdAt'";
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

$sql = "SELECT * FROM payment_details $whereSql ORDER BY user_id";
$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "success", "data" => []]);
}

$conn->close();
?>
