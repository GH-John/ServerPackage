<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $loadCategory = "SELECT idCategory, name FROM categories";
    $response = mysqli_query($connect, $loadCategory);

    $result['categories'] = array();

    if ($connect) {
        if (mysqli_num_rows($response)) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['categories'], $row);
            }

            $result['code'] = "1";
            $result['message'] = "SUCCESS: Categories loaded";
        } else {
            $result['code'] = "2";
            $result['message'] = "ERROR: Load categories";
        }
    } else {
        $result["code"] = "101";
        $result["message"] = "ERROR: Could not connect to DB";
    }
    echo json_encode($result);
    mysqli_close($connect);
}
