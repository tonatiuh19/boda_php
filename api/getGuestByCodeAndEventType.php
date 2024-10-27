<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
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
    $params = json_decode($requestBody);
    $params = (array) $params;

    if (isset($params['guest_code']) && isset($params['event_type'])) {
        $guest_code = $params['guest_code'];
        $event_type = $params['event_type'];

        // Main query to fetch guest details
        $sql_guest = "SELECT a.id_guest, a.full_name, a.email, a.phone, a.guest_code, a.event_type, a.guest_type, a.guest_note, a.guest_extras, a.confirmation 
                      FROM guests as a 
                      WHERE a.guest_code='" . $guest_code . "' AND a.event_type=" . $event_type;

        $result_guest = $conn->query($sql_guest);
        if ($result_guest->num_rows > 0) {
            $guest_data = $result_guest->fetch_assoc();

            // Subquery to fetch event details
            $sql_event = "SELECT a.id_event, a.event_address, a.event_date, c.label, b.place, b.google_link, b.address_line1, b.address_line2, b.city, b.state, b.postal_code, b.country, c.label 
                          FROM event as a
                          INNER JOIN event_addresses as b on b.id_event_address = a.event_address
                          INNER JOIN event_types as c on c.id_event_type = a.event_type
                          WHERE a.event_type=" . $event_type;

            $result_event = $conn->query($sql_event);
            if ($result_event->num_rows > 0) {
                $event_data = $result_event->fetch_assoc();
                $guest_data['event_details'] = $event_data;
            } else {
                $guest_data['event_details'] = null;
            }

            $guest_data = utf8_encode_recursive($guest_data);
            $res = json_encode($guest_data, JSON_NUMERIC_CHECK);
            header('Content-type: application/json; charset=utf-8');
            echo $res;
        } else {
            echo json_encode(new stdClass());
        }
    } else {
        echo json_encode(new stdClass());
    }
} else {
    echo json_encode(new stdClass());
}

$conn->close();