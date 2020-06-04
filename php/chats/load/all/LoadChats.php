<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idChat = filter_var(trim($_POST['idChat']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if ($idChat == 0) {
        $loadChats = "SELECT c.idChat, u.login, u.userLogo FROM chats c 
        ON ((c.idUser_To = '$idUser' AND u.idUser = c.idUser_To) OR 
                (c.idUser_From = '$idUser' AND u.idUser = c.idUser_From))

        INNER JOIN messages m ON m.idChat = c.idChat       
        
        GROUP BY c.idChat, u.idUser
        ORDER BY c.idChat DESC
        LIMIT $limitItemInPage";
    } else {
        $loadChats = "SELECT c.idChat, u.login, u.userLogo FROM chats c 
                ON ((c.idUser_To = '$idUser' AND u.idUser = c.idUser_To) OR 
                (c.idUser_From = '$idUser' AND u.idUser = c.idUser_From))

        INNER JOIN messages m ON m.idChat = c.idChat       
        
        WHERE c.idChat < '$idChat'

        GROUP BY c.idChat, u.idUser
        ORDER BY c.idChat DESC

        LIMIT $limitItemInPage";
    }

    if ($connect) {
        if ($idUser) {
            $response = mysqli_query($connect, $loadChats);
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
