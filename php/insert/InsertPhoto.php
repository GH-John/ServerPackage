<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);
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
            $request = "INSERT INTO photo (idAnnouncement, photoPath)
                VALUES ('$idAnnouncement', '$url')";

            if (mysqli_query($connect, $request)) {
                $result['code'] = "1";
                $result['message'] = "SUCCESS: Photo added";
            } else {
                $result['code'] = "2";
                $result['message'] = mysqli_error($connect);
            }
        } else {
            $result['code'] = "0";
            $result['message'] = "ERROR: Photo don't added to server";
        }
    } else {
        $result['code'] = "101";
        $result['message'] = "ERROR: Could not connect to DB";
    }
    echo json_encode($result);
    mysqli_close($connect);
}
