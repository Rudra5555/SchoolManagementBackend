<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// DB connection
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Read and decode JSON
$data = json_decode(file_get_contents("php://input"), true);

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($data === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
        exit;
    }

    $distance_From_School = $data['distanceFromSchool'] ?? '';
$sms_Phone = $data['smsPhone'] ?? '';
$emergency_Contact = $data['emergencyContact'] ?? '';
$contact_Person = $data['contactPerson'] ?? '';
$contact_Relation = $data['contactRelation'] ?? '';
$medical_History = $data['medicalHistory'] ?? '';
$guardian_Name = $data['guardianName'] ?? '';
$father_Name = $data['fatherName'] ?? '';
$mother_Name = $data['motherName'] ?? '';
$date = $data['date'] ?? '';
$place = $data['place'] ?? '';


    // Prepare SQL query
    $sql = "INSERT INTO additional_Form (
        distance_From_School, sms_Phone, emergency_Contact, contact_Person, contact_Relation,
        medical_History, guardian_Name, father_Name, mother_Name, date, place
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "sssssssssss",
        $distance_From_School, $sms_Phone, $emergency_Contact, $contact_Person, $contact_Relation,
        $medical_History, $guardian_Name, $father_Name, $mother_Name, $date, $place
    );

    try {
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Additional information submitted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Exception: " . $e->getMessage()]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
