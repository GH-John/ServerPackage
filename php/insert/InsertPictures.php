<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);
    $nameMainPicture = filter_var(trim($_POST['nameMainPicture']), FILTER_SANITIZE_STRING);

    if (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
    )
        $url = "https://";
    else
        $url = "http://";

    $url .= $_SERVER['SERVER_ADDR'];

    require_once '../Utils.php';

    $pathForMove = '../../pictures/';
    $pathForServerSave = '/AndroidConnectWithServer/pictures/';

    $count = $_POST['countPictures'];
    $counterLoadedPicture = 0;

    if ($connect) {
        if (php_ini_loaded_file()) {
            if (!empty($_FILES) && $connect) {
                try {
                    for ($x = 0; $x < $count; $x++) {
                        $name = basename($_FILES['picture_' . $x]['name']);

                        $newName = time() . $name;

                        $newPath = $pathForMove . $newName;

                        $newUrl = $url . $pathForServerSave . $newName;

                        if (!move_uploaded_file($_FILES['picture_' . $x]['tmp_name'], $newPath)) {
                            $result['error'] = $_FILES['picture_' . $x]['error'];
                        } else {
                            if ($name == $nameMainPicture) {
                                $request = "INSERT INTO pictures (idAnnouncement, picture, isMainPicture)
                                    VALUES ('$idAnnouncement', '$newUrl', '1')";
                            } else {
                                $request = "INSERT INTO pictures (idAnnouncement, picture)
                                    VALUES ('$idAnnouncement', '$newUrl')";
                            }

                            if (mysqli_query($connect, $request)) {
                                $counterLoadedPicture++;
                            }
                        }
                    }
                } catch (Exception $e) {
                    $result['response'] = 'UNKNOW_ERROR';
                    $result['error'] = $e->getMessage();
                } finally {
                    if ($count == $counterLoadedPicture) {
                        $result['response'] = 'SUCCESS_PICTURES_ADDED';
                    } else {
                        $result['response'] = 'UNSUCCESS_PICTURES_ADDED';
                    }
                }
            } else {
                $result['response'] = 'UNSUCCESS_PICTURES_ADDED';
            }
        } else {
            $result['response'] = 'PHP_INI_NOT_LOADED';
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
}
