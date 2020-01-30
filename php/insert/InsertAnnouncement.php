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
    $isVisible_phone_1 = filter_var(trim($_POST['isVisible_phone_1']), FILTER_SANITIZE_STRING);

    $phone_2 = filter_var(trim($_POST['phone_2']), FILTER_SANITIZE_STRING);
    $isVisible_phone_2 = filter_var(trim($_POST['isVisible_phone_2']), FILTER_SANITIZE_STRING);

    $phone_3 = filter_var(trim($_POST['phone_3']), FILTER_SANITIZE_STRING);
    $isVisible_phone_3 = filter_var(trim($_POST['isVisible_phone_3']), FILTER_SANITIZE_STRING);

    $encodedString = $_POST['encodedString'];

    $decodedString = base64_decode($encodedString);

    $targetDir = "../../photo/";
    $hash = md5($idAnnouncement + rand());
    $namePhoto = "{$idAnnouncement}_{$hash}.jpeg";

    if ( isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
    )
        $url = "https://";
    else
        $url = "http://";

    $url .= $_SERVER['SERVER_ADDR'] . "/AndroidConnectWithServer/photo/" . $namePhoto;

    $photoPath = "{$targetDir}{$namePhoto}";

    $file = fopen("{$photoPath}", 'w');
    fwrite($file, $decodedString);
    fclose($file);

    require_once '../Utils.php';

    if ($connect) {
        if (file_exists($photoPath)) {
            $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

            if ($idUser) {
                $request = "INSERT INTO announcements (idUser, idSubcategory, name, description, costToBYN, costToUSD, 
            costToEUR, address, phone_1, isVisible_phone_1, phone_2, isVisible_phone_2, phone_3, isVisible_phone_3, photoPath,
            lifeCicle) VALUES ('$idUser', '$idSubcategory', '$name', '$description', '$costToBYN', '$costToUSD', 
            '$costToEUR', '$address', '$phone_1', $isVisible_phone_1, '$phone_2', $isVisible_phone_2, '$phone_3', 
            $isVisible_phone_3, '$url', DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 DAY))";

                if (mysqli_query($connect, $request)) {
                    $idAnnouncement = getRow($connect, 'idAnnouncement', "SELECT idAnnouncement FROM announcements WHERE 
                    idUser = '$idUser' AND name = '$name' AND idSubcategory = '$idSubcategory'");

                    $result['idAnnouncement'] = $idAnnouncement;
                    $result['code'] = "1";
                    $result['message'] = "SUCCESS: Announcement added";
                } else {
                    $result['code'] = "2";
                    $result['message'] = mysqli_error($connect);
                }
            } else {
                $result['code'] = "0";
                $result['message'] = "ERROR: Wrong user";
            }
        } else {
            $result['code'] = "3";
            $result['message'] = "ERROR: Main photo don't added to server";
        }
    } else {
        $result['code'] = "101";
        $result['message'] = "ERROR: Could not connect to DB";
    }

    echo json_encode($result);
    mysqli_close($connect);
}