<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    $loadAnnouncement = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, description, 
    announcements.idSubcategory, announcements.rating, countRent, statusRent, address, phone_1, phone_2, phone_3, 
    isVisible_phone_1, isVisible_phone_2, isVisible_phone_3, costToBYN, costToUSD, costToEUR, placementDate,
    IFNULL(favoriteAnnouncements.isFavorite, '0') AS 'isFavorite'
    FROM announcements 
    LEFT JOIN favoriteAnnouncements ON (('$idUser' = favoriteAnnouncements.idUser) 
            AND (announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement))
    INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory
    LEFT JOIN reviews ON announcements.idAnnouncement = reviews.idAnnouncement
    WHERE announcements.idAnnouncement = '$idAnnouncement'";

    $loadingUris = "SELECT photoPath FROM photo
        WHERE idAnnouncement = '$idAnnouncement'";

    $result['announcement'] = array();
    $result['uris'] = array();

    if ($connect) {
        $requestUpdate = "UPDATE announcements SET countViewers = countViewers + 1
            WHERE announcements.idAnnouncement = '$idAnnouncement'";

        $updateViewer = mysqli_query($connect, $requestUpdate);

        $response = mysqli_query($connect, $loadAnnouncement);

        $responseUris = mysqli_query($connect, $loadingUris);

        if ($response) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['announcement'], $row);
            }

            while ($row = mysqli_fetch_assoc($responseUris)) {
                array_push($result['uris'], $row);
            }

            $result['code'] = "1";
            $result['message'] = "SUCCESS: Announcement loaded";
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
