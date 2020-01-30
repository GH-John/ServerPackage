<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);

    $checkToken = "SELECT idUser FROM users WHERE token = '$token'";
    $response = mysqli_query($connect, $checkToken);

    if ($connect) {
        if (mysqli_num_rows($response)) {

            $result['code'] = "1";
            $result['message'] = "SUCCESS: User logged in";
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
