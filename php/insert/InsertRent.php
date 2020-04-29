<?php
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $idUser = $_POST['idUser'];
        $idAnnouncement = $_POST['idAnnouncement'];
        $rentalStart = $_POST['rentalStart'];
        $rentalEnd = $_POST['rentalEnd'];

        require_once '../Utils.php';

        $sql = "INSERT INTO rent (idUser, idAnnouncement, rentalStart, rentalEnd) 
        VALUES ('$idUser', '$idAnnouncement', '$rentalStart', '$rentalEnd')";

        if(mysqli_query($connect, $sql)){
            $result["success"] = "1";
            $result["message"] = "SUCCESS: Add rent";

            echo json_encode($result);
            mysqli_close($connect);
        }else{
            $result["success"] = "0";
            $result["message"] = "ERROR: Add rent";

            echo json_encode($result);
            mysqli_close($connect);
        }
    }
