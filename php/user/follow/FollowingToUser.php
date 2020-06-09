<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idUser = filter_var(trim($_POST['idUser']), FILTER_SANITIZE_STRING);

    $isFollow = convert_to_bool(filter_var(trim($_POST['isFollow']), FILTER_SANITIZE_STRING));

    $idUserFollower = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $checkFollower = getRow($connect, 'idFollower', "SELECT idFollower FROM followers 
                                                WHERE idUser = '$idUser' 
                                                AND idUserFollower = '$idUserFollower'");

    $followToUser = "INSERT INTO followers(idUser, idUserFollower) VALUE ('$idUser', '$idUserFollower')";

    $unfollow = "DELETE FROM followers WHERE idUser = '$idUser' AND idUserFollower = '$idUserFollower'";

    $result['response'] = array();

    if ($connect) {
        if ($idUserFollower) {
            if ($isFollow) {
                if (!$checkFollower) {
                    if (mysqli_query($connect, $followToUser)) {
                        $result['code'] = SUCCESS;
                    } else {
                        $result['code'] = ERROR_FOLLOW;
                    }
                } else {
                    $result['code'] = ALLREADY_FOLLOW;
                }
            } else {
                if ($checkFollower) {
                    if (mysqli_query($connect, $unfollow)) {
                        $result['code'] = SUCCESS;
                    } else {
                        $result['code'] = ERROR_UNFOLLOW;
                    }
                } else {
                    $result['code'] = ALLREADY_UNFOLLOW;
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
