<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);
    $searchQuery = filter_var(trim($_POST['query']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if (count($searchQuery) > 0 && $searchQuery != null && $idAnnouncement == 0) {
        $lastAnnouncement = getRow($connect, 'idAnnouncement', "SELECT max(idAnnouncement) AS idAnnouncement FROM announcements");

        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        users.login, users.userLogo, costToBYN, costToUSD, costToEUR, address, 
        placementDate, countRent, announcements.rating, announcements.countReviews, pictures.picture,
        IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'
        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        INNER JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement AND pictures.isMainPicture = '1'

        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))            

        WHERE (UPPER(announcements.name) LIKE '%$searchQuery%') 
        OR (UPPER(subcategories.name) LIKE '%$searchQuery%') 
        OR (UPPER(categories.name) LIKE '%$searchQuery%')
        AND announcements.idAnnouncement <= '$lastAnnouncement'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else if (count($searchQuery) > 0 && $searchQuery != null && $idAnnouncement > 0) {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        users.login, users.userLogo, costToBYN, costToUSD, costToEUR, address,
         placementDate, countRent, announcements.rating, announcements.countReviews, pictures.picture,
        IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'        
        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        INNER JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement AND pictures.isMainPicture = '1'

        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))

        WHERE (UPPER(announcements.name) LIKE '%$searchQuery%') 
        OR (UPPER(subcategories.name) LIKE '%$searchQuery%') 
        OR (UPPER(categories.name) LIKE '%$searchQuery%')
        AND announcements.idAnnouncement < '$idAnnouncement'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else if ($idAnnouncement == 0) {
        $lastAnnouncement = getRow($connect, 'idAnnouncement', "SELECT max(idAnnouncement) AS idAnnouncement FROM announcements");

        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        users.login, users.userLogo, costToBYN, costToUSD, costToEUR, address, 
        placementDate, countRent, announcements.rating, announcements.countReviews, pictures.picture,
        IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'        
        FROM announcements 

        INNER JOIN users ON announcements.idUser = users.idUser
        INNER JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement AND pictures.isMainPicture = '1'

        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))

        WHERE announcements.idAnnouncement <= '$lastAnnouncement'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        users.login, users.userLogo, costToBYN, costToUSD, costToEUR, address, 
        placementDate, countRent, announcements.rating, announcements.countReviews, pictures.picture,
        IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'        
        FROM announcements 

        INNER JOIN users ON announcements.idUser = users.idUser
        INNER JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement AND pictures.isMainPicture = '1'

        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))        

        WHERE announcements.idAnnouncement < '$idAnnouncement'
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

            $result['response'] = "SUCCESS_ANNOUNCEMENTS_LOADED";
        } else if ($rows == 0) {

            $result['response'] = "NONE_REZULT";
        } else {

            $result['response'] = mysqli_error($connect);
        }
    } else {
        $result['error'] = "NOT_CONNECT_TO_DB";
    }
    echo json_encode($result);
    mysqli_close($connect);
}
