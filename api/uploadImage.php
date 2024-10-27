<?php 
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");


require_once('../../admin/cn.php');
$response = array();

if($_FILES['avatar'])
{
    $idProduct = $_POST['id_products'];
    $folder_path = "../user/".$idProduct."/profile/";
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }else{
        $files = glob($folder_path.'*'); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
        }
    }

    $filename = basename($_FILES['avatar']['name']);
    $newname = $folder_path . $filename;
    $fileOk = 1;
    $types = array('image/jpeg', 'image/jpg', 'image/png');  

    if (($_FILES["avatar"]["type"] == "image/jpeg") || ($_FILES["avatar"]["type"] == "image/jpg") || ($_FILES["avatar"]["type"] == "image/png"))
    {
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $newname))
        {
            $sql = "UPDATE products SET active=1 WHERE id_products=".$idProduct."";

            if ($conn->query($sql) === TRUE) {
                $response = array(
                    "status" => "1",
                    "message" => "File uploaded!"
                );
            } else {
                $response = array(
                    "status" => "2",
                    "message" => "Error updating record: " . $conn->error
                );
            }
            
        }else{
            $response = array(
                "status" => "2",
                "message" => "Something happened!"
            );
        }
    }else{
        $response = array(
            "status" => "3",
            "message" => "File invalid!"
        );
    }

}else{
    $response = array(
        "status" => "error",
        "error" => true,
        "message" => "No file was sent!"
    );
}

echo json_encode($response);
?>