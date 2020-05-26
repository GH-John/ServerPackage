<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idUser = filter_var(trim($_POST['idUser']), FILTER_SANITIZE_STRING);

    $idUserViewer = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $loadProfile = "SELECT idUser, name, lastName,
                    userLogo, login, accountType, rating, 
                    statusUser, countAnnouncementsUser, countAllViewers, 
                    countFollowers, countFollowing,
                    (SELECT EXISTS(SELECT idFollower FROM followers WHERE idUser = '$idUser' AND idUserFollower = '$idUserViewer')) isFollow,
                    created, updated
                    FROM users 
                    WHERE idUser = '$idUser'";

    $result['response'] = array();

    if ($connect) {
        if ($idUser) {
            $response = mysqli_query($connect, $loadProfile);
            if ($response) {
                $result['response'] = mysqli_fetch_assoc($response);
                $result['code'] = SUCCESS;
            } else {
                $result['code'] = UNSUCCESS;
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
