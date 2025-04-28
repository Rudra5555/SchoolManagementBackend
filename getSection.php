<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


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

$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // SQL query to fetch class_id, class_name, section_id, section_name from both tables with INNER JOIN
    $sql = "SELECT class_master.class_id, class_master.class_name, section_master.section_id, section_master.section_name
            FROM class_master
            INNER JOIN section_master ON class_master.class_id = section_master.class_id
            ORDER BY class_master.class_id, section_master.section_id";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $classes_and_sections = [];
        while ($row = $result->fetch_assoc()) {
            $classes_and_sections[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $classes_and_sections]);
    } else {
        echo json_encode(["status" => "success", "data" => []]); // No data found
    }
} else {
    echo json_encode(["status" => "error", "message" => "Only GET requests allowed"]);
}

$conn->close();
?>
