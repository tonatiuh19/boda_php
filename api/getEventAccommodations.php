<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_event'])) {
        $id_event = $params['id_event'];

        $sql = "SELECT a.id_event_accomodations_suggestions, a.title, a.address_link, a.promo_code 
                FROM event_accomodations_suggestions as a 
                WHERE a.active=1 AND a.id_event=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_event);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $accommodations = [];
            while ($row = $result->fetch_assoc()) {
                $accommodations[] = array_map('utf8_encode', $row);
            }
            $res = json_encode($accommodations, JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>