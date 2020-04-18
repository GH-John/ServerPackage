<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $idCategory = filter_var(trim($_POST['idCategory']), FILTER_SANITIZE_STRING);

    $loadSubcategory = "SELECT subcategories.idSubcategory as idSubcategory, subcategories.name as name 
        FROM subcategories INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        WHERE categories.idCategory = $idCategory";

    $response = mysqli_query($connect, $loadSubcategory);

    $result['subcategories'] = array();

    if ($connect) {
        if (mysqli_num_rows($response)) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['subcategories'], $row);
            }

            $result['response'] = "SUCCESS_SUBCATEGORIES_LOADED";
        } else {
            $result['response'] = "UNSUCCESS_SUBCATEGORIES_LOADED";
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }
    echo json_encode($result);
    mysqli_close($connect);
}
