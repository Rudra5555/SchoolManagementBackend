<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS & headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

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

// Optional: check for ID in query string
$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;

$sql = $id
    ? "SELECT * FROM additional_Form WHERE id = '$id'"
    : "SELECT * FROM additional_Form";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Optional: typecast fields if needed
        $row['id'] = (int)$row['id'];
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]); // No records found
}

$conn->close();
?>
