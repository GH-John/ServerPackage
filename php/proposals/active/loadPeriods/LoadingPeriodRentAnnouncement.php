<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);

    $loadRentDates = "SELECT idRent, idUser, idAnnouncement, 
    
    DATE_FORMAT(rentalStart, '%Y-%m-%d') AS 'dateStart',
    DATE_FORMAT(rentalStart, '%k:%i') AS 'timeStart',

    DATE_FORMAT(rentalEnd, '%Y-%m-%d') AS 'dateEnd',
    DATE_FORMAT(rentalEnd, '%k:%i') AS 'timeEnd'

    FROM rent 
    WHERE idAnnouncement = '$idAnnouncement' AND isProposals IS FALSE";

    $response = mysqli_query($connect, $loadRentDates);

    $result['response'] = array();

    if ($connect) {
        if (mysqli_num_rows($response)) {
            while ($row = mysqli_fetch_assoc($response)) {
                array_push($result['response'], $row);
            }

            $result['code'] = SUCCESS;
        } else {
            $result['code'] = UNSUCCESS;
        }
    } else {
        $result['code'] = NOT_CONNECT_TO_DB;
    }

    $result['error'] = mysqli_error($connect);

    echo json_encode($result);
    mysqli_close($connect);
}
