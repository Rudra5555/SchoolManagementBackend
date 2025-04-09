<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "1234", "admission");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : null;
$sql = $id ? "SELECT * FROM student_Form WHERE id = '$id'" : "SELECT * FROM student_Form";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['siblings_Count'] = $row['siblings_Count'] !== null ? (int)$row['siblings_Count'] : null;
        $row['sibling_Age'] = $row['sibling_Age'] !== null ? (int)$row['sibling_Age'] : null;

        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode([]);
}

$conn->close();
?>


