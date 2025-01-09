<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (is_array($params)) {
        foreach ($params as $guest) {
            if (isset($guest['full_name']) && isset($guest['id_guest'])) {
                $full_name = $guest['full_name'];
                $id_guest = $guest['id_guest'];

                // Insert into guests_extras table
                $sql_insert_guest_extra = "INSERT INTO guests_extras (id_guest, full_name) VALUES (?, ?)";
                $stmt_insert_guest_extra = $conn->prepare($sql_insert_guest_extra);
                $stmt_insert_guest_extra->bind_param("is", $id_guest, $full_name);

                if (!$stmt_insert_guest_extra->execute()) {
                    echo json_encode(["message" => "Failed to insert guest extra", "error" => $stmt_insert_guest_extra->error]);
                    exit;
                }
            } else {
                echo json_encode(["message" => "Invalid input data"]);
                exit;
            }
        }
        echo json_encode(["message" => "Guests extras inserted successfully"]);
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>