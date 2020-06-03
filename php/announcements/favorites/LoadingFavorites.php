<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idFavorite = filter_var(trim($_POST['idFavorite']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if ($idFavorite == 0) {
        $loadAnnouncements = "SELECT fa.idFavorite, a.idAnnouncement, a.idUser, a.name,           
        a.hourlyCost, a.hourlyCurrency, a.dailyCost, a.dailyCurrency, a.address,
        a.created AS announcementCreated, a.updated AS announcementUpdated,
            (SELECT picture FROM pictures pic
                WHERE isMainPicture IS TRUE
             AND pic.idAnnouncement = a.idAnnouncement
            ) picture,
        u.login, u.userLogo,
        fa.isFavorite

        FROM favoriteAnnouncements fa

        INNER JOIN announcements a ON a.idAnnouncement = fa.idAnnouncement
        INNER JOIN users u ON u.idUser = a.idUser
        LEFT JOIN pictures p ON p.idAnnouncement = a.idAnnouncement
		
        WHERE fa.idUser = '$idUser' AND fa.isFavorite IS TRUE
        
        GROUP BY fa.idAnnouncement, fa.idFavorite
        ORDER BY fa.idFavorite DESC
        LIMIT $limitItemInPage";
    } else {
        $loadAnnouncements = "SELECT fa.idFavorite, a.idAnnouncement, a.idUser, a.name,
        a.hourlyCost, a.hourlyCurrency, a.dailyCost, a.dailyCurrency, a.address,
        a.created AS announcementCreated, a.updated AS announcementUpdated,
            (SELECT picture FROM pictures pic
                WHERE isMainPicture IS TRUE
             AND pic.idAnnouncement = a.idAnnouncement
            ) picture,
        u.login, u.userLogo,
        fa.isFavorite

        FROM favoriteAnnouncements fa

        INNER JOIN announcements a ON a.idAnnouncement = fa.idAnnouncement
        INNER JOIN users u ON u.idUser = a.idUser
        LEFT JOIN pictures p ON p.idAnnouncement = a.idAnnouncement

        WHERE fa.idFavorite < '$idFavorite' AND fa.idUser = '$idUser' AND fa.isFavorite IS TRUE

        GROUP BY fa.idAnnouncement, fa.idFavorite
        ORDER BY fa.idFavorite DESC
        LIMIT $limitItemInPage";
    }

    $result['response'] = array();

    if ($connect) {
        if ($idUser) {
            $response = mysqli_query($connect, $loadAnnouncements);
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
            $result['code'] = USER_NOT_FOUND;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }
    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
