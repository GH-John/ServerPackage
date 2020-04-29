<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);

    $result['pictures'] = array();

    $requestLoadPictures = "SELECT * FROM pictures WHERE idAnnouncement = '$idAnnouncement'";

    if ($connect) {
        $response = mysqli_query($connect, $loadAnnouncements);
        $rows = mysqli_num_rows($response);
        if ($rows > 0) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['pictures'], $row);
            }

            $result['response'] = "SUCCESS_PICTURES_LOADED";
        } else if ($rows == 0) {

            $result['response'] = "NONE_REZULT";
        } else {

            $result['response'] = mysqli_error($connect);
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
