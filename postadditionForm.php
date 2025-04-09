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
    $distance_From_School=$_POST['distance_From_School'];
    $sms_Phone=$_POST['sms_Phone'];
    $emergency_Contact=$_POST['emergency_Contact'];
    $contact_Person=$_POST['contact_Person'];
    $contact_Relation=$_POST['contact_Relation'];
    $medical_History=$_POST['medical_History'];
    $guardian_Name=$_POST['guardian_Name'];
    $father_Name=$_POST['father_Name'];
    $mother_Name=$_POST['mother_Name'];
    $date=$_POST['date'];
    $place=$_POST['place'];
    


    // Insert into database
    $sql = "INSERT INTO additional_Form(distance_From_School,sms_Phone,emergency_Contact,contact_Person,contact_Relation,medical_History,guardian_Name,
    father_Name,mother_Name,date,place) 
            VALUES ('$father_Name','$sms_Phone','$emergency_Contact','$contact_Person','$contact_Relation','$medical_History',
            '$guardian_Name','$father_Name','$mother_Name','$date','$place')";

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

