<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody);
    $params = (array) $params;

    if ($params['username']) {
        $username = $params['username'];

        $sql = "SELECT a.id_user FROM users as a WHERE a.user_name='" . $username . "'";

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $res = json_encode(array_map('utf8_encode', $row), JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo "0";
        }
    } else {
        echo "Not valid Body Data";
    }
} else {
    echo "Not valid Data";
}

$conn->close();
