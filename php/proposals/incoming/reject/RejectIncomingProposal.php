<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idRent = filter_var(trim($_POST['idRent']), FILTER_SANITIZE_STRING);

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");
    $isProposalExist = getRow($connect, 'isExist', "SELECT EXISTS(SELECT idRent FROM rent r 
                                                    INNER JOIN announcements a ON r.idAnnouncement = a.idAnnouncement 
                                                    WHERE r.idRent = '$idRent' AND a.idUser = '$idUser') isExist");

    $rejectProposal = "DELETE FROM rent r WHERE idRent = '$idRent'";


    $result['response'] = array();

    if ($connect) {
        if ($idUser) {
            if ($isProposalExist) {
                if (mysqli_query($connect, $rejectProposal)) {
                    $result['code'] = SUCCESS;
                } else {
                    $result['code'] = UNSUCCESS;
                }
            } else {
                $result['code'] = PROPOSAL_NOT_FOUND;
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
