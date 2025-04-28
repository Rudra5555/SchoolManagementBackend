<?php
require_once 'jwt_utils.php'; // Make sure this file has is_jwt_valid() function

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
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

// Get JSON body data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->user_id) || !isset($data->status)) {
    echo json_encode(["status" => "error", "message" => "User ID and status are required"]);
    exit;
}

$userId = $conn->real_escape_string(trim($data->user_id));
$status = $conn->real_escape_string(trim($data->status));
$remark = isset($data->remark) ? $conn->real_escape_string(trim($data->remark)) : 'No remark provided'; // Default remark if not provided

// SQL query to update payment status and remark
$sql = "UPDATE payment_details SET status = ?, remark = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $status, $remark, $userId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Payment status updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update payment status"]);
}

$stmt->close();
$conn->close();
?>
