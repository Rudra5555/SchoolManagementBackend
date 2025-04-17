<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

require_once 'jwt_utils.php';

// Get the Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: No token provided"]);
    exit;
}

// Extract Bearer token
$authHeader = $headers['Authorization'];
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid token format"]);
    exit;
}

// Validate the JWT
if (!is_jwt_valid($jwt)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized: Invalid or expired token"]);
    exit;
}

// Step 2: Connect to Database
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Step 3: Handle and Validate `id` Parameter
$studentId = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if (trim($id) === "" && $id !== "") {
        echo json_encode(["message" => "record not found"]);
        exit;
    } elseif ($id === "") {
        // Blank = fetch all
    } elseif (preg_match('/^\d+$/', $id)) {
        $studentId = (int)$id;
    } else {
        echo json_encode(["message" => "record not found"]);
        exit;
    }
}

// Step 4: Prepare SQL Query
$data = [];

if ($studentId !== null) {
    $sql = "
        SELECT 
            s.student_id, s.surname, s.name, s.dob, s.place_Of_Birth, s.sex, s.address,
            s.pincode, s.phone, s.blood_Group, s.religion, s.nationality, s.second_Language,
            s.mother_Tongue, s.present_Class, s.admission_Class, s.last_School,
            s.siblings_Count, s.sibling_Name, s.sibling_Age, s.sibling_Sex,
            s.sibling_Class, s.sibling_School, s.place_Of_Origin, s.category, s.photo,

            p.father_Name, p.father_Age, p.father_Nationality, p.father_Education, 
            p.father_Occupation, p.father_Office_Address, p.father_Designation, 
            p.father_Annual_Income, p.father_Aadhar, p.father_Pincode, p.father_Phone, p.father_Photo,

            p.mother_Name, p.mother_Age, p.mother_Nationality, p.mother_Education,
            p.mother_Occupation, p.mother_Office_Address, p.mother_Designation, 
            p.mother_Annual_Income, p.mother_Aadhar, p.mother_Pincode, p.mother_Phone, p.mother_Photo,

            a.distance_From_School, a.sms_Phone, a.emergency_Contact, a.contact_Person, a.contact_Relation,
            a.medical_History, a.guardian_Name, a.date, a.place

        FROM student_details s
        INNER JOIN parent_details p ON s.student_id = p.student_id
        INNER JOIN additional_details a ON s.student_id = a.student_id
        WHERE s.student_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
} else {
    $sql = "
        SELECT 
            s.student_id, s.surname, s.name, s.dob, s.place_Of_Birth, s.sex, s.address,
            s.pincode, s.phone, s.blood_Group, s.religion, s.nationality, s.second_Language,
            s.mother_Tongue, s.present_Class, s.admission_Class, s.last_School,
            s.siblings_Count, s.sibling_Name, s.sibling_Age, s.sibling_Sex,
            s.sibling_Class, s.sibling_School, s.place_Of_Origin, s.category, s.photo,

            p.father_Name, p.father_Age, p.father_Nationality, p.father_Education, 
            p.father_Occupation, p.father_Office_Address, p.father_Designation, 
            p.father_Annual_Income, p.father_Aadhar, p.father_Pincode, p.father_Phone, p.father_Photo,

            p.mother_Name, p.mother_Age, p.mother_Nationality, p.mother_Education,
            p.mother_Occupation, p.mother_Office_Address, p.mother_Designation, 
            p.mother_Annual_Income, p.mother_Aadhar, p.mother_Pincode, p.mother_Phone, p.mother_Photo,

            a.distance_From_School, a.sms_Phone, a.emergency_Contact, a.contact_Person, a.contact_Relation,
            a.medical_History, a.guardian_Name, a.date, a.place

        FROM student_details s
        INNER JOIN parent_details p ON s.student_id = p.student_id
        INNER JOIN additional_details a ON s.student_id = a.student_id";
    
    $stmt = $conn->prepare($sql);
}

// Step 5: Execute and Output
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode(["message" => "record not found"]);
}

$stmt->close();
$conn->close();
?>
