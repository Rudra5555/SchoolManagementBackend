<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers
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
    die(json_encode(["status" => "error", "message" => "DB Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check both keys
    if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing or empty 'student_id'"]);
        exit;
    }

    if (!isset($_POST['user']) || empty($_POST['user'])) {
        echo json_encode(["status" => "error", "message" => "Missing or empty 'user'"]);
        exit;
    }

    $student_id = $_POST['student_id'];
    $data = json_decode($_POST['user'], true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON in 'user'"]);
        exit;
    }

    // Extract fields from user JSON
    $distance_From_School = $data['distanceFromSchool'] ?? null;
    $sms_Phone            = $data['smsPhone'] ?? null;
    $emergency_Contact    = $data['emergencyContact'] ?? null;
    $contact_Person       = $data['contactPerson'] ?? null;
    $contact_Relation     = $data['contactRelation'] ?? null;
    $medical_History      = $data['medicalHistory'] ?? null;
    $guardian_Name        = $data['guardianName'] ?? null;
    $father_Name          = $data['fatherName'] ?? null;
    $mother_Name          = $data['motherName'] ?? null;
    $date                 = $data['date'] ?? null;
    $place                = $data['place'] ?? null;

    // Insert query
    $sql = "INSERT INTO additional_details (
        student_id, distance_From_School, sms_Phone, emergency_Contact,
        contact_Person, contact_Relation, medical_History, guardian_Name,
        father_Name, mother_Name, date, place
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssssssss",
        $student_id, $distance_From_School, $sms_Phone, $emergency_Contact,
        $contact_Person, $contact_Relation, $medical_History, $guardian_Name,
        $father_Name, $mother_Name, $date, $place
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Additional details saved"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
