<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

function utf8_encode_recursive($data)
{
    if (is_string($data)) {
        return utf8_encode($data);
    } elseif (is_array($data)) {
        return array_map('utf8_encode_recursive', $data);
    } elseif (is_object($data)) {
        foreach ($data as $key => $value) {
            $data->$key = utf8_encode_recursive($value);
        }
        return $data;
    } else {
        return $data;
    }
}

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_guest']) && isset($params['email']) && isset($params['phone']) && isset($params['valueConfirmation'])) {
        $id_guest = $params['id_guest'];
        $email = $params['email'];
        $phone = $params['phone'];
        $confirmation = $params['valueConfirmation'] === 'yes' ? 1 : 0;
        $date_confirmed = date('Y-m-d H:i:s');
        $submitted = true;

        // Update guest details
        $sql_update_guest = "UPDATE guests SET email = ?, phone = ?, confirmation = ?, date_confirmed = ?, submited = ? WHERE id_guest = ?";
        $stmt = $conn->prepare($sql_update_guest);
        $stmt->bind_param("ssisii", $email, $phone, $confirmation, $date_confirmed, $submitted, $id_guest);

        if ($stmt->execute()) {
            // Fetch existing guest extras
            $sql_fetch_extras = "SELECT id_guest_extra FROM guests_extras WHERE id_guest = ?";
            $stmt_fetch_extras = $conn->prepare($sql_fetch_extras);
            $stmt_fetch_extras->bind_param("i", $id_guest);
            $stmt_fetch_extras->execute();
            $result_extras = $stmt_fetch_extras->get_result();

            $guest_extras = [];
            while ($row = $result_extras->fetch_assoc()) {
                $guest_extras[] = $row['id_guest_extra'];
            }

            // Update guest extras based on the provided extra_* values
            foreach ($guest_extras as $index => $id_guest_extra) {
                $extra_key = 'extra_' . $id_guest_extra; // Assuming extra_7, extra_8, etc.
                if (isset($params[$extra_key])) {
                    $extra_confirmation = $params[$extra_key] === 'yes' ? 1 : 0;

                    $sql_update_extra = "UPDATE guests_extras SET confirmation = ? WHERE id_guest_extra = ?";
                    $stmt_update_extra = $conn->prepare($sql_update_extra);
                    $stmt_update_extra->bind_param("ii", $extra_confirmation, $id_guest_extra);

                    if (!$stmt_update_extra->execute()) {
                        echo json_encode(["message" => "Failed to update extra guest", "error" => $stmt_update_extra->error]);
                        exit;
                    }
                }
            }

            // Fetch updated guest details
            $sql_guest = "SELECT a.id_guest, a.full_name, a.email, a.phone, a.guest_code, a.event_type, a.guest_type, a.guest_note, a.guest_extras, a.confirmation, a.photo, a.title, a.date_confirmed, a.submited
                          FROM guests as a 
                          WHERE a.id_guest='" . $id_guest . "'";

            $result_guest = $conn->query($sql_guest);
            if ($result_guest->num_rows > 0) {
                $guest_data = $result_guest->fetch_assoc();

                // Subquery to fetch event details
                $sql_event = "SELECT a.id_event, a.event_address, a.event_date, c.label, b.place, b.google_link, b.address_line1, b.address_line2, b.city, b.state, b.postal_code, b.country, c.label 
                              FROM event as a
                              INNER JOIN event_addresses as b on b.id_event_address = a.event_address
                              INNER JOIN event_types as c on c.id_event_type = a.event_type";

                $result_event = $conn->query($sql_event);
                if ($result_event->num_rows > 0) {
                    $event_data = $result_event->fetch_assoc();
                    $guest_data['event_details'] = $event_data;

                    // Fetch accommodations suggestions
                    $id_event = $event_data['id_event'];
                    $sql_accommodations = "SELECT a.id_event_accomodations_suggestions, a.title, a.address_link, a.promo_code 
                                           FROM event_accomodations_suggestions as a 
                                           WHERE a.active=1 AND a.id_event=?";
                    $stmt_accommodations = $conn->prepare($sql_accommodations);
                    $stmt_accommodations->bind_param("i", $id_event);
                    $stmt_accommodations->execute();
                    $result_accommodations = $stmt_accommodations->get_result();

                    if ($result_accommodations->num_rows > 0) {
                        $accommodations = [];
                        while ($row = $result_accommodations->fetch_assoc()) {
                            $accommodations[] = array_map('utf8_encode', $row);
                        }
                        $guest_data['accommodations'] = $accommodations;
                    } else {
                        $guest_data['accommodations'] = [];
                    }
                } else {
                    $guest_data['event_details'] = null;
                    $guest_data['accommodations'] = [];
                }

                // Query to fetch guest extras
                $sql_guest_extras = "SELECT a.id_guest_extra, a.full_name, a.email, a.phone, a.confirmation 
                                     FROM guests_extras as a 
                                     WHERE a.id_guest=" . $guest_data['id_guest'];

                $result_guest_extras = $conn->query($sql_guest_extras);
                if ($result_guest_extras->num_rows > 0) {
                    $guest_extras = [];
                    while ($row = $result_guest_extras->fetch_assoc()) {
                        $guest_extras[] = $row;
                    }
                    $guest_data['guest_extras'] = $guest_extras;
                } else {
                    $guest_data['guest_extras'] = [];
                }

                $guest_data = utf8_encode_recursive($guest_data);
                $res = json_encode($guest_data, JSON_NUMERIC_CHECK);
                header('Content-type: application/json; charset=utf-8');
                echo $res;
            } else {
                echo json_encode(new stdClass());
            }
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