<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';
    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = $_POST['idAnnouncement'];
    
    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $checkFavorite = "SELECT idAnnouncement FROM favoriteAnnouncements 
        WHERE idUser = '$idUser' AND idAnnouncement = '$idAnnouncement'";

    $insertToFavorite = "INSERT INTO favoriteAnnouncements (idUser, idAnnouncement)
        VALUES ('$idUser', '$idAnnouncement')";

    $deleteFromFavorite = "DELETE FROM favoriteAnnouncements 
        WHERE idUser = '$idUser' AND idAnnouncement = '$idAnnouncement'";

    if ($connect) {        
        if ($idUser) {
            $response = mysqli_query($connect, $checkFavorite);

            if (mysqli_num_rows($response)) {

                if (mysqli_query($connect, $deleteFromFavorite)) {
                    $result['isFavorite'] = 'false';
                    $result['code'] = "1";
                    $result["message"] = "SUCCESS: Delete from favorite";
                } else {
                    $result['isFavorite'] = 'false';
                    $result['code'] = "0";
                    $result["message"] = "ERROR: Delete from favorite";
                }            
            } else {

                if (mysqli_query($connect, $insertToFavorite)) {
                    $result['isFavorite'] = 'true';
                    $result['code'] = "1";
                    $result["message"] = "SUCCESS: Insert to favorite";
                } else {
                    $result['isFavorite'] = 'false';
                    $result['code'] = "0";
                    $result["message"] = "ERROR: Insert to favorite";
                }
            }
        } else {
            $result['code'] = "2";
            $result['message'] = "UNSUCCESS: Wrong token";
        }
    } else {
        $result["code"] = "101";
        $result["message"] = "ERROR: Could not connect to DB";
    }

    echo json_encode($result);
    mysqli_close($connect);
}
