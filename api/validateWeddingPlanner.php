<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['code'])) {
        $code = $params['code'];

        // Query to fetch wedding planner details
        $sql = "SELECT a.id_event, a.full_name, a.code 
                FROM event_wedding_planners as a 
                WHERE a.active=1 AND a.code=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $wedding_planner = $result->fetch_assoc();
            $res = json_encode(array_map('utf8_encode', $wedding_planner), JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode(false);
        }
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

$conn->close();
?>