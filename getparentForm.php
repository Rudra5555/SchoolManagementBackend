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

// Check for optional ID parameter
$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;

$sql = $id
    ? "SELECT * FROM parent_Form WHERE id = '$id'"
    : "SELECT * FROM parent_Form";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Optional: typecasting fields if needed
        $row['id'] = (int)$row['id'];
        $row['father_Age'] = (int)$row['father_Age'];
        $row['father_Annual_Income'] = (int)$row['father_Annual_Income'];
        $row['mother_Age'] = (int)$row['mother_Age'];
        $row['mother_Annual_Income'] = (int)$row['mother_Annual_Income'];
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]); // empty array
}

$conn->close();
?>
