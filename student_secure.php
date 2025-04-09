<?php
header("Content-Type: application/json");
require 'jwt_utils.php';
require 'db.php';

// Function to get Authorization header properly
function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

$authHeader = getAuthorizationHeader();

if (!$authHeader) {
    echo json_encode(["status" => "error", "message" => "Token not provided"]);
    exit;
}

list($type, $token) = explode(" ", $authHeader, 2);

if (strcasecmp($type, 'Bearer') != 0) {
    echo json_encode(["status" => "error", "message" => "Invalid token type"]);
    exit;
}

$payload = is_jwt_valid($token);

if (!$payload) {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
    exit;
}

// Token is valid, fetch data
$sql = "SELECT * FROM student_Form";
$result = dbQuery($sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
