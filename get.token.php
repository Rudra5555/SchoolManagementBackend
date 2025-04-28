<?php

require_once 'db.php';
require_once 'jwt_utils.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    $username = mysqli_real_escape_string($dbConn, $data->username);
    $password = mysqli_real_escape_string($dbConn, $data->password);

    $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password' LIMIT 1";
    $result = dbQuery($sql);

    if (dbNumRows($result) < 1) {
        echo json_encode(['token' => null, 'error' => 'Invalid user']);
    } else {
        $payload = ['username' => $username, 'exp' => time() + 5000]; // token valid for 5 mins
        $jwt = generate_jwt(['alg' => 'HS256', 'typ' => 'JWT'], $payload);
        echo json_encode(['token' => $jwt, 'error' => null]);
    }
}
?>
