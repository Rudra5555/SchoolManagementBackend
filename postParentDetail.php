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
    if (!isset($_POST['student_id']) || !isset($_POST['user']) || !isset($_FILES['father_Photo']) || !isset($_FILES['mother_Photo'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields or files"]);
        exit;
    }

    $student_id = $_POST['student_id'];
    $data = json_decode($_POST['user'], true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON in 'user' field"]);
        exit;
    }

    // Helper function
    function clean($value, $isInt = false) {
        $value = trim($value, "\"");
        return ($isInt && $value === '') ? NULL : $value;
    }

    // Extract data
    $father_Name = clean($data['fatherName'] ?? '');
    $father_Age = clean($data['fatherAge'] ?? '', true);
    $father_Nationality = clean($data['fatherNationality'] ?? '');
    $father_Education = clean($data['fatherEducation'] ?? '');
    $father_Occupation = clean($data['fatherOccupation'] ?? '');
    $father_Office_Address = clean($data['fatherOfficeAddress'] ?? '');
    $father_Designation = clean($data['fatherDesignation'] ?? '');
    $father_Annual_Income = clean($data['fatherAnnualIncome'] ?? '', true);
    $father_Aadhar = clean($data['fatherAadhar'] ?? '');
    $father_Pincode = clean($data['fatherPincode'] ?? '');
    $father_Phone = clean($data['fatherPhone'] ?? '');

    $mother_Name = clean($data['motherName'] ?? '');
    $mother_Age = clean($data['motherAge'] ?? '', true);
    $mother_Nationality = clean($data['motherNationality'] ?? '');
    $mother_Education = clean($data['motherEducation'] ?? '');
    $mother_Occupation = clean($data['motherOccupation'] ?? '');
    $mother_Office_Address = clean($data['motherOfficeAddress'] ?? '');
    $mother_Designation = clean($data['motherDesignation'] ?? '');
    $mother_Annual_Income = clean($data['motherAnnualIncome'] ?? '', true);
    $mother_Aadhar = clean($data['motherAadhar'] ?? '');
    $mother_Pincode = clean($data['motherPincode'] ?? '');
    $mother_Phone = clean($data['motherPhone'] ?? '');

    // File Upload
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    function uploadPhoto($file, $prefix) {
        global $uploadDir;
        $timestamp = time();
        $fileName = $prefix . "_" . $timestamp . "_" . basename($file["name"]);
        $targetPath = $uploadDir . $fileName;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array(mime_content_type($file['tmp_name']), $allowedTypes)) {
            return [false, "Only JPG and PNG images allowed"];
        }

        if ($file['size'] > $maxSize) {
            return [false, "File size exceeds 2MB"];
        }

        if (move_uploaded_file($file["tmp_name"], $targetPath)) {
            return [true, "http://localhost/schoolManagementBackend/SchoolManagementBackend/" . $targetPath];
        }

        return [false, "Failed to upload image"];
    }

    list($successFather, $father_photo_url) = uploadPhoto($_FILES['father_Photo'], "father");
    if (!$successFather) {
        echo json_encode(["status" => "error", "message" => $father_photo_url]);
        exit;
    }

    list($successMother, $mother_photo_url) = uploadPhoto($_FILES['mother_Photo'], "mother");
    if (!$successMother) {
        echo json_encode(["status" => "error", "message" => $mother_photo_url]);
        exit;
    }

    // Insert
    $sql = "INSERT INTO parent_details (
        student_id, father_Name, father_Age, father_Nationality, father_Education, father_Occupation, 
        father_Office_Address, father_Designation, father_Annual_Income, father_Aadhar, father_Pincode, father_Phone, 
        mother_Name, mother_Age, mother_Nationality, mother_Education, mother_Occupation, 
        mother_Office_Address, mother_Designation, mother_Annual_Income, mother_Aadhar, mother_Pincode, mother_Phone, 
        father_Photo, mother_Photo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("issssssssssssssssssssssss", 
        $student_id, $father_Name, $father_Age, $father_Nationality, $father_Education, $father_Occupation, 
        $father_Office_Address, $father_Designation, $father_Annual_Income, $father_Aadhar, $father_Pincode, $father_Phone,
        $mother_Name, $mother_Age, $mother_Nationality, $mother_Education, $mother_Occupation, 
        $mother_Office_Address, $mother_Designation, $mother_Annual_Income, $mother_Aadhar, $mother_Pincode, $mother_Phone,
        $father_photo_url, $mother_photo_url
    );

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Parent details inserted successfully",
            "father_photo" => $father_photo_url,
            "mother_photo" => $mother_photo_url
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
