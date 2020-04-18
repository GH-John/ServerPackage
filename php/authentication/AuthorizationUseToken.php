<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);

    $checkToken = "SELECT idUser FROM users WHERE token = '$token'";
    $response = mysqli_query($connect, $checkToken);

    if ($connect) {
        if (mysqli_num_rows($response)) {
            $result['response'] = "USER_LOGGED";
        } else {
            $result['response'] = "WRONG_TOKEN";
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }

    $result['mysqli_error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
