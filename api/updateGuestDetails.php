<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_guest']) && isset($params['email']) && isset($params['phone']) && isset($params['valueConfirmation'])) {
        $id_guest = $params['id_guest'];
        $email = $params['email'];
        $phone = $params['phone'];
        $confirmation = $params['valueConfirmation'] === 'yes' ? 1 : 0;

        // Update guest details
        $sql_update_guest = "UPDATE guests SET email = ?, phone = ?, confirmation = ? WHERE id_guest = ?";
        $stmt = $conn->prepare($sql_update_guest);
        $stmt->bind_param("ssii", $email, $phone, $confirmation, $id_guest);

        if ($stmt->execute()) {
            // Check for extra guests
            $extraGuests = array_filter($params, function ($key) {
                return strpos($key, 'extraGuest') === 0 && strpos($key, 'Checkbox') === false;
            }, ARRAY_FILTER_USE_KEY);

            foreach ($extraGuests as $key => $full_name) {
                $checkboxKey = $key . 'Checkbox';
                if (isset($params[$checkboxKey]) && $params[$checkboxKey] === true) {
                    // Insert extra guest
                    $sql_insert_extra = "INSERT INTO guests_extras (id_guest, full_name, confirmation) VALUES (?, ?, ?)";
                    $stmt_extra = $conn->prepare($sql_insert_extra);
                    $stmt_extra->bind_param("isi", $id_guest, $full_name, $confirmation);
                    $stmt_extra->execute();
                }
            }

            echo json_encode(["message" => "Guest details updated successfully"]);
        } else {
            echo json_encode(["message" => "Failed to update guest details"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>