<?php
define('SUCCESS', 2000);
define('UNSUCCESS', 2001);

define('NONE_REZULT', 2002);
define('NOT_CONNECT_TO_DB', 2003);
define('UNKNOW_ERROR', 2004);
define('PHP_INI_NOT_LOADED', 2005);

define('USER_WITH_LOGIN_EXISTS', 1800);
define('USER_EXISTS', 1801);
define('USER_NOT_FOUND', 1802);
define('WRONG_PASSWORD', 1803);
define('WRONG_EMAIL_LOGIN', 1804);

define('WRONG_TOKEN', 1805);

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
