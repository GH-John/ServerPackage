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

            $result['code'] = "1";
            $result['message'] = "SUCCESS: Subcategories loaded";
        } else {
            $result['code'] = "2";
            $result['message'] = "ERROR: Load Subcategories";
        }
    } else {
        $result["code"] = "101";
        $result["message"] = "ERROR: Could not connect to DB";
    }
    echo json_encode($result);
    mysqli_close($connect);
}
