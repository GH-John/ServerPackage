<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);

    $idSubcategory = filter_var(trim($_POST['idSubcategory']), FILTER_SANITIZE_STRING);

    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);

    $description = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);

    $hourlyCost = filter_var(trim($_POST['hourlyCost']), FILTER_SANITIZE_STRING);
    $hourlyCurrency = filter_var(trim($_POST['hourlyCurrency']), FILTER_SANITIZE_STRING);

    $dailyCost = filter_var(trim($_POST['dailyCost']), FILTER_SANITIZE_STRING);
    $dailyCurrency = filter_var(trim($_POST['dailyCurrency']), FILTER_SANITIZE_STRING);

    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);

    $phone_1 = filter_var(trim($_POST['phone_1']), FILTER_SANITIZE_STRING);
    $phone_2 = filter_var(trim($_POST['phone_2']), FILTER_SANITIZE_STRING);
    $phone_3 = filter_var(trim($_POST['phone_3']), FILTER_SANITIZE_STRING);

    $minTime = filter_var(trim($_POST['minTime']), FILTER_SANITIZE_STRING);
    $minDay = filter_var(trim($_POST['minDay']), FILTER_SANITIZE_STRING);

    $maxRentalPeriod = filter_var(trim($_POST['maxRentalPeriod']), FILTER_SANITIZE_STRING);

    $timeOfIssueWith = filter_var(trim($_POST['timeOfIssueWith']), FILTER_SANITIZE_STRING);
    $timeOfIssueBy = filter_var(trim($_POST['timeOfIssueBy']), FILTER_SANITIZE_STRING);

    $returnTimeWith = filter_var(trim($_POST['returnTimeWith']), FILTER_SANITIZE_STRING);
    $returnTimeBy = filter_var(trim($_POST['returnTimeBy']), FILTER_SANITIZE_STRING);

    $withSale = filter_var(trim($_POST['withSale']), FILTER_VALIDATE_BOOLEAN) === 'true' ? 1 : 0;

    $nameMainPicture = filter_var(trim($_POST['nameMainPicture']), FILTER_SANITIZE_STRING);

    if (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] === 'on'
    )
        $url = "https://";
    else
        $url = "http://";

    $url .= $_SERVER['SERVER_ADDR'];

    $pathForMove = '../../../pictures/';
    $pathForServerSave = '/AndroidConnectWithServer/pictures/';

    $count = $_POST['countPictures'];
    $counterLoadedPicture = 0;

    if ($connect) {

        $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

        if ($idUser) {
            $request = "INSERT INTO announcements (
                            idUser, idSubcategory, name, description, 
                            hourlyCost, hourlyCurrency, dailyCost, dailyCurrency, 
                            address, phone_1, phone_2, phone_3, 
                            lifeCicle,
                            minTime, minDay, maxRentalPeriod,
                            timeOfIssueWith, timeOfIssueBy,
                            returnTimeWith, returnTimeBy,
                            withSale) 
                        VALUES (
                            '$idUser', '$idSubcategory', '$name', '$description', 
                            '$hourlyCost', '$hourlyCurrency', '$dailyCost', '$dailyCurrency',
                            '$address', '$phone_1', '$phone_2','$phone_3', 
                            DATE_ADD(UTC_TIMESTAMP(), INTERVAL 60 DAY),
                            '$minTime', '$minDay', '$maxRentalPeriod',
                            '$timeOfIssueWith', '$timeOfIssueBy',
                            '$returnTimeWith', '$returnTimeBy',
                            '$withSale')";

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

                if (php_ini_loaded_file()) {
                    if (!empty($_FILES)) {
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
                            $result['code'] = UNKNOW_ERROR;
                            $result['error'] = $e->getMessage();
                        } finally {
                            if ($count == $counterLoadedPicture) {
                                $result['code'] = SUCCESS;
                            } else {
                                $result['code'] = PICTURES_DONT_LOAD;
                            }
                        }
                    } else {
                        $result['code'] = FILES_EMPTY;
                    }
                } else {
                    $result['code'] = PHP_INI_NOT_LOADED;
                }
            } else {
                $result['code'] = UNSUCCESS;
            }
        } else {
            $result['code'] = USER_NOT_FOUND;
        }
    } else {
        $result['code'] = UNKNOW_ERROR;
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
