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
    // Check if the JSON string is provided as a form-data field
    if (!isset($_POST['user'])) {
        echo json_encode(["status" => "error", "message" => "Missing JSON data"]);
        exit;
    }

    // Decode the JSON string into an associative array
    $input = json_decode($_POST['user'], true);

    // Check if necessary fields are provided in the decoded JSON
    if (!isset($input['subject_name']) || !isset($input['subject_code']) || !isset($input['class_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields in JSON"]);
        exit;
    }

    // Clean and assign variables from the decoded JSON
    $subject_name = $input['subject_name'];
    $subject_code = $input['subject_code'];
    $class_id = intval($input['class_id']);

    // Insert data into subject_master table
    $sql = "INSERT INTO subject_master (subject_name, subject_code, class_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssi", $subject_name, $subject_code, $class_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Subject added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
