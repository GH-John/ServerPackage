<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);

    $address_1 = filter_var(trim($_POST['address_1']), FILTER_SANITIZE_STRING);
    $address_2 = filter_var(trim($_POST['address_2']), FILTER_SANITIZE_STRING);
    $address_3 = filter_var(trim($_POST['address_3']), FILTER_SANITIZE_STRING);

    $phone_1 = filter_var(trim($_POST['phone_1']), FILTER_SANITIZE_STRING);
    $phone_2 = filter_var(trim($_POST['phone_2']), FILTER_SANITIZE_STRING);
    $phone_3 = filter_var(trim($_POST['phone_3']), FILTER_SANITIZE_STRING);


    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $updateProfile = "UPDATE users SET address_1 = '$address_1',
                                        address_2 = '$address_2',
                                        address_3 = '$address_3',
                                        phone_1 = '$phone_1',
                                        phone_2 = '$phone_2',
                                        phone_3 = '$phone_3',
                                        updated = UTC_TIMESTAMP()
                                    WHERE idUser = '$idUser'";

    if ($connect) {
        if ($idUser) {
            $response = mysqli_query($connect, $updateProfile);
            if ($response) {
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
