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

    $result['response'] = array();

    if ($connect) {
        if ($idUser) {
            $response = mysqli_query($connect, $checkFavorite);

            if (mysqli_num_rows($response)) {

                if (mysqli_query($connect, $deleteFromFavorite)) {
                    $row['isFavorite'] = 'false';

                    array_push($result['response'], $row);

                    $result['code'] = SUCCESS;
                } else {
                    $row['isFavorite'] = 'false';

                    array_push($result['response'], $row);
                    $result['code'] = UNSUCCESS;
                }
            } else {

                if (mysqli_query($connect, $insertToFavorite)) {
                    $row['isFavorite'] = 'true';

                    array_push($result['response'], $row);
                    $result['code'] = SUCCESS;
                } else {
                    $row['isFavorite'] = 'false';

                    array_push($result['response'], $row);
                    $result['code'] = UNSUCCESS;
                }
            }
        } else {
            $result['code'] = USER_NOT_FOUND;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
