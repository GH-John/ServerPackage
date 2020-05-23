<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);

    $loadRentDates = "SELECT idRent, idUser, idAnnouncement, 
    
    DATE_FORMAT(rentalStart, '%Y-%m-%d') AS 'dateStart',
    DATE_FORMAT(rentalStart, '%k:%i') AS 'timeStart',

    DATE_FORMAT(rentalEnd, '%Y-%m-%d') AS 'dateEnd',
    DATE_FORMAT(rentalEnd, '%k:%i') AS 'timeEnd'

    -- JSON_OBJECT(
    --     'day', DATE_FORMAT(rentalStart, '%e'),
    --     'month', DATE_FORMAT(rentalStart, '%c'),
    --     'year', DATE_FORMAT(rentalStart, '%Y')
    -- )AS 'dateStart',
    
    -- JSON_OBJECT(
    --     'hour', DATE_FORMAT(rentalStart, '%k'),
    --     'minute', DATE_FORMAT(rentalStart, '%i'),
    --     'nano', 0,
    --     'second', 0
    -- )AS 'timeStart',

    -- JSON_OBJECT(
    --     'day', DATE_FORMAT(rentalEnd, '%e'),
    --     'month', DATE_FORMAT(rentalEnd, '%c'),
    --     'year', DATE_FORMAT(rentalEnd, '%Y')
    -- )AS 'dateEnd',

    -- JSON_OBJECT(
    --     'hour', DATE_FORMAT(rentalEnd, '%k'),
    --     'minute', DATE_FORMAT(rentalEnd, '%i'),
    --     'nano', 0,
    --     'second', 0
    -- )AS 'timeEnd'

    FROM rent 
    WHERE idAnnouncement = '$idAnnouncement' AND isProposals IS FALSE";

    $response = mysqli_query($connect, $loadRentDates);

    $result['response'] = array();

    if ($connect) {
        if (mysqli_num_rows($response)) {
            while ($row = mysqli_fetch_assoc($response)) {
                // $row['dateStart'] = json_decode($row['dateStart']);
                // $row['timeStart'] = json_decode($row['timeStart']);

                // $row['dateEnd'] = json_decode($row['dateEnd']);
                // $row['timeEnd'] = json_decode($row['timeEnd']);

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
