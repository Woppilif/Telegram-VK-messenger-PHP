<?php
class VKMessages{
	private $user_id;
	private $vk_token;
	private $user_ids = array();
	private $count = 10;
	public function __construct($vk_id,$vk_token){
		$this->user_id = $vk_id;
		$this->vk_token = $vk_token;
	}
	public function getMessages($count = 10){
		if($count == null || $count == 0) {$this->count = 10;}
		else
		{
			$this->count = $count;
		}
		$names = $this->getNames();
		$messages = $this->sendMessageQuery();
		for($i=0;$i<count($messages);$i++){

			for($n=0;$n<count($names);$n++){
				if($names[$n]['id'] == $messages[$i]['user_id']) { 
					$messages[$i]['user_name'] = "".$names[$n]['first_name']." ".$names[$n]['last_name']."";

			}

			$message = R::findOne('messages','message_id = ? AND date = ? AND vk_id = ?',[$messages[$i]['id'],$messages[$i]['date'],$this->user_id]);
			if($message){
				
				//$messages[$i]['exists'] = 1;
			}else{

				$messages[$i]['exists'] = 1;

				if(!isset($messages[$i]['chat_id'])) {$ch_id = 0;}else{
					$ch_id = $messages[$i]['chat_id'];
				}
				$mess = R::dispense('messages');
				$mess->vk_id = $this->user_id;
				$mess->message_id = $messages[$i]['id'];
				$mess->date = $messages[$i]['date'];
				$mess->chat_id = $ch_id;
				$mess->text = $messages[$i]['body'];
				$mess->user_id = $messages[$i]['user_id'];


				$id = R::store($mess);	

				
				$messages[$i]['added_message'] = $id;
			}
		}
		}
		file_put_contents("names.txt",json_encode($messages));
	//	file_put_contents("dt.txt",json_encode($messages));

		return array_reverse($messages);
	}
	public function sendMessageQuery(){
/*		if(isset($this->count) and $this->count!=0){
			$requestCount = $this->count;
		}else{
			$requestCount = 10;
		}
*/
		$url = 'https://api.vk.com/method/messages.get';
		$params = array(
			'user_id' => $this->user_id,  

			'access_token' => $this->vk_token, 
			'v' => '5.37',
			'out'=>0,
			'count'=>$this->count
			);
		$data = json_decode($this->request($url,$params), true);
		
		file_put_contents("dt.txt",json_encode($data));

		if(isset($data['error'])){
		//		$this->request($data['error']['redirect_uri'],array());
				$this->sendRdrUrl($data['error']['redirect_uri']);
			//	$this->sendMessageQuery();
		}

		return $data['response']['items'];
	}
	
	public function getNames(){
		$messages = $this->sendMessageQuery();
		for($i=0;$i<count($messages);$i++){

			$this->user_ids[$i] = $messages[$i]['user_id'];
		}
		$names = $this->sendUsersGet($this->user_ids);		
		return $names;
	}
	

	public function sendUsersGet($user_ids){
		$url = 'https://api.vk.com/method/users.get';
		$params = array(
			'user_ids' => $user_ids,  

			'access_token' => $this->vk_token, 
			'v' => '5.63',
			'name_case'=>'Nom'
			);
		$data = json_decode($this->request($url,$params), true);
		return $data['response'];
	}
	public function sendRdrUrl($url){
		return $url;
	}
	public function answerMessage($data){
		$reply_data = $data['reply_to_message'];

		$reply_message = R::findOne('telegram','message_id = ?',[$reply_data['message_id']]);
		if(!$reply_message) return false;
		$vk_message = R::findOne('messages','id = ?',[$reply_message['added_message']]);
		if(!$vk_message) return false;



 $url = 'https://api.vk.com/method/messages.send';
    $params = array(

        'message' => $data['text'],   
        
        'access_token' => $this->vk_token,  
        'v' => '5.37',
    );

	if($vk_message['chat_id'])
	{
       	$params['chat_id'] = $vk_message['chat_id'];
    } 
    else 
    {
       	$params['user_id'] = $vk_message['user_id'];
    }

    file_put_contents("dtt.txt",json_encode($params));
    $this->request($url,$params);
		return $vk_message;


	}
	public function request($url,$params){
		return file_get_contents($url, false, stream_context_create(array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => http_build_query($params)
				)
			)));
	}

	public function get_curl($url) {
if(function_exists('curl_init')) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$output = curl_exec($ch);
echo curl_error($ch);
curl_close($ch);
return $output;
} else{
return file_get_contents($url);
}
}


}