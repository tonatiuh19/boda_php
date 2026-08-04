<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['date']) && isset($params['id_guest'])) {
        $input_date = strtotime($params['date']);
        $id_guest = $params['id_guest'];

        // Query to fetch the latest session details by id_sites_visitor
        $sql = "SELECT date, id_guest FROM sites_visitors WHERE id_guest = ? ORDER BY id_sites_visitor DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_guest);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            $session_date = strtotime($session['date']);

            // Check if the session date is older than three hours from the input date
            if ($input_date - $session_date > 3 * 60 * 60) {
                $res = json_encode(true);
            } else {
                $res = json_encode(false);
            }

            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode(["message" => "No session found for the provided id_guest"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>