<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
require_once('db_cnn/cnn.php');
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['mainDirectory']) && isset($params['secondaryDirectory'])) {
        $mainDirectory = $params['mainDirectory'];
        $secondaryDirectory = $params['secondaryDirectory'];

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'mp4', 'avi', 'mov', 'wmv'];

        function getMediaFiles($directory, $allowed_types)
        {
            $mediaFiles = [];
            if (is_dir($directory)) {
                $files = array_diff(scandir($directory), array('.', '..'));
                foreach ($files as $file) {
                    $file_path = realpath($directory . '/' . $file);
                    $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                    if (in_array($file_type, $allowed_types)) {
                        // Generate URL path
                        $url_path = str_replace($_SERVER['DOCUMENT_ROOT'], 'https://garbrix.com', $file_path);
                        $mediaFiles[] = [
                            'name' => $file,
                            'path' => $url_path
                        ];
                    }
                }
            }
            return $mediaFiles;
        }

        $id_guest = 0;

            // Insert data into sites_visitors table
        $id_event = 1; // Example value, replace with actual data
        $date = date('Y-m-d H:i:s'); // Current date and time
        $section = 'main_section'; // Example value, replace with actual data

        $sql_insert_site_visitor = "INSERT INTO sites_visitors (id_event, date, section, id_guest) VALUES (?, ?, ?, ?)";
        $stmt_insert_site_visitor = $conn->prepare($sql_insert_site_visitor);
        $stmt_insert_site_visitor->bind_param("issi", $id_event, $date, $section, $id_guest);

        if (!$stmt_insert_site_visitor->execute()) {
            echo json_encode(["message" => "Failed to insert site visitor", "error" => $stmt_insert_site_visitor->error]);
            exit;
        }

        $mainSection = getMediaFiles($mainDirectory, $allowed_types);
        $secondarySection = getMediaFiles($secondaryDirectory, $allowed_types);

        // Get current time and end time
        $startTime = date('Y-m-d H:i:s');
        $civilMarriage = date('Y-m-d H:i:s', strtotime('2025-03-15 19:00:00'));
        $religiuosMarriage = date('Y-m-d H:i:s', strtotime('2027-03-13 13:00:00'));
        $ladieBachelorTrip = date('Y-m-d H:i:s', strtotime('2026-02-05 14:00:00'));
        $manBachelorTrip = date('Y-m-d H:i:s', strtotime('2026-03-14 14:00:00'));
        $mixBachelorTrip = date('Y-m-d H:i:s', strtotime('2026-03-14 14:00:00'));

        $response = [
            'mainSection' => $mainSection,
            'secondarySection' => $secondarySection,
            'startTime' => $startTime,
            'civilMarriage' => $civilMarriage,
            'religiuosMarriage' => $religiuosMarriage,
            'ladieBachelorTrip' => $ladieBachelorTrip,
            'manBachelorTrip' => $manBachelorTrip,
            'mixBachelorTrip' => $mixBachelorTrip
        ];

        $res = json_encode($response, JSON_NUMERIC_CHECK);
        header('Content-type: application/json; charset=utf-8');
        echo $res;
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}
?>