<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = $_POST['idAnnouncement'];
    $rentalStart = $_POST['rentalStart'];
    $rentalEnd = $_POST['rentalEnd'];

    require_once '../../Utils.php';

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $request = "INSERT INTO rent (idUser, idAnnouncement, rentalStart, rentalEnd) 
        VALUES ('$idUser', '$idAnnouncement', '$rentalStart', '$rentalEnd')";

    if ($connect) {
        if ($idUser) {
            if (mysqli_query($connect, $request)) {
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
