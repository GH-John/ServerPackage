<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

    require_once '../Utils.php';

    $checkUser = "SELECT token, password FROM users WHERE email = '$email'";
    $response = mysqli_query($connect, $checkUser);

    $result['token'] = "-1";

    if ($connect) {
        $resultCheck = mysqli_num_rows($response);
        if ($resultCheck) {
            $row = mysqli_fetch_assoc($response);

            if (password_verify($password, $row['password'])) {
                $result['token'] = $row['token'];

                $result['response'] = "USER_LOGGED";
            } else {
                $result['response'] = "WRONG_PASSWORD";
            }
        } else {
            $result['response'] = "WRONG_EMAIL";
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }

    $result['mysqli_error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
