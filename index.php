<?php

define('CONTROLLERS',dirname(__FILE__) .'/engine/controllers/');
define('DB',dirname(__FILE__) .'/engine/database/');
define('SETTINGS',dirname(__FILE__) .'/engine/');
require_once DB.'rb.php';
require_once CONTROLLERS.'request.php';
require_once CONTROLLERS.'message.php';
require_once CONTROLLERS.'vk.php';
require_once CONTROLLERS.'user.php';
require_once CONTROLLERS.'config.php';
$config = new Config();
R::setup( 'mysql:host='.$config->db_host.';dbname='.$config->db_name.'', $config->db_user, $config->db_password );
define('BOT_TOKEN', $config->token);
define('API_URL', $config->api_url.BOT_TOKEN.'/');
define('WEBHOOK_URL', $config->webhook_url);

$request = new Request();

$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) {
  // receive wrong update, must not happen
	exit;
}

if (isset($update["message"])) {
	//Log your JSON requests
	$myFile = "json.txt";
	file_put_contents($myFile,$content);

	$message = new Message($update["message"]);
	$request->getChatId($message->getChatId());
	$user = new User($update["message"]);
	$vk = new VKMessages($user->getVkId(),$user->getToken());

	if(isset($update["message"]['reply_to_message']))
	{
	//	$dataX = $request->sendMessage("Okay ");
	//	$message->logMessage($dataX);

		$answer = $vk->answerMessage($update["message"]);

		if(!$answer) return $request->sendMessage("Smth wrong!");

		$request->sendMessage("Success!");


	}else {

	switch($message->getMessage())
	{
		case '/start':
			if(!$message->getMessageParam(1)) return $request->sendMessage('Hi! Send me your VK ID => /start <VK ID>');
			$user->setVkId($message->getMessageParam(1));


			
			$request->sendMessage("Okay. Now you need to Auth in VK. Use this link: https://oauth.vk.com/authorize?client_id=SET_YOUR_ID_HERE&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=offline,messages&response_type=token&v=5.37 \n And then you have to copy access_token in url then use command /token <access_token>");
		break;
		case '/token':
			if(!$message->getMessageParam(1)) return $request->sendMessage('Hi! Send me your access_token from URL');
			$user->setToken($message->getMessageParam(1));
			$request->sendMessage("Okay. Now you can start with /get command");

		break;

		case '/get':
			if(!$vk->sendMessageQuery()) return $request->sendMessage('Something went wrong!');
		
			$data =  $vk->getMessages($message->getMessageParam(1));
			
				
			
			$zeroCount = 0;
			for($i=0;$i<count($data);$i++)
			{
				
				if($data[$i]['exists'])
				{
							$date = $data[$i]['date'];
							$body = $data[$i]['body'];
							if(empty($body)) 
							{
								$body = "";
								for($x=0;$x<count($data[$i]['attachments']);$x++)
								{

									if(isset($data[$i]['attachments'][$x]['photo']['photo_604']))
									{$body .= $data[$i]['attachments'][$x]['photo']['photo_604']."\n";}


									if(isset($data[$i]['attachments'][$x]['sticker']['photo_128']))
									{$body .= $data[$i]['attachments'][$x]['sticker']['photo_128']."\n";}
				
									if(isset($data[$i]['attachments'][$x]['doc']['url']))
									{$body .= $data[$i]['attachments'][$x]['doc']['url']."\n";}
								}
				
							}
							
							$uid = $data[$i]['user_name'];

							

							if($data[$i]['title'] != " ... ") {$title = $data[$i]['title'];}else{
								$title ="";
							}
							
							$dataX = $request->sendMessage("".$title."\n".$uid."\n".$body."\n".date("Y-m-d H:i:s",$date)."");
							

							$message->logMessage($dataX,$data[$i]['added_message']);

							$zeroCount ++;
				}
			}

			if($zeroCount == 0) return $request->sendMessage('Seems like there is no new messages!');
			

		break;

		default:$request->sendMessage('Again');
	}
}
}
?>
