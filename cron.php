<?php

include_once __DIR__ . '/includes/settings.php';

#===============================================================================
#------------------------------ Check Files ------------------------------------
#===============================================================================

function deleteUserFilesCount($userID){
    global $conn;
    
    $sql = "SELECT files FROM users WHERE chat_id ='$userID' LIMIT 1";
    $q = $conn->query($sql);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $oldFileCount = $r['files'];
    $newFileCount = $oldFileCount-1;
    $Update = "UPDATE users SET files='$newFileCount' WHERE chat_id ='$userID'";
    $ap = $conn->prepare($Update);
    $ap->execute();
}

$sqlCron = 'SELECT * FROM files';
$qCron = $conn->query($sqlCron);
$rCron = $qCron->setFetchMode(PDO::FETCH_ASSOC);
while ( $rCron = $qCron->fetch() )
{
    $fileDate   =   $rCron['date'];
    $allowTime  =   $fileDate + $FilesDeleteAfter;
    
    if ( time() >= $allowTime){
        
        $fileID     =   $rCron['id'];
        $fileDir    =   $rCron['file_dir'];
        $fileName   =   $rCron['file_name'];
        $userID     =   $rCron['user_id'];
        $Delete     =   "DELETE FROM files WHERE id=$fileID";
        
        $deleteFilesMessageID   .=   $rCron['message_id'] . ',';
            
        unlink("$fileDir/$fileName");  # Delete file in saved folder
        $conn->exec($Delete);                  # Delete file in database
        deleteUserFilesCount($userID);         # Update user files count
        
    }
}

$DELETEFILES = true;
include_once __DIR__ . '/index.php';  # Edit user message