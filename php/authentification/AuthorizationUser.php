<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

    require_once '../Utils.php';

    $checkUser = "SELECT name, token, password FROM users WHERE email = '$email'";
    $response = mysqli_query($connect, $checkUser);

    if ($connect) {
        $resultCheck = mysqli_num_rows($response);
        if ($resultCheck) {
            $row = mysqli_fetch_assoc($response);

            if (password_verify($password, $row['password'])) {
                $result['name'] = $row['name'];
                $result['token'] = $row['token'];

                $result['code'] = "1";
                $result['message'] = "SUCCESS: User logged in";
            } else {
                $result['code'] = "2";
                $result['message'] = "UNSUCCESS: Wrong password";
            }
        } else {
            $result['code'] = "3";
            $result['message'] = "UNSUCCESS: Wrong email";
        }
    } else {
        $result['code'] = "101";
        $result['message'] = "ERROR: Could not connect to DB";
    }

    echo json_encode($result);
    mysqli_close($connect);
}
