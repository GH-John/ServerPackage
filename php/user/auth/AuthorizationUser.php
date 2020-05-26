<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_login = filter_var(trim($_POST['email_login']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

    require_once '../../Utils.php';

    $checkUser = "SELECT idUser, token, name, lastName,
        userLogo, login, email, address_1, address_2, address_3, 
        phone_1, phone_2, phone_3, accountType, balance, rating, 
        statusUser, countAnnouncementsUser, countAllViewers, 
        countFollowers, countFollowing, created, updated 
    FROM users WHERE email = '$email_login' OR login = '$email_login'";
    $response = mysqli_query($connect, $checkUser);

    if ($connect) {
        $rows = mysqli_num_rows($response);
        if ($rows) {
            $resCheck = mysqli_fetch_assoc($response);

            if (password_verify($password, $resCheck['password'])) {

                $result['response'] = $resCheck;
                $result['code'] = SUCCESS;
            } else {
                $result['code'] = WRONG_PASSWORD;
            }
        } else {
            $result['code'] = WRONG_EMAIL_LOGIN;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
