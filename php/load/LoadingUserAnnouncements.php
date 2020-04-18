<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = $_POST['token'];
    $searchQuery = filter_var(trim($_POST['search']), FILTER_SANITIZE_STRING);
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemInPage']), FILTER_SANITIZE_STRING);

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    if (count($searchQuery) > 0 && $searchQuery != null && $idAnnouncement == 0) {
        $lastAnnouncement = getRow($connect, 'idAnnouncement', "SELECT max(idAnnouncement) AS idAnnouncement FROM announcements");

        $loadAnnouncements = "SELECT idAnnouncement, idUser, announcements.name, costToBYN, 
        costToUSD, costToEUR, address, placementDate, countRent, countViewers, countFavorites, rating
        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN photo ON announcement.idAnnouncement = photo.idAnnouncement AND photo.isMainPhoto = '1'

        WHERE (UPPER(announcements.name) LIKE '%$searchQuery%') 
        OR (UPPER(subcategories.name) LIKE '%$searchQuery%') 
        OR (UPPER(categories.name) LIKE '%$searchQuery%')
        AND idUser = '$idUser'
        AND announcements.idAnnouncement <= '$lastAnnouncement'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else if (count($searchQuery) > 0 && $searchQuery != null && $idAnnouncement > 0) {
        $loadAnnouncements = "SELECT idAnnouncement, idUser, announcements.name, costToBYN, 
        costToUSD, costToEUR, address, placementDate, countRent, countViewers, countFavorites, rating
        FROM announcements 
        
        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN photo ON announcement.idAnnouncement = photo.idAnnouncement AND photo.isMainPhoto = '1'

        WHERE (UPPER(announcements.name) LIKE '%$searchQuery%') 
        OR (UPPER(subcategories.name) LIKE '%$searchQuery%') 
        OR (UPPER(categories.name) LIKE '%$searchQuery%')
        AND idUser = '$idUser'
        AND announcements.idAnnouncement < '$idAnnouncement'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else if ($idAnnouncement == 0) {
        $lastAnnouncement = getRow($connect, 'idAnnouncement', "SELECT max(idAnnouncement) AS idAnnouncement FROM announcements");

        $loadAnnouncements = "SELECT idAnnouncement, idUser, name, costToBYN, costToUSD, costToEUR, 
        address, placementDate, countRent, countViewers, countFavorites, rating
        FROM announcements 

        INNER JOIN photo ON announcement.idAnnouncement = photo.idAnnouncement AND photo.isMainPhoto = '1'

        WHERE announcements.idAnnouncement <= '$lastAnnouncement' AND idUser = '$idUser'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else {
        $loadAnnouncements = "SELECT idAnnouncement, idUser, name, costToBYN, costToUSD, costToEUR, 
        address, placementDate, countRent, countViewers, countFavorites, rating
        FROM announcements 

        INNER JOIN photo ON announcement.idAnnouncement = photo.idAnnouncement AND photo.isMainPhoto = '1'

        WHERE announcements.idAnnouncement < '$idAnnouncement' AND idUser = '$idUser'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
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
