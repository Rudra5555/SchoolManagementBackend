<?php
require_once 'jwt_utils.php'; // Make sure this file has validate_jwt() function

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

// Fetch data
$sql = "SELECT * FROM payment_details ORDER BY user_id";
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
