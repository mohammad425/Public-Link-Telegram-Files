<?php

#===============================================================================
#-------------------------------- Settings -------------------------------------
#===============================================================================

/*  Database */
define('HOST','localhost'); # Database host name
define('DBNAME',''); # Database name
define('DBUSERNAME',''); # Database username
define('DBPASSWORD',''); # Database password

#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

/*  Telegram Bot API Key */
define('API_KEY','');  # Enter bot api token
define('APP_URL','https://api.telegram.org/bot'.API_KEY);   ## Don't edit this line ##

#===============================================================================
#-------------------------- Connect to database --------------------------------
#===============================================================================
try {
    $conn = new PDO('mysql:host='.HOST.';dbname='.DBNAME.';charset=utf8mb4', DBUSERNAME, DBPASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //Create tables
    include_once __DIR__ . '/tables.php';
    $conn->exec($filesTable);
    $conn->exec($usersTable);
}catch(PDOException $e) {
    file_put_contents('Error_log.txt', $e->getMessage());   # Save Errors in: 'Error_log.txt'
}

#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

/*  Folders */
$folder         =   'upload';   # Save All Files In Folder Name
$imageFolder    =   'image';    # Save Images In Folder Name
$videoFolder    =   'video';    # Save Videos In Folder Name
$musicFolder    =   'music';    # Save Musics In Folder Name
$fileFolder     =   'file';     # Save Other Files In Folder Name

#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

/*  Files Delete After... 
*   
*   You should enter in second, For example:
*   
*   60          =>      one minute
*   3600        =>      one hour
*   86400       =>      one day
*   172800      =>      two days
*   604800      =>      one week
*   
*/
$FilesDeleteAfter = 60;

#\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

/* lang */
$lang = 'fa';

#/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

include_once __DIR__ . "/langs/$lang.php";