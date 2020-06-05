<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idUser_To = filter_var(trim($_POST['idUser_To']), FILTER_SANITIZE_STRING);
    $idMessageAfter = filter_var(trim($_POST['idMessageAfter']), FILTER_SANITIZE_STRING);
    $idMessageBefore = filter_var(trim($_POST['idMessageBefore']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");
    $idUser_To = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE idUser = '$idUser_To'");

    if ($idMessageAfter == 0 && $idMessageBefore == 0) {
        $loadMessages = "SELECT m.idMessage, m.message, m.created, m.updated,
                        (
                            CASE
                                WHEN m.idUser_From = '$idUser' AND m.idUser_To = '$idUser' THEN 0
                                WHEN m.idUser_From = '$idUser' THEN 0
                                ELSE 1
                            END
                        ) typeMessage

                        FROM messages m
                        INNER JOIN chats c ON c.idChat = m.idChat

                        WHERE ((m.idUser_To = '$idUser' AND m.idUser_From = '$idUser_To') OR 
                                (m.idUser_To = '$idUser_To' AND m.idUser_From = '$idUser'))

                        ORDER BY m.idMessage
                        LIMIT $limitItemInPage";
    } else if ($idMessageAfter > 0 && $idMessageBefore == 0) {
        $loadMessages = "SELECT m.idMessage, m.message, m.created, m.updated,
                        (
                            CASE
                                WHEN m.idUser_From = '$idUser' AND m.idUser_To = '$idUser' THEN 0
                                WHEN m.idUser_From = '$idUser' THEN 0
                                ELSE 1
                            END
                        ) typeMessage

                        FROM messages m
                        INNER JOIN chats c ON c.idChat = m.idChat

                        WHERE ((m.idUser_To = '$idUser' AND m.idUser_From = '$idUser_To') OR 
                                (m.idUser_To = '$idUser_To' AND m.idUser_From = '$idUser')) AND 
                        m.idMessage > '$idMessageAfter'

                        ORDER BY m.idMessage
                        LIMIT $limitItemInPage";
    } else if ($idMessageAfter == 0 && $idMessageBefore > 0) {
        $loadMessages = "SELECT m.idMessage, m.message, m.created, m.updated,
                        (
                            CASE
                                WHEN m.idUser_From = '$idUser' AND m.idUser_To = '$idUser' THEN 0
                                WHEN m.idUser_From = '$idUser' THEN 0
                                ELSE 1
                            END
                        ) typeMessage

                        FROM messages m
                        INNER JOIN chats c ON c.idChat = m.idChat

                        WHERE ((m.idUser_To = '$idUser' AND m.idUser_From = '$idUser_To') OR 
                                (m.idUser_To = '$idUser_To' AND m.idUser_From = '$idUser') OR
                                (m.idUser_To = '$idUser' AND m.idUser_From = '$idUser')) AND 
                        m.idMessage < '$idMessageBefore'

                        ORDER BY m.idMessage 
                        LIMIT $limitItemInPage";
    } else if ($idMessageAfter > 0 && $idMessageBefore > 0) {
        $loadMessages = "SELECT m.idMessage, m.message, m.created, m.updated,
                        (
                            CASE
                                WHEN m.idUser_From = '$idUser' AND m.idUser_To = '$idUser' THEN 0
                                WHEN m.idUser_From = '$idUser' THEN 0
                                ELSE 1
                            END
                        ) typeMessage

                        FROM messages m
                        INNER JOIN chats c ON c.idChat = m.idChat

                        WHERE ((m.idUser_To = '$idUser' AND m.idUser_From = '$idUser_To') OR 
                                (m.idUser_To = '$idUser_To' AND m.idUser_From = '$idUser')) AND 
                        (m.idMessage > '$idMessageAfter' AND m.idMessage < '$idMessageBefore')

                        ORDER BY m.idMessage 
                        LIMIT $limitItemInPage";
    }

    $result['response'] = array();

    if ($connect) {
        if ($idUser) {
            if ($idUser_To) {
                $response = mysqli_query($connect, $loadMessages);
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
                $result['code'] = RECIPIENT_NOT_FOUND;
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
