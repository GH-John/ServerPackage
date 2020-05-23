<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../Utils.php';

    $loadCategory = "SELECT idCategory, name, iconUri FROM categories";

    $result['response'] = array();

    if ($connect) {
        $response = mysqli_query($connect, $loadCategory);
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
