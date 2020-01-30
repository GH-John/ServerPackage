<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $lastName = filter_var(trim($_POST['lastName']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $accountType = filter_var(trim($_POST['accountType']), FILTER_SANITIZE_STRING);

    require_once '../Utils.php';

    if ($connect) {
        $checkUser = "SELECT idUser FROM users WHERE email = '$email'";
        $response = mysqli_query($connect, $checkUser);

        $resultCheck = mysqli_num_rows($response);
        if (!$resultCheck) {
            $token = password_hash($email, PASSWORD_DEFAULT);
            $password = password_hash($password, PASSWORD_DEFAULT);

            $insertUser = "INSERT INTO users (name, lastName, email, password, token, phone_1, accountType)
                    VALUES ('$name', '$lastName', '$email', '$password', '$token', '$phone', '$accountType')";

            if (mysqli_query($connect, $insertUser)) {

                $result['token'] = $token;
                $result['code'] = "1";
                $result['message'] = "SUCCESS: User is registered";
            } else {
                $result['code'] = "2";
                $result['message'] = "ERROR: Registration user";
            }
        } else {
            $result['code'] = "0";
            $result['message'] = "ERROR: User exists";
        }
    } else {
        $result['code'] = "101";
        $result['message'] = "ERROR: Could not connect to DB";
    }

    echo json_encode($result);
    mysqli_close($connect);
}
