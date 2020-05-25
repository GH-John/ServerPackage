<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $loadProfile = "SELECT name, lastName, login, userLogo,
                    address_1, address_2, address_3,
                    phone_1, phone_2, phone_3,
                    balance, rating, statusUser, 
                    countAnnouncementsUser, countAllViewers, 
                    countFollowers, countFollowing, 
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
