<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = filter_var(trim($_POST['login']), FILTER_SANITIZE_STRING);

    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $lastName = filter_var(trim($_POST['lastName']), FILTER_SANITIZE_STRING);

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_STRING);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);


    $test = $password;



    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $address_1 = filter_var(trim($_POST['address_1']), FILTER_SANITIZE_STRING);
    $accountType = filter_var(trim($_POST['accountType']), FILTER_SANITIZE_STRING);

    require_once '../../../Utils.php';

    if (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
    )
        $url = "https://";
    else
        $url = "http://";

    $url .= $_SERVER['SERVER_ADDR'];

    $pathForMove = '../../../../pictures/';
    $pathForServerSave = '/AndroidConnectWithServer/pictures/';

    if ($connect) {
        $checkLogin = "SELECT idUser FROM users WHERE login = '$login'";
        $resCheckLogin = mysqli_query($connect, $checkLogin);
        $rezultCheckLogin = mysqli_num_rows($resCheckLogin);

        $checkUser = "SELECT idUser FROM users WHERE email = '$email'";
        $response = mysqli_query($connect, $checkUser);
        $resultCheck = mysqli_num_rows($response);

        if (!$rezultCheckLogin) {
            if (!$resultCheck) {
                $token = password_hash($email . $password, PASSWORD_DEFAULT);
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
                            $result['code'] = UNKNOW_ERROR;
                            $result['error'] = $e->getMessage();
                        }
                    } else {
                        $result['code'] = FILES_EMPTY;
                    }
                } else {
                    $result['code'] = PHP_INI_NOT_LOADED;
                }

                $insertUser = "INSERT INTO users (userLogo, name, lastName, login, email, password, token, phone_1, accountType, address_1)
                    VALUES ('$userLogoUri', '$name', '$lastName', '$login', '$email', '$password', '$token', '$phone', '$accountType', '$address_1')";

                if (mysqli_query($connect, $insertUser)) {

                    $selectUser = "SELECT idUser, token, name, lastName,
                        userLogo, login, email, address_1, address_2, address_3, 
                        phone_1, phone_2, phone_3, accountType, balance, rating, 
                        statusUser, countAnnouncementsUser, countAllViewers, 
                        countFollowers, countFollowing, created, updated 
                    FROM users WHERE token = '$token'";
                    $resSelect = mysqli_query($connect, $selectUser);
                    $rows =  mysqli_num_rows($resSelect);

                    if ($rows) {
                        $result['response'] = mysqli_fetch_assoc($resSelect);
                        $result['code'] = SUCCESS;
                    }
                } else {
                    $result['code'] = UNSUCCESS;
                }
            } else {
                $result['code'] = USER_EXISTS;
            }
        } else {
            $result['code'] = USER_WITH_LOGIN_EXISTS;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }

    $result['error'] = mysqli_error($connect);


    $result['test'] = $test;



    echo json_encode($result);
    mysqli_close($connect);
}
