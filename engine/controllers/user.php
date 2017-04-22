<?php
class User
{
	private $user_id;
	private $user_status;
	private $vk_id;
	private $vk_token;
	private $chat_id;
	private $first_name;
	private $last_name;
	private $username;
	public function __construct($message)
	{
		/*telegram*/
		$this->chat_id = $message['chat']['id'];
		$this->first_name = $message['from']['first_name'];
		$this->last_name = $message['from']['last_name'];
		$this->username = $message['from']['username'];

		$user = R::findOne('users','chat_id = ?',[$this->chat_id]);
		if(!isset($user)){
			$u = R::dispense('users');
			$u->chat_id = $this->chat_id;
			$u->first_name = $this->first_name;
			$u->last_name = $this->last_name;
			$u->username = $this->username;
			$u->vk_id = 0;
			$u->vk_token = '';
			$u->user_status = "waiting_for_id";
			$this->user_id = R::store($u);
		}
		else
		{
			$this->user_id = $user['id'];
			$this->user_status = $user['user_status'];
			$this->vk_id = $user['vk_id'];
			$this->vk_token = $user['vk_token'];
		}


		
	}

	public function getId()
	{
		return $this ->user_id;
	}
	public function getVkId(){
		return $this->vk_id;
	}
	public function getToken(){
		return $this->vk_token;
	}
	public function setVkId($vk_id){
		$u = R::load('users',$this->user_id);
		$u->vk_id = $vk_id;
		R::store($u);
		$this->vk_id = $vk_id;
		return $this->vk_id;
	}
	public function setToken($vk_token){
		$u = R::load('users',$this->user_id);
		$u->vk_token= $vk_token;
		R::store($u);
		$this->vk_token = $vk_token;
		return $this->vk_token;
	}
	public function getFirstName()
	{
		return $this ->first_name;
	}
	public function getLastName()
	{
		return $this ->last_name;
	}
	
	public function getStatus($message){
		switch($this->user_status){
			case "waiting_for_id":
				if(!is_numeric($message)) return $step ="Excuse me!?...";
				$u = R::load('users',$this->user_id);
				$u->user_status = 'okay';
				R::store($u);

			break;
			case "okay": break;
			default: $step = "Hm..";
		}
		return $step;
	}
	
	
	
}
