<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../../../Utils.php';

    $token = filter_var(trim($_POST['token']), FILTER_SANITIZE_STRING);
    $idAnnouncement = filter_var(trim($_POST['idAnnouncement']), FILTER_SANITIZE_STRING);
    $idSubcategory = filter_var(trim($_POST['idSubcategory']), FILTER_SANITIZE_STRING);
    $searchQuery = filter_var(trim($_POST['query']), FILTER_SANITIZE_STRING);
    $limitItemInPage = filter_var(trim($_POST['limitItemsInPage']), FILTER_SANITIZE_STRING);

    if ($limitItemInPage == 0)
        $limitItemInPage = 10;

    $idUser = getRow($connect, 'idUser', "SELECT idUser FROM users WHERE token = '$token'");

    if (count($searchQuery) > 0 && $searchQuery != null && $idAnnouncement == 0) {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        announcements.idSubcategory, announcements.description, announcements.phone_1, announcements.phone_2, announcements.phone_3,     
        costToBYN, costToUSD, costToEUR, address, 
        announcements.created AS announcementCreated, announcements.updated AS announcementUpdated, 
        announcements.countRent, announcements.rating AS announcementRating, 
        announcements.countReviews, announcements.countFavorites, announcements.countViewers, 

        users.login, users.userLogo, users.created AS userCreated, users.rating AS userRating, users.countAnnouncementsUser,

        JSON_ARRAYAGG(
            JSON_OBJECT(
                'idPicture', pictures.idPicture,
                'idAnnouncement', pictures.idAnnouncement,
                'picture', pictures.picture, 
                'isMainPicture', CASE WHEN pictures.isMainPicture = 0 THEN 'false' ELSE 'true' END
            )
        ) AS 'pictures',

        IFNULL((SELECT 
                    CASE 
                        WHEN isFavorite = 0 THEN 'false'
                        ELSE 'true'
                    END 
                FROM favoriteAnnouncements 
                WHERE favoriteAnnouncements.idUser = '$idUser' 
                AND announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement), 
        'false') AS 'isFavorite'

        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        LEFT JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement         

        WHERE (UPPER(announcements.name) LIKE '%$searchQuery%') 
        OR (UPPER(subcategories.name) LIKE '%$searchQuery%') 
        OR (UPPER(categories.name) LIKE '%$searchQuery%')
        AND categories.idCategory = subcategories.idCategory
        AND announcements.idSubcategory = '$idSubcategory'

        GROUP BY announcements.idAnnouncement
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else if (count($searchQuery) > 0 && $searchQuery != null && $idAnnouncement > 0) {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        announcements.idSubcategory, announcements.description, announcements.phone_1, announcements.phone_2, announcements.phone_3,     
        costToBYN, costToUSD, costToEUR, address, 
        announcements.created AS announcementCreated, announcements.updated AS announcementUpdated, 
        announcements.countRent, announcements.rating AS announcementRating, 
        announcements.countReviews, announcements.countFavorites, announcements.countViewers, 

        users.login, users.userLogo, users.created AS userCreated, users.rating AS userRating, users.countAnnouncementsUser,
        
        JSON_ARRAYAGG(
            JSON_OBJECT(
                'idAnnouncement', pictures.idAnnouncement,
                'picture', pictures.picture, 
                'isMainPicture', CASE WHEN pictures.isMainPicture = 0 THEN 'false' ELSE 'true' END
            )
        ) AS 'pictures',

        IFNULL((SELECT 
                    CASE 
                        WHEN isFavorite = 0 THEN 'false'
                        ELSE 'true'
                    END 
                FROM favoriteAnnouncements 
                WHERE favoriteAnnouncements.idUser = '$idUser' 
                AND announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement), 
        'false') AS 'isFavorite'

        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        LEFT JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement

        WHERE (UPPER(announcements.name) LIKE '%$searchQuery%') 
        OR (UPPER(subcategories.name) LIKE '%$searchQuery%') 
        OR (UPPER(categories.name) LIKE '%$searchQuery%')
        AND announcements.idAnnouncement < '$idAnnouncement'
        AND categories.idCategory = subcategories.idCategory
        AND announcements.idSubcategory = '$idSubcategory'

        GROUP BY announcements.idAnnouncement
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else if ($idAnnouncement == 0) {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        announcements.idSubcategory, announcements.description, announcements.phone_1, announcements.phone_2, announcements.phone_3,     
        costToBYN, costToUSD, costToEUR, address, 
        announcements.created AS announcementCreated, announcements.updated AS announcementUpdated, 
        announcements.countRent, announcements.rating AS announcementRating, 
        announcements.countReviews, announcements.countFavorites, announcements.countViewers, 

        users.login, users.userLogo, users.created AS userCreated, users.rating AS userRating, users.countAnnouncementsUser,

        JSON_ARRAYAGG(
            JSON_OBJECT(
                'idAnnouncement', pictures.idAnnouncement,
                'picture', pictures.picture, 
                'isMainPicture', CASE WHEN pictures.isMainPicture = 0 THEN 'false' ELSE 'true' END
            )
        ) AS 'pictures',

        IFNULL((SELECT 
                    CASE 
                        WHEN isFavorite = 0 THEN 'false'
                        ELSE 'true'
                    END 
                FROM favoriteAnnouncements 
                WHERE favoriteAnnouncements.idUser = '$idUser' 
                AND announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement), 
        'false') AS 'isFavorite'

        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        LEFT JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement 

        WHERE categories.idCategory = subcategories.idCategory
        AND announcements.idSubcategory = '$idSubcategory'

        GROUP BY announcements.idAnnouncement
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    } else {
        $loadAnnouncements = "SELECT announcements.idAnnouncement, announcements.idUser, announcements.name, 
        announcements.idSubcategory, announcements.description, announcements.phone_1, announcements.phone_2, announcements.phone_3,     
        costToBYN, costToUSD, costToEUR, address, 
        announcements.created AS announcementCreated, announcements.updated AS announcementUpdated, 
        announcements.countRent, announcements.rating AS announcementRating, 
        announcements.countReviews, announcements.countFavorites, announcements.countViewers, 

        users.login, users.userLogo, users.created AS userCreated, users.rating AS userRating, users.countAnnouncementsUser,

        JSON_ARRAYAGG(
            JSON_OBJECT(
                'idAnnouncement', pictures.idAnnouncement,
                'picture', pictures.picture, 
                'isMainPicture', CASE WHEN pictures.isMainPicture = 0 THEN 'false' ELSE 'true' END
            )
        ) AS 'pictures',

        IFNULL((SELECT 
                    CASE 
                        WHEN isFavorite = 0 THEN 'false'
                        ELSE 'true'
                    END 
                FROM favoriteAnnouncements 
                WHERE favoriteAnnouncements.idUser = '$idUser' 
                AND announcements.idAnnouncement = favoriteAnnouncements.idAnnouncement), 
        'false') AS 'isFavorite'

        FROM announcements 

        INNER JOIN subcategories ON announcements.idSubcategory = subcategories.idSubcategory 
        INNER JOIN categories ON subcategories.idCategory = categories.idCategory 
        INNER JOIN users ON announcements.idUser = users.idUser
        LEFT JOIN pictures ON announcements.idAnnouncement = pictures.idAnnouncement      

        WHERE announcements.idAnnouncement < '$idAnnouncement'
        AND categories.idCategory = subcategories.idCategory
        AND announcements.idSubcategory = '$idSubcategory'

        GROUP BY announcements.idAnnouncement
        ORDER BY announcements.idAnnouncement DESC
        LIMIT $limitItemInPage";
    }

    $result['response'] = array();

    if ($connect) {
        $response = mysqli_query($connect, $loadAnnouncements);
        $rows = mysqli_num_rows($response);
        if ($rows > 0) {
            while ($row = mysqli_fetch_assoc($response)) {
                $row['pictures'] = json_decode($row['pictures']);

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
