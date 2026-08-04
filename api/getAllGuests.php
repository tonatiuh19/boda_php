<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $sql = "
        SELECT 
            a.id_guest, 
            NULL AS id_guest_parent,
            a.full_name, 
            a.title, 
            a.email, 
            a.phone, 
            a.guest_code, 
            a.event_type, 
            c.label AS event_label, 
            a.guest_type, 
            a.guest_note, 
            a.photo, 
            a.confirmation, 
            a.date_confirmed, 
            a.submited,
            d.table,
            d.chair
        FROM guests AS a
        INNER JOIN event_types AS c ON c.id_event_type = a.event_type
        LEFT JOIN event_layout_tables AS d ON d.id_guest = a.id_guest
        WHERE a.confirmation = 1

        UNION ALL

        SELECT 
            b.id_guest_extra AS id_guest, 
            b.id_guest AS id_guest_parent,
            b.full_name, 
            NULL AS title, 
            NULL AS email, 
            NULL AS phone, 
            NULL AS guest_code, 
            NULL AS event_type, 
            NULL AS event_label, 
            b.guest_type, 
            NULL AS guest_note, 
            NULL AS photo, 
            b.confirmation, 
            NULL AS date_confirmed, 
            NULL AS submited,
            f.table,
            f.chair
        FROM guests_extras AS b
        LEFT JOIN event_layout_tables AS f ON f.id_guest = b.id_guest_extra
        WHERE b.confirmation = 1
        ORDER BY id_guest;
    ";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $guests = [];
        while ($row = $result->fetch_assoc()) {
            $guests[] = array_map('utf8_encode', $row);
        }
        $res = json_encode($guests, JSON_NUMERIC_CHECK);
        header('Content-type: application/json; charset=utf-8');
        echo $res;
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>