<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);
    $search = filter_var(trim($_POST['search']), FILTER_SANITIZE_STRING);

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if (count($search) > 0 && $search != null) {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        costToBYN, costToUSD, costToEUR, address, placementDate, countRent, rating, photoPath, 
        IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'        
        FROM announcements 
        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))
        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        WHERE (announcements.name LIKE '%$search%') 
        OR (subcategories.name LIKE '%$search%') 
        OR (categories.name LIKE '%$search%') ";
    } else {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        costToBYN, costToUSD, costToEUR, address, placementDate, countRent, rating, photoPath, 
        IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'
        FROM announcements
        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))
        WHERE announcements.idAnnouncement > '$idAnnouncement'";
    }

    $result['announcements'] = array();

    if ($connect) {
        $response = mysqli_query($connect, $loadAnnouncements);
        $rows = mysqli_num_rows($response);
        if ($rows > 0) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['announcements'], $row);
            }

            $result['code'] = "1";
            $result['message'] = "SUCCESS: Announcements loaded";
        } else if ($rows == 0) {
            $result['code'] = "3";
            $result['message'] = "SUCCESS: Announcements result = 0";
        } else {
            $result['code'] = "2";
            $result['message'] = mysqli_error($connect);
        }
    } else {
        $result['code'] = "101";
        $result['message'] = "ERROR: Could not connect to DB";
    }
    echo json_encode($result);
    mysqli_close($connect);
}
