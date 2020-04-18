<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idSubcategory = filter_var(trim($_POST['idSubcategory']), FILTER_SANITIZE_STRING);
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $description = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);

    $costToBYN = filter_var(trim($_POST['costToBYN']), FILTER_SANITIZE_STRING);
    $costToUSD = filter_var(trim($_POST['costToUSD']), FILTER_SANITIZE_STRING);
    $costToEUR = filter_var(trim($_POST['costToEUR']), FILTER_SANITIZE_STRING);

    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);

    $phone_1 = filter_var(trim($_POST['phone_1']), FILTER_SANITIZE_STRING);

    $phone_2 = filter_var(trim($_POST['phone_2']), FILTER_SANITIZE_STRING);

    $phone_3 = filter_var(trim($_POST['phone_3']), FILTER_SANITIZE_STRING);

    require_once '../Utils.php';

    if ($connect) {

        $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

        if ($idUser) {
            $request = "INSERT INTO announcements (
                            idUser, idSubcategory, name, description, costToBYN, costToUSD, 
                            costToEUR, address, phone_1, phone_2, phone_3, lifeCicle) 
                        VALUES (
                            '$idUser', '$idSubcategory', '$name', '$description', 
                            '$costToBYN', '$costToUSD', '$costToEUR', '$address', 
                            '$phone_1', '$phone_2','$phone_3', 
                            DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 DAY))";

            if (mysqli_query($connect, $request)) {
                $idAnnouncement = getRow(
                    $connect,
                    'idAnnouncement',
                    "SELECT idAnnouncement 
                FROM announcements 
                WHERE idUser = '$idUser' 
                AND name = '$name' 
                AND idSubcategory = '$idSubcategory'"
                );

                $result['idAnnouncement'] = $idAnnouncement;
                $result['response'] = "SUCCESS_ANNOUNCEMENT_ADDED";
            } else {
                $result['response'] = "UNSUCCESS_ANNOUNCEMENT_ADDED";
            }
        } else {
            $result['response'] = "USER_NOT_FOUND";
        }
    } else {
        $result['response'] = 'UNKNOW_ERROR';
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
