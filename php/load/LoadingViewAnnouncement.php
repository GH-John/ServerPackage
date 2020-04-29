<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $loadAnnouncement = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        announcements.idSubcategory, announcements.description, announcements.phone_1, announcements.phone_2, announcements.phone_3, 
        users.login, users.userLogo, costToBYN, costToUSD, costToEUR, address, 
        placementDate, announcements.countRent, announcements.rating, announcements.countReviews, announcements.countFavorites, 
        announcements.countViewers, pictures.picture, IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'
        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        INNER JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement AND pictures.isMainPicture = '1'

        LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))            

        WHERE announcements.idAnnouncement = '$idAnnouncement'
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";

    $loadingUris = "SELECT picture, isMainPicture FROM pictures WHERE idAnnouncement = '$idAnnouncement'";

    $checkViewer = getRow($connect, 'idUser', "SELECT idUser FROM viewers 
    WHERE idUser = '$idUser'   
    AND idAnnouncement = '$idAnnouncement'");

    $insertViewer = "INSERT INTO viewers (idUser, idAnnouncement) 
    VALUES ('$idUser', '$idAnnouncement')";

    $result['announcement'] = array();
    $result['pictures'] = array();

    if ($connect) {
        if (!$checkViewer) {
            mysqli_query($connect, $insertViewer);
        }

        $response = mysqli_query($connect, $loadAnnouncement);

        $responseUris = mysqli_query($connect, $loadingUris);

        if ($response) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['announcement'], $row);
            }

            while ($row = mysqli_fetch_assoc($responseUris)) {
                array_push($result['pictures'], $row);
            }

            $result['response'] = "SUCCESS_ANNOUNCEMENT_LOADED";
        } else {
            $result['response'] = "UNSUCCESS_ANNOUNCEMENT_LOADED";
        }
    } else {
        $result['response'] = "NOT_CONNECT_TO_DB";
    }
    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
