<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";


// Include your JWT helper functions
require 'jwt_utils.php'; 

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

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // SQL query to fetch subject and class data without section information
    $sql = "SELECT subject_master.subject_id, subject_master.subject_name, subject_master.subject_code, 
                   class_master.class_id, class_master.class_name
            FROM subject_master
            JOIN class_master ON subject_master.class_id = class_master.class_id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $subjects]);
    } else {
        echo json_encode(["status" => "success", "data" => []]); // No subjects found
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only GET requests allowed"]);
}

$conn->close();
?>
