<?php
$connect = mysqli_connect("localhost", "root", "12345678", "ArendaApp");

function getRow($connect, $rowName, $request)
{
    $response = mysqli_query($connect, $request);

    if (mysqli_num_rows($response)) {
        $row = mysqli_fetch_assoc($response);
        return $row[$rowName];
    }
    return "";
}
