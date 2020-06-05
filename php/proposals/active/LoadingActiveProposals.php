<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idRent = filter_var(trim($_POST['idRent']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if ($idRent > 0) {
        $loadProposals = "SELECT r.idRent, r.idAnnouncement, r.rentalStart, r.rentalEnd, 
                        r.created, r.updated, r.isClosed,
                        (SELECT picture FROM pictures p 
                        WHERE p.idAnnouncement = a.idAnnouncement 
                        AND p.isMainPicture IS TRUE) picture,

                        IF((a.idUser = '$idUser'), 1, 0) isIncoming,
                        u.idUser, u.userLogo, u.login 

                        FROM rent r                    

                        INNER JOIN announcements a ON a.idAnnouncement = r.idAnnouncement
                        INNER JOIN users u ON (u.idUser = r.idUser AND a.idUser = '$idUser') OR
                                    (u.idUser = a.idUser AND r.idUser = '$idUser')

                        WHERE (r.isProposals IS FALSE AND r.isClosed IS FALSE)
                        AND (a.idUser = '$idUser' OR r.idUser = '$idUser')
                        AND r.idRent < '$idRent'

                        ORDER BY r.idRent DESC
                        LIMIT $limitItemInPage";
    } else if ($idRent == 0) {
        $loadProposals = "SELECT r.idRent, r.idAnnouncement, r.rentalStart, r.rentalEnd, 
                        r.created, r.updated, r.isClosed,
                        (SELECT picture FROM pictures p 
                        WHERE p.idAnnouncement = a.idAnnouncement 
                        AND p.isMainPicture IS TRUE) picture,

                        IF((a.idUser = '$idUser'), 1, 0) isIncoming,
                        u.idUser, u.userLogo, u.login 

                        FROM rent r                    

                        INNER JOIN announcements a ON a.idAnnouncement = r.idAnnouncement
                        INNER JOIN users u ON (u.idUser = r.idUser AND a.idUser = '$idUser') OR
                                    (u.idUser = a.idUser AND r.idUser = '$idUser')

                        WHERE (r.isProposals IS FALSE AND r.isClosed IS FALSE)
                        AND (a.idUser = '$idUser' OR r.idUser = '$idUser')

                        ORDER BY r.idRent DESC
                        LIMIT $limitItemInPage";
    }

    $result['response'] = array();

    if ($connect) {
        $response = mysqli_query($connect, $loadProposals);
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
        $result['code'] = NOT_CONNECT_TO_DB;
    }
    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
