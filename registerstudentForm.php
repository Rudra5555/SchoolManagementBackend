<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// DB config
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Read and decode JSON
$data = json_decode(file_get_contents("php://input"), true);

// Check for valid JSON
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($data === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
        exit;
    }

    // Extract data with default fallbacks
    $surname = $data['surname'] ?? '';
    $name = $data['name'] ?? '';
    $dob = $data['dob'] ?? null;
    $place_Of_Birth = $data['placeOfBirth'] ?? '';
    $sex = $data['sex'] ?? '';
    $address = $data['address'] ?? '';
    $pincode = isset($data['pincode']) && $data['pincode'] !== '' ? (int)$data['pincode'] : null;
    $phone = $data['phone'] ?? '';
    $blood_Group = $data['bloodGroup'] ?? '';
    $religion = $data['religion'] ?? '';
    $nationality = $data['nationality'] ?? '';
    $second_Language = $data['secondLanguage'] ?? '';
    $mother_Tongue = $data['motherTongue'] ?? '';
    $present_Class = $data['presentClass'] ?? '';
    $admission_Class = $data['admissionClass'] ?? '';
    $last_School = $data['lastSchool'] ?? '';
    $siblings_Count = isset($data['siblingsCount']) && $data['siblingsCount'] !== '' ? (int)$data['siblingsCount'] : null;
    $sibling_Name = $data['siblingName'] ?? '';
    $sibling_Age = isset($data['siblingAge']) && $data['siblingAge'] !== '' ? (int)$data['siblingAge'] : null;
    $sibling_Sex = $data['siblingSex'] ?? '';
    $sibling_Class = $data['siblingClass'] ?? '';
    $sibling_School = $data['siblingSchool'] ?? '';
    $place_Of_Origin = $data['placeOfOrigin'] ?? '';
    $category = $data['category'] ?? '';
    $photo = $data['photo'] ?? '';
   
    // Prepare SQL
    $sql = "INSERT INTO student_Form (
        surname, name, dob, place_Of_Birth, sex, address, pincode, phone,
        blood_Group, religion, nationality, second_Language, mother_Tongue,
        present_Class, admission_Class, last_School, siblings_Count, sibling_Name,
        sibling_Age, sibling_Sex, sibling_Class, sibling_School, place_Of_Origin,
        category, photo
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssissssssssssisssssss", // exactly 25 type characters
        $surname, $name, $dob, $place_Of_Birth, $sex, $address, $pincode, $phone,
        $blood_Group, $religion, $nationality, $second_Language, $mother_Tongue,
        $present_Class, $admission_Class, $last_School, $siblings_Count, $sibling_Name,
        $sibling_Age, $sibling_Sex, $sibling_Class, $sibling_School, $place_Of_Origin,
        $category, $photo
    );
    

    try {
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Student registered successfully"]);
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


