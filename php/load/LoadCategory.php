<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $loadCategory = "SELECT idCategory, name, iconUri FROM categories";
    $response = mysqli_query($connect, $loadCategory);

    $result['categories'] = array();

    if ($connect) {
        if (mysqli_num_rows($response)) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['categories'], $row);
            }

            $result['response'] = "SUCCESS_CATEGORIES_LOADED";
        } else {
            $result['response'] = "UNSUCCESS_CATEGORIES_LOADED";
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
