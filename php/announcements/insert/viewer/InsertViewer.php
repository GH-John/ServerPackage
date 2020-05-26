<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $idAnnouncement = $_POST['idAnnouncement'];

    require_once '../../../Utils.php';

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");
    $checkViewer = getRow($connect, 'idUser', "SELECT idUser FROM viewers 
                            WHERE idUser = '$idUser'   
                            AND idAnnouncement = '$idAnnouncement'");

    $insertViewer = "INSERT INTO viewers (idUser, idAnnouncement) 
        VALUES ('$idUser', '$idAnnouncement')";

    if ($connect) {
        if (!$checkViewer) {
            if (mysqli_query($connect, $insertViewer)) {
                $result['code'] = SUCCESS;
            } else {
                $result['code'] = UNSUCCESS;
            }
        } else {
            $result['code'] = UNSUCCESS;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
