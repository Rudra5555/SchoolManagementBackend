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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if json_data is passed in form-data
    if (!isset($_POST['user'])) {
        echo json_encode(["status" => "error", "message" => "Missing json_data"]);
        exit;
    }

    // Decode the JSON string from form-data
    $input = json_decode($_POST['user'], true);

    // Check if necessary fields are provided in the decoded JSON
    if (!isset($input['section_name']) || !isset($input['class_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing section_name or class_id in JSON"]);
        exit;
    }

    // Extract the variables from the JSON
    $section_name = $input['section_name'];
    $class_id = intval($input['class_id']);

    // Insert section into section_master table
    $sql = "INSERT INTO section_master (section_name, class_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $section_name, $class_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Section added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add section"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
