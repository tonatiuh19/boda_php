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

            // Debugging output
            echo "Extra guests: ";
            print_r($extraGuests);

            foreach ($extraGuests as $key => $full_name) {
                error_log("Processing extra guest: key = $key, full_name = $full_name");

                // Insert extra guest
                $sql_insert_extra = "INSERT INTO guests_extras (id_guest, full_name, confirmation) VALUES (?, ?, ?)";
                $stmt_extra = $conn->prepare($sql_insert_extra);

                if ($stmt_extra === false) {
                    error_log("Failed to prepare statement: " . $conn->error);
                    echo json_encode(["message" => "Failed to prepare statement", "error" => $conn->error]);
                    exit;
                }

                $stmt_extra->bind_param("isi", $id_guest, $full_name, $confirmation);

                if (!$stmt_extra->execute()) {
                    error_log("Failed to execute statement: " . $stmt_extra->error);
                    echo json_encode(["message" => "Failed to insert extra guest", "error" => $stmt_extra->error]);
                    exit;
                } else {
                    error_log("Successfully inserted extra guest: $full_name");
                }
            }

            echo json_encode(["message" => "Guest details updated successfully"]);
        } else {
            echo json_encode(["message" => "Failed to update guest details", "error" => $stmt->error]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
