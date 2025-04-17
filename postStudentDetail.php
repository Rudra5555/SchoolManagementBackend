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
    if (!isset($_POST['user']) || !isset($_FILES['photo'])) {
        echo json_encode(["status" => "error", "message" => "Missing form data or file"]);
        exit;
    }
    $data = json_decode($_POST['user'], true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
        exit;
    }

    // Extract fields from JSON
    $surname = $data['surname'] ?? null;
    $name = $data['name'] ?? null;
    $dob = $data['dob'] ?? null;
    $place_Of_Birth = $data['placeOfBirth'] ?? null;  // Check the key name here
    $sex = $data['sex'] ?? null;
    $address = $data['address'] ?? null;
    $pincode = $data['pincode'] ?? null;
    $phone = $data['phone'] ?? null;
    $blood_Group = $data['bloodGroup'] ?? null; // Correct the key name here
    $religion = $data['religion'] ?? null;
    $nationality = $data['nationality'] ?? null;
    $second_Language = $data['secondLanguage'] ?? null; // Correct the key name here
    $mother_Tongue = $data['motherTongue'] ?? null;  // Correct the key name here
    $present_Class = $data['presentClass'] ?? null;  // Correct the key name here
    $admission_Class = $data['admissionClass'] ?? null;  // Correct the key name here
    $last_School = $data['lastSchool'] ?? null;
    $siblings_Count = $data['siblingsCount'] ?? null;  // Correct the key name here
    $sibling_Name = $data['siblingName'] ?? null;  // Correct the key name here
    $sibling_Age = $data['siblingAge'] ?? null;  // Correct the key name here
    $sibling_Sex = $data['siblingSex'] ?? null;  // Correct the key name here
    $sibling_Class = $data['siblingClass'] ?? null;  // Correct the key name here
    $sibling_School = $data['siblingSchool'] ?? null;  // Correct the key name here
    $place_Of_Origin = $data['placeOfOrigin'] ?? null;  // Correct the key name here
    $category = $data['category'] ?? null;

 // Handle photo upload
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$tmpName = $_FILES['photo']['tmp_name'];
$originalName = basename($_FILES['photo']['name']);
$fileType = mime_content_type($tmpName);
$fileSize = $_FILES['photo']['size'];

$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(["status" => "error", "message" => "Only JPG and PNG images allowed"]);
    exit;
}

if ($fileSize > $maxSize) {
    echo json_encode(["status" => "error", "message" => "Image size exceeds 2MB"]);
    exit;
}

$timestamp = time();
$fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
$fileBaseName = pathinfo($originalName, PATHINFO_FILENAME);
$safeNameWithTimestamp = $fileBaseName . "_" . $timestamp . "." . $fileExtension;
$destination = $uploadDir . $safeNameWithTimestamp;

if (!move_uploaded_file($tmpName, $destination)) {
    echo json_encode(["status" => "error", "message" => "Image upload failed"]);
    exit;
}

$photo = "http://localhost/schoolManagementBackend/SchoolManagementBackend/" . $destination;


    // Prepare SQL insert
    $sql = "INSERT INTO student_details (
        surname, name, dob, place_Of_Birth, sex, address, pincode, phone,
        blood_Group, religion, nationality, second_Language, mother_Tongue,
        present_Class, admission_Class, last_School, siblings_Count, sibling_Name,
        sibling_Age, sibling_Sex, sibling_Class, sibling_School, place_Of_Origin,
        category, photo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssissssssssssisssssss",
        $surname, $name, $dob, $place_Of_Birth, $sex, $address, $pincode, $phone,
        $blood_Group, $religion, $nationality, $second_Language, $mother_Tongue,
        $present_Class, $admission_Class, $last_School, $siblings_Count, $sibling_Name,
        $sibling_Age, $sibling_Sex, $sibling_Class, $sibling_School, $place_Of_Origin,
        $category, $photo
    );

   if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Student data inserted successfully",
        "photo_url" => $photo,
        "file_name" => $safeNameWithTimestamp
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
}

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed"]);
}

$conn->close();
?>
