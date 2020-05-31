<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idUser_To = filter_var(trim($_POST['idUser_To']), FILTER_SANITIZE_STRING);

    $idUser_From = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $loadRoom = "SELECT idRoom FROM chatRoom 
                WHERE (idUser_From = '$idUser_From' AND idUser_To = '$idUser_To') 
                OR 
                (idUser_From = '$idUser_To' AND idUser_To = '$idUser_From')";

    if ($connect) {

        if ($idUser_From) {
            $response = mysqli_query($connect, $loadRoom);
            $rows =  mysqli_num_rows($response);

            if ($rows) {
                $result['response'] = mysqli_fetch_assoc($response);
                $result['code'] = SUCCESS;
            } else {
                $result['code'] = NONE_REZULT;
            }
        } else {
            $result['code'] = USER_NOT_FOUND;
        }

        $response = mysqli_query($connect, $loadRoom);
        $rows = mysqli_num_rows($response);
        if ($rows > 0) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['response'], $row);
            }

            $result['code'] = SUCCESS;
        } else if ($rows == 0) {

            $result['code'] = NONE_REZULT;
        } else {
            $result['code'] = UNKNOW_ERROR;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }
    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
