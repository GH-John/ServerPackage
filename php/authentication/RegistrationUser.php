<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $lastName = filter_var(trim($_POST['lastName']), FILTER_SANITIZE_STRING);
    $login = filter_var(trim($_POST['login']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $accountType = filter_var(trim($_POST['accountType']), FILTER_SANITIZE_STRING);

    require_once '../Utils.php';

    if (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
    )
        $url = "https://";
    else
        $url = "http://";

    $url .= $_SERVER['SERVER_ADDR'];

    $pathForMove = '../../pictures/';
    $pathForServerSave = '/AndroidConnectWithServer/pictures/';

    $result['token'] = "-1";

    if ($connect) {
        $checkLogin = "SELECT idUser FROM users WHERE login = '$login'";
        $resCheckLogin = mysqli_query($connect, $checkLogin);
        $rezultCheckLogin = mysqli_num_rows($resCheckLogin);

        $checkUser = "SELECT idUser FROM users WHERE email = '$email'";
        $response = mysqli_query($connect, $checkUser);
        $resultCheck = mysqli_num_rows($response);

        if (!$rezultCheckLogin) {
            if (!$resultCheck) {
                $token = password_hash($email, PASSWORD_DEFAULT);
                $password = password_hash($password, PASSWORD_DEFAULT);

                $userLogoUri = "";

                if (php_ini_loaded_file()) {
                    if (!empty($_FILES)) {
                        try {
                            $oldName = basename($_FILES['userLogo']['name']);

                            $newName = time() . $oldName;

                            $newPath = $pathForMove . $newName;

                            $newUrl = $url . $pathForServerSave . $newName;

                            $result['fileName'] = $_FILES['userLogo']['name'];
                            $result['newUrl'] = $newUrl;


                            if (!move_uploaded_file($_FILES['userLogo']['tmp_name'], $newPath)) {
                                $result['error'] = $_FILES['userLogo']['error'];


                                $result['isMoved'] = false;
                            } else {
                                $userLogoUri = $newUrl;

                                $result['isMoved'] = true;
                            }
                        } catch (Exception $e) {
                            $result['response'] = 'UNKNOW_ERROR';
                            $result['error'] = $e->getMessage();
                        }
                    } else {

                        $result['empty'] = "files empty";

                        $result['response'] = 'UNSUCCESS_PICTURES_ADDED';
                    }
                } else {
                    $result['php.ini'] = "not loaded";

                    $result['response'] = 'PHP_INI_NOT_LOADED';
                }

                $insertUser = "INSERT INTO users (userLogo, name, lastName, login, email, password, token, phone_1, accountType)
                    VALUES ('$userLogoUri', '$name', '$lastName', '$login', '$email', '$password', '$token', '$phone', '$accountType')";

                if (mysqli_query($connect, $insertUser)) {

                    $result['token'] = $token;
                    $result['response'] = "USER_SUCCESS_REGISTERED";
                } else {
                    $result['response'] = "USER_UNSUCCESS_REGISTERED";
                }
            } else {
                $result['response'] = "USER_EXISTS";
            }
        } else {
            $result['response'] = "USER_WITH_LOGIN_EXISTS";
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }

    $result['mysqli_error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
