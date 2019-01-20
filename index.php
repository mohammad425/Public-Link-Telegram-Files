<?php
/*
*   Bot Name: Public Link Telegram Files
*   Bot Description: Generate public links for your telegram files
*   Bot Version: 1.0
*   Bot Url: https://github.com/mohammad425/Public-Link-Telegram-Files
*   Author Name: Mir Mohammad Hosseini
*   Author Url: https://tasar.ir
*   Date Created: 2019/01/20
*   Date Updated: 2019/01/20
*/

$INPUT      =   file_get_contents('php://input');
$update     =   json_decode($INPUT);
if($update->message->chat->id){
$chat_id    =   $update->message->chat->id;
$message_id =   $update->message->message_id;
}else{
$chat_id    =   $update->callback_query->message->chat->id;
$message_id =   $update->callback_query->message->message_id;
}
$call       =   $update->callback_query;
$callData   =   $update->callback_query->data;
$callID     =   $update->callback_query->id;
$on         =   $update->message;
$text       =   $update->message->text;
$image      =   $update->message->photo;
$video      =   $update->message->video;
$videoNote  =   $update->message->video_note;
$music      =   $update->message->audio;
$voice      =   $update->message->voice;
$file       =   $update->message->document;
$username   =   $update->message->from->username;
$firstName  =   $update->message->from->first_name;
$lastName   =   $update->message->from->last_name;
$fullName   =   "$firstName $lastName";

//file_put_contents('message.txt', $INPUT);  #Save Messages in: 'message.txt'
include_once __DIR__ . '/includes/settings.php';
include_once __DIR__ . '/includes/functions.php';

#===============================================================================
#--------------------------------- Check ---------------------------------------
#===============================================================================

// Callback query
if($call){
    
    $callKey = explode('*', $callData);
    
    // Delete user selected files
    if($callKey[0] == 'delete') {
        try {
            $fileID     =   $callKey[1];
            $sql        =   "SELECT * FROM files WHERE id ='$fileID' LIMIT 1";
            $q          =   $conn->query($sql);
            $r          =   $q->fetch(PDO::FETCH_ASSOC);
            $fileName   =   $r['file_name'];
            $fileDir    =   $r['file_dir'];
            $Delete     =   "DELETE FROM files WHERE id=$fileID";
            
            unlink("$fileDir/$fileName");    # Delete file in saved folder
            $conn->exec($Delete);                   # Delete file in database
            updateUserFiles($chat_id, true);  # Update user files count
            
            /*
            Send('deleteMessage',[
                'chat_id'           =>  $chat_id,
                'message_id'        =>  $message_id
            ]);
            */
            
            Send('editMessageText',[
                'chat_id'           =>  $chat_id,
                'message_id'        =>  $message_id,
                'parse_mode'        =>  'HTML',
                'text'              =>  $e['FileIsRemoved']
                
            ]);
            
            Send('answerCallbackQuery',[
                'callback_query_id' =>  $callID,
                'text'              =>  $e['Done'],
                'show_alert'        =>  false
            ]);
        }
        catch(PDOException $e){
            Send('answerCallbackQuery',[
                'callback_query_id' =>  $callID,
                'text'              =>  $e['Error'],
                'show_alert'        =>  true
            ]);
        }
        
    }
    
}

// Send welcome message
if($text == '/start'){
    
    Send('sendMessage',[
        'chat_id'       =>  $chat_id,
        'parse_mode'    =>  'HTML',
        'text'          =>  $e['welcome']
    ]);
}

// Save images
if($image){

	if($image[3])
		$fileID = $image[3]->file_id;
	else if($image[2])
		$fileID = $image[2]->file_id;
	else if($image[1])
		$fileID = $image[1]->file_id;
	else if($image[0])
		$fileID = $image[0]->file_id;
    
	$fileSaveData = save($fileID, $imageFolder);   # Save file
	
	if($fileSaveData){
	    
	    updateUserFiles($chat_id);  # Update user files count
	    
	    $res = Send('sendMessage',[
            'chat_id'               =>  $chat_id,
            'reply_to_message_id'   =>  $message_id,
            'parse_mode'            =>  'HTML',
            'text'                  =>  $e['imageSaveText'],
            'reply_markup'          =>  json_encode([
                'inline_keyboard'   =>  $fileSaveData
            ])
        ]);
        
        saveMessageIdFiles($fileID, $res->result->message_id);
	}
	
}

// Save videos & video notes
if($video || $videoNote){
    
    if($video){
	    $fileID = $video->file_id;
	    $fileSize   = ($video->file_size / 1024)/1024;
	}else{
	    $fileID = $videoNote->file_id;
	    $fileSize   = ($videoNote->file_size / 1024)/1024;
	}
    
	if((int)$fileSize > 20){
	    Send('sendMessage',[
            'chat_id'       =>  $chat_id,
            'parse_mode'    =>  'HTML',
            'text'          =>  $e['FileIsBig']
        ]);
	}else{
	    $fileSaveData = save($fileID,$videoFolder); # Save file
	    updateUserFiles($chat_id);  # Update user files count
    	if($fileSaveData){
        	$res = Send('sendMessage',[
                'chat_id'               =>  $chat_id,
                'reply_to_message_id'   =>  $message_id,
                'parse_mode'            =>  'HTML',
                'text'                  =>  $e['videoSaveText'],
                'reply_markup'          =>  json_encode([
                    'inline_keyboard'   =>  $fileSaveData
                ])
            ]);
            
            saveMessageIdFiles($fileID, $res->result->message_id);
    	}else{
    	    Send('sendMessage',[
                'chat_id'       =>  $chat_id,
                'parse_mode'    =>  'HTML',
                'text'          =>  $e['errorToSave']
            ]);
    	}
	}
}

// Save musics & voices
if($music || $voice){
    
    if($music){
	    $fileID     = $music->file_id;
	    $fileSize   = ($music->file_size / 1024)/1024;
	}else{
	    $fileID     = $voice->file_id;
	    $fileSize   = ($voice->file_size / 1024)/1024;
	}
	
	if((int)$fileSize > 20){
	    Send('sendMessage',[
            'chat_id'=>$chat_id,
            'parse_mode'=>'HTML',
            'text'=>$e['FileIsBig']
        ]);
	}else{
	    $fileSaveData = save($fileID,$musicFolder); # Save file
	    updateUserFiles($chat_id);  # Update user files count
    	if($fileSaveData){
        	$res = Send('sendMessage',[
                'chat_id'               =>  $chat_id,
                'reply_to_message_id'   =>  $message_id,
                'parse_mode'            =>  'HTML',
                'text'                  =>  $e['musicSaveText'],
                'reply_markup'          =>  json_encode([
                    'inline_keyboard'   =>  $fileSaveData
                ])
            ]);
            
            saveMessageIdFiles($fileID, $res->result->message_id);
    	}else{
    	    Send('sendMessage',[
                'chat_id'=>$chat_id,
                'parse_mode'=>'HTML',
                'text'=>$e['errorToSave']
            ]);
    	}
	}
}

// Save documents
if($file){
    
	$fileID     = $file->file_id;
	$fileSize   = ($file->file_size / 1024)/1024;
	
	if((int)$fileSize >= 20){
	    Send('sendMessage',[
            'chat_id'=>$chat_id,
            'parse_mode'=>'HTML',
            'text'=>$e['FileIsBig']
        ]);
	}else{
	    $fileSaveData = save($fileID,$fileFolder);    #Save file
	    updateUserFiles($chat_id);  # Update user files count
    	if($fileSaveData){
        	$res = Send('sendMessage',[
                'chat_id'               =>  $chat_id,
                'reply_to_message_id'   =>  $message_id,
                'parse_mode'            =>  'HTML',
                'text'                  =>  $e['fileSaveText'],
                'reply_markup'          =>  json_encode([
                    'inline_keyboard'   =>  $fileSaveData
                ])
            ]);
            
            saveMessageIdFiles($fileID, $res->result->message_id);
    	}else{
    	    Send('sendMessage',[
                'chat_id'=>$chat_id,
                'parse_mode'=>'HTML',
                'text'=>$e['errorToSave']
            ]);
    	}
	}
}


// Insert & Update User
if(isset($on)){
    $stmt = $conn->prepare('SELECT chat_id FROM users WHERE chat_id=:chat_id');
    $stmt->execute(array(':chat_id'=>$chat_id));
    $count = $stmt->rowCount();
    $time = time();
    if($count>0){
        $userUpdate = "UPDATE users SET full_name='$fullName', username='$username', date='$time' WHERE chat_id=$chat_id";
        $ap = $conn->prepare($userUpdate);
        $ap->execute();
    }else{
        try{
            $userSet = 'INSERT INTO `users` (`chat_id`, `full_name`, `username`, `date`) VALUES (:chat_id , :full_name, :username, :date)';
            $userInsert = $conn->prepare($userSet);
            $userInsert->execute(array(
                ':chat_id'      =>      $chat_id,
                ':full_name'    =>      "$fullName",
                ':username'     =>      "$username",
                ':date'         =>      time()
            ));
        }catch(PDOException $e){
            file_put_contents('Error_log.txt', $e->getMessage());   # Save Errors in file: 'Error_log.txt'
        }
    }
}

// For cron job
if($DELETEFILES && $deleteFilesMessageID)
    editMessageFileDeleted($userID, $deleteFilesMessageID);

#===============================================================================
#-------------------------------- Webhook --------------------------------------
#===============================================================================

if(isset($_GET['setWebhook'])){
    
    $publicKEY = '';    # *Optional* - Public key certificate file.
    
    $url= APP_URL.'/setWebhook?url='.curPageURL(true);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if(!empty($publicKEY)){
        $post = array('certificate'   =>  new CURLFile(realpath($publicKEY)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_exec($ch);
    curl_close($ch);
}

if(isset($_GET['getWebhookInfo'])){
    $url= APP_URL.'/getWebhookInfo';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_exec($ch);
    curl_close($ch);
}

if(isset($_GET['deleteWebhook'])){
    $url= APP_URL.'/deleteWebhook';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_exec($ch);
    curl_close($ch);
}
?>
