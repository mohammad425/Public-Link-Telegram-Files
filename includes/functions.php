<?php

function Send($method, array $data)
{
    $url = APP_URL."/$method";
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode ($res);
}

function curPageURL($fullPageUrl = false) {
    $pageURL = 'http';
    if ($_SERVER['HTTPS'] == 'on')
        $pageURL .= 's';
    $pageURL .= '://';
    $pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $pageName = substr($pageURL,strrpos($pageURL,'/')+1);
    $pageGet = substr($pageName,strrpos($pageName,'?'));
    if($fullPageUrl)
        return str_replace($pageGet,'', $pageURL);
    return str_replace($pageName,'',$pageURL);
}

function save($fileID, $filePath){
    global  $folder,
            $chat_id,
            $e,
            $conn;

    $stmt = $conn->prepare('SELECT file_id FROM files WHERE file_id=:file_id');
    $stmt->execute(array(':file_id'=>$fileID));
    $count = $stmt->rowCount();

    if($count > 0){

        $sql = "SELECT * FROM files WHERE file_id ='$fileID' LIMIT 1";
        $q = $conn->query($sql);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        $file_id = $r['id'];
        $file_name = $r['file_name'];
        $file_dir = $r['file_dir'];
        $file_url = curPageURL() . "$file_dir/$file_name";

        $FileBtns = [
            [
                ['text' => $e['DownloadBtnText'], 'url' => $file_url]
            ],
            [
                ['text' => $e['DeleteBtnText'], 'callback_data' => 'delete*'.$file_id]
            ]
        ];

        return $FileBtns;

    }else{

        $dir = "$folder/$filePath/$chat_id";
        $URL = APP_URL."/getFile?file_id=$fileID";
        $a= file_get_contents($URL);
        $c= json_decode($a, true);
        $path = $c['result']['file_path'];
        $fileSize = $c['result']['file_size'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if(empty($extension)){
            $ext = substr($path,strrpos($path,'/'));
            $ext2 = str_replace($ext,'',$path);
            if($ext2 == 'video_notes'){
                $extension = 'mp4';
            }
            if($ext2 == 'voice'){
                $extension = 'ogg';
            }
        }
        $fileName = rand(10000000,9999999999) . ".$extension";  # Random Name
        $downloadPath = 'https://api.telegram.org/file/bot'.API_KEY."/$path";

        // Create Folder
        if(is_dir($dir)==false){
            if(!mkdir($dir, 0777, true)){
                Send('sendMessage',[
                    'chat_id'=>$chat_id,
                    'parse_mode'=>'HTML',
                    'text'=>$e['errorMakeFolder']
                ]);
            }
        }

        // Save File
        if(!copy($downloadPath, $fileName)){
            Send('sendMessage',[
                'chat_id'=>$chat_id,
                'parse_mode'=>'HTML',
                'text'=>$e['errorToSave']
            ]);

            //Move File To Folder
        }else if(!rename($fileName, "$dir/$fileName")) {
            Send('sendMessage',[
                'chat_id'=>$chat_id,
                'parse_mode'=>'HTML',
                'text'=>$e['errorToSave']
            ]);
        }else {

            $set = 'INSERT INTO `files` (`file_id`, `file_name`, `file_dir`, `file_size`, `user_id`, `date`) VALUES (:file_id, :file_name , :file_dir, :file_size, :user_id, :date)';
            $insert = $conn->prepare($set);
            $insert->execute(array(
                ':file_id'      =>      $fileID,
                ':file_name'    =>      $fileName,
                ':file_dir'     =>      $dir,
                ':file_size'    =>      $fileSize,
                ':user_id'      =>      $chat_id,
                ':date'         =>      time()
            ));

            $file_id = $conn->lastInsertId();
            $file_url = curPageURL() . "$dir/$fileName";

            $FileBtns = [
                [
                    ['text' => $e['DownloadBtnText'], 'url' => $file_url]
                ],
                [
                    ['text' => $e['DeleteBtnText'], 'callback_data' => 'delete*'.$file_id]
                ]
            ];

            return $FileBtns;
        }
    }

    return false;
}

function updateUserFiles($chatid, $delete = false){
    global $conn;

    $sql = "SELECT files FROM users WHERE chat_id ='$chatid' LIMIT 1";
    $q = $conn->query($sql);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    $oldFileCount = $r['files'];
    if($delete)
        $newFileCount = $oldFileCount-1;
    else
        $newFileCount = $oldFileCount+1;

    $Update = "UPDATE users SET files='$newFileCount' WHERE chat_id ='$chatid'";
    $ap = $conn->prepare($Update);
    $ap->execute();
}

function saveMessageIdFiles($fileID, $messageID){
    global $conn;

    $sql            =   "SELECT * FROM files WHERE file_id ='$fileID' LIMIT 1";
    $q              =   $conn->query($sql);
    $r              =   $q->fetch(PDO::FETCH_ASSOC);
    $oldMessageID   =   $r['message_id'];
    $newMessageID   =   "$messageID,$oldMessageID";
    if(empty($oldMessageID)){
        $Update     =   "UPDATE files SET message_id='$messageID' WHERE file_id ='$fileID'";
    }else{
        $Update     =   "UPDATE files SET message_id='$newMessageID' WHERE file_id ='$fileID'";
    }
    $ap             =   $conn->prepare($Update);
    $ap->execute();

}

function editMessageFileDeleted($userID, $messageID){
    global $e;

    $message_id = explode(',', $messageID);
    $count = count($message_id);

    for($i = 0; $i < $count; $i++){
        Send('editMessageText',[
            'chat_id'           =>  $userID,
            'message_id'        =>  $message_id[$i],
            'parse_mode'        =>  'HTML',
            'text'              =>  $e['FileIsRemoved']
        ]);
    }
}