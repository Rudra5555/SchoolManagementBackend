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

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]));
}

// Get the POSTed JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate
if (empty($data['category_name']) || empty($data['amount'])) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "Category name and amount are required"]);
    exit;
}

$category_name = $data['category_name'];
$amount = $data['amount'];

// Insert into database
$stmt = $conn->prepare("INSERT INTO fees_category (category_name, amount) VALUES (?, ?)");
$stmt->bind_param("sd", $category_name, $amount);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Fees category added successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => false, "message" => "Failed to add fees category"]);
}

$stmt->close();
$conn->close();
?>
