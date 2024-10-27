<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $idUser = $_POST['id_user'];
        $target_dir = "../user/" . $idUser . "/civil/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES['video']['name']);
        $videoFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size (limit to 100MB)
        if ($_FILES['video']['size'] > 100000000) {
            echo json_encode(["message" => "Sorry, your file is too large."]);
            exit;
        }

        // Allow certain file formats
        $allowed_types = ['mp4', 'avi', 'mov', 'wmv'];
        if (!in_array($videoFileType, $allowed_types)) {
            echo json_encode(["message" => "Sorry, only MP4, AVI, MOV, & WMV files are allowed."]);
            exit;
        }

        if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
            echo json_encode(["message" => 1]);
        } else {
            echo json_encode(["message" => 0]);
        }
    } else {
        echo json_encode(["message" => 0]);
    }
} else {
    echo json_encode(["message" => 0]);
}
?>