<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

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
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB Connection failed: " . $conn->connect_error]));
}

// Fetch data
$sql = "SELECT * FROM Filled_details";
$result = $conn->query($sql);

$students = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $students]);
} else {
    echo json_encode(["status" => "success", "data" => [], "message" => "No records found"]);
}

$conn->close();
?>
