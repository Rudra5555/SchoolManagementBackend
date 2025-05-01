<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Content-Type: application/json");

require_once 'jwt_utils.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admission";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "DB Connection failed"]);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);


// // Retrieving GET parameters and sanitizing inputs
$userId = isset($_GET['user_id']) ? $conn->real_escape_string(trim($_GET['user_id'])) : '';
$userName = isset($_GET['user_name']) ? $conn->real_escape_string(trim($_GET['user_name'])) : '';
$status = isset($_GET['status']) ? $conn->real_escape_string(trim($_GET['status'])) : '';
$remark = isset($_GET['remark']) ? $conn->real_escape_string(trim($_GET['remark'])) : '';
$feeDuration = isset($_GET['fee_duration']) ? $conn->real_escape_string(trim($_GET['fee_duration'])) : '';

// Get and clean inputs
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$class = $_GET['class'] ?? '';
$monthly = $_GET['monthly'] ?? '';
$payment_type = $_GET['payment_type'] ?? '';


// Initialize WHERE clause parts
$whereClauses = [];

if (!empty($from_date)) {
    $whereClauses[] = "from_date >= '$from_date'";
}

if (!empty($to_date)) {
    $whereClauses[] = "to_date <= '$to_date'";
}

if (!empty($class)) {
    $whereClauses[] = "class = '$class'";
}

if ($userId !== '') $whereClauses[] = "user_id = '$userId'";
if ($userName !== '') $whereClauses[] = "user_name LIKE '%$userName%'";
if ($status !== '') $whereClauses[] = "status = '$status'";
if ($remark !== '') $whereClauses[] = "remark LIKE '%$remark%'";
if ($feeDuration !== '') $whereClauses[] = "fee_duration = '$feeDuration'";



// Add condition for date range
if (!empty($from_date) && !empty($to_date)) {
    $whereClauses[] = "(to_date >= '$from_date' AND from_date <= '$to_date')";
}

// Monthly
if (!empty($monthly)) {
    $monthly_array = explode(',', $monthly);
    $monthly_condition = array_map(fn($m) => "monthly LIKE '%" . trim($m) . "%'", $monthly_array);
    $whereClauses[] = "(" . implode(" OR ", $monthly_condition) . ")";
}

// Payment Type
if (!empty($payment_type)) {
    $payment_type_array = explode(',', $payment_type);
    $payment_type_condition = array_map(fn($p) => "payment_type LIKE '%" . trim($p) . "%'", $payment_type_array);
    $whereClauses[] = "(" . implode(" OR ", $payment_type_condition) . ")";
}
$whereSql = count($whereClauses) > 0 ? "WHERE " . implode(" AND ", $whereClauses) : "";
 
//  Date range filter (IMPORTANT)
if (!empty($from_date) && !empty($to_date)) {
    $whereClauses[] = "(to_date <= '$to_date' AND from_date >= '$from_date')";
}

$whereClause = "";
if (!empty($from_date)) {
    $whereClause = "WHERE created_at BETWEEN '$from_date' AND '$to_date'";
}

 // Final SQL query
 $sql = "SELECT * FROM payment_details $whereSql ORDER BY user_id";
 
// Prepare statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "SQL query prepare failed"]);
    exit;
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Convert comma-separated strings to arrays for frontend
        if (isset($row['payment_type']) && is_string($row['payment_type'])) {
            $row['payment_type'] = explode(', ', $row['payment_type']);
        }
        if (isset($row['monthly']) && is_string($row['monthly'])) {
            $row['monthly'] = explode(', ', $row['monthly']);
        }
        $data[] = $row;
    }

    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => "Query failed"]);
}

// Close DB connection
$stmt->close();
$conn->close();
?>
