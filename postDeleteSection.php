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
    // Directly access the form-data values
    if (!isset($_POST['class_id']) || !isset($_POST['section_id'])) {
        echo json_encode(["status" => "error", "message" => "Both class_id and section_id are required"]);
        exit;
    }

    // Get the class_id and section_id from form-data
    $class_id = intval($_POST['class_id']);
    $section_id = intval($_POST['section_id']);

    // SQL query to delete section by both class_id and section_id
    $sql = "DELETE FROM section_master WHERE class_id = ? AND section_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    // Bind both parameters to the query
    $stmt->bind_param("ii", $class_id, $section_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Section deleted successfully for class_id: $class_id and section_id: $section_id"]);
        } else {
            echo json_encode(["status" => "error", "message" => "No section found with the given class_id and section_id"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
