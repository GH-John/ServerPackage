<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idMessage = filter_var(trim($_POST['idMessage']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if ($idMessage == 0) {
        $loadMessages = "SELECT m.idMessage, m.idUser_To, m.message, m.created, m.updated,
                        u.userLogo, u.login
                        FROM messages m
                        INNER JOIN users u ON u.idUser = m.idUser_From

                        WHERE m.idUser_From = '$idUser' AND 
                        m.idMessage < '$idMessage'

                        ORDER BY m.idMessage DESC
                        LIMIT $limitItemInPage";
    } else {
        $loadMessages = "SELECT m.idMessage, m.idUser_To, m.message, m.created, m.updated,
                        u.userLogo, u.login
                        FROM chatRoom c
                        INNER JOIN messages m ON m.idRoom = c.idRoom
                        INNER JOIN users u ON u.idUser = m.idUser_From

                        WHERE m.idUser_From = '$idUser'

                        ORDER BY m.idMessage DESC
                        LIMIT $limitItemInPage";
    }
}
