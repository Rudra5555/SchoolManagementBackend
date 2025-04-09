<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Connect to DB
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Read JSON input
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($data === null) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
        exit;
    }

    // Use camelCase keys (matching your JSON)
    $father_Name = $data['fatherName'] ?? '';
    $father_Age = $data['fatherAge'] ?? '';
    $father_Nationality = $data['fatherNationality'] ?? '';
    $father_Education = $data['fatherEducation'] ?? '';
    $father_Occupation = $data['fatherOccupation'] ?? '';
    $father_Office_Address = $data['fatherOfficeAddress'] ?? '';
    $father_Designation = $data['fatherDesignation'] ?? '';
    $father_Annual_Income = $data['fatherAnnualIncome'] ?? '';
    $father_Aadhar = $data['fatherAadhar'] ?? '';
    $father_Pincode = $data['fatherPincode'] ?? '';
    $father_Phone = $data['fatherPhone'] ?? '';
    
    $mother_Name = $data['motherName'] ?? '';
    $mother_Age = $data['motherAge'] ?? '';
    $mother_Nationality = $data['motherNationality'] ?? '';
    $mother_Education = $data['motherEducation'] ?? '';
    $mother_Occupation = $data['motherOccupation'] ?? '';
    $mother_Office_Address = $data['motherOfficeAddress'] ?? '';
    $mother_Designation = $data['motherDesignation'] ?? '';
    $mother_Annual_Income = $data['motherAnnualIncome'] ?? '';
    $mother_Aadhar = $data['motherAadhar'] ?? '';
    $mother_Pincode = $data['motherPincode'] ?? '';
    $mother_Phone = $data['motherPhone'] ?? '';

    // Prepare SQL Insert
    $sql = "INSERT INTO parent_Form (
        father_Name, father_Age, father_Nationality, father_Education, father_Occupation, father_Office_Address,
        father_Designation, father_Annual_Income, father_Aadhar, father_Pincode, father_Phone,
        mother_Name, mother_Age, mother_Nationality, mother_Education, mother_Occupation,
        mother_Office_Address, mother_Designation, mother_Annual_Income, mother_Aadhar,
        mother_Pincode, mother_Phone
    ) VALUES (
        '$father_Name', '$father_Age', '$father_Nationality', '$father_Education', '$father_Occupation',
        '$father_Office_Address', '$father_Designation', '$father_Annual_Income', '$father_Aadhar',
        '$father_Pincode', '$father_Phone', '$mother_Name', '$mother_Age', '$mother_Nationality',
        '$mother_Education', '$mother_Occupation', '$mother_Office_Address', '$mother_Designation',
        '$mother_Annual_Income', '$mother_Aadhar', '$mother_Pincode', '$mother_Phone'
    )";

    try {
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Parent data saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Exception: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
}

$conn->close();
?>
