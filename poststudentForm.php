<?php
// Enablror reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json"); // Set content type to JSON

// Database connection
$servername = "localhost";
$username = "root";
$password = "1234"; // Default for XAMPP
$dbname = "admission";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $surname=$_POST['surname'];
    $name=$_POST['name'];
    $dob=$_POST['dob'];
    $place_of_Birth=$_POST['place_of_Birth'];
    $sex=$_POST['sex'];
    $address=$_POST['address'];
    $pincode=$_POST['pincode'];
    $phone=$_POST['phone'];
    $blood_Group=$_POST['blood_Group'];
    $religion=$_POST['religion'];
    $nationality=$_POST['nationality'];
    $second_Language=$_POST['second_Language'];
    $mother_Tongue=$_POST['mother_Tongue'];
    $present_Class=$_POST['present_Class'];
    $admission_Class=$_POST['admission_Class'];
    $last_School=$_POST['last_School'];
    $siblings_Count=$_POST['siblings_Count'];
    $sibling_Name=$_POST['sibling_Name'];
    $sibling_Age=$_POST['sibling_Age'];
    $sibling_Sex=$_POST['sibling_Sex'];
    $sibling_Class=$_POST['sibling_Class'];
    $sibling_School=$_POST['sibling_School'];
    $place_Of_Origin=$_POST['place_Of_Origin'];
    $category=$_POST['category'];
    $photo=$_POST['photo'];

    // Insert into database
    $sql = "INSERT INTO student_Form(surname,name,dob,place_of_Birth,sex,address,pincode,phone,blood_Group,religion,nationality,second_Language,mother_Tongue,present_Class,admission_Class,last_School,siblings_Count,sibling_Name,sibling_Age,sibling_Sex,sibling_Class,sibling_School,place_Of_Origin,category,photo) 
            VALUES ('$surname','$name','$dob','$place_of_Birth','$sex','$address','$pincode','$phone','$blood_Group','$religion','$nationality','$second_Language','$mother_Tongue','$present_Class','$admission_Class','$last_School','$siblings_Count','$sibling_Name','$sibling_Age','$sibling_Sex','$sibling_Class','$sibling_School','$place_Of_Origin','$category','$photo')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Registration successful.  "]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . "This email id has already been registered"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>

