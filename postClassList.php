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
    if (!isset($_POST['user'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields or files"]);
        exit;
    }

    // Decode the JSON data from 'user'
    $data = json_decode($_POST['user'], true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON in 'user' field"]);
        exit;
    }

    // Helper function to clean the value
    function clean($value, $isInt = false) {
        $value = trim($value, "\"");
        return ($isInt && $value === '') ? NULL : $value;
    }

    // Clean the 'class_Name' value
    $class_Name = clean($data['class_Name'] ?? '');

    // Check if 'class_Name' is provided
    if (empty($class_Name)) {
        echo json_encode(["status" => "error", "message" => "Class name is required"]);
        exit;
    }

    // Insert into database
    $sql = "INSERT INTO class_master (class_Name) VALUES (?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    // Bind the parameter and check for errors
    if (!$stmt->bind_param("s", $class_Name)) {
        echo json_encode(["status" => "error", "message" => "Binding parameters failed"]);
        exit;
    }

    // Execute the statement and check for errors
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
        exit;
    }

    // Success response
    echo json_encode(["status" => "success", "message" => "Class added successfully"]);

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
