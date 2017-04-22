<?php
class Daily
{
	private $chat_id;
	public function __construct($chat_id)
	{
		$this->chat_id = $chat_id;
	}
	public function GetDaily()
	{
		$daily =  R::getAll( 'SELECT name FROM daily WHERE chat_id = :chat_id  ORDER BY rating DESC LIMIT 10 ',
			[':chat_id' => $this->chat_id]
			);  
		$dailyArray = array();
		$dailyArray2 = array();
		for($i=0;$i<count($daily);$i++)
		{
			$dailyArray[$i] = '/d '.$daily[$i]['name'];
		}
		for($i=0;$i<count($daily);$i++)
		{
			$dailyArray2[$i] = array($dailyArray[$i]);
		}
		return $dailyArray2;

	}
	public function executeDaily($param)
	{
		$dailyOne =  R::getAll( 'SELECT price FROM daily WHERE chat_id = :chat_id  AND name = :name ',
			[':chat_id' => $this->chat_id , ':name' => $param]
			);  
		if($dailyOne)
		{
		//	commandMessage($chat_id,'Успешно выполнено действие '.$command.' ');
		//	downBalance($chat_id,$dailyOne[0]['price']);
			//$data = array('price' => );
			$dailyTwo = R::findOne( 'daily', ' name = ? AND chat_id = ?', [ $param,$this->chat_id ] );
			$rating = $dailyTwo['rating']+1;
			$daily = R::load('daily', $dailyTwo['id']);
			$daily->rating = $rating;
			$id = R::store( $daily );
			return $dailyOne[0]['price'];
		}
		else
		{
			return false;
		}
	}
	public function AddDaily($params=array())
	{
		$daily_id  = R::exec('SELECT id FROM daily WHERE name=? AND chat_id = ?',[ $params[0] ,$this->chat_id ]);
		if(!$daily_id)
		{
			$daily = R::dispense( 'daily' );
			$daily->chat_id = $this->chat_id;
			$daily->name = $params[0];
			$daily->price = $params[1];
			$daily->rating = 0;
			$id = R::store( $daily );
			return true;
    	//commandMessage($chat_id,'Добавлено новое ежедневное действие '.$params[0].'-'.$price.' ');
    	//showDaily($chat_id);
		}
		else
		{
		return false;//commandMessage($chat_id,' "'.$name.'" уже существует');
		}

	}
	public function RemoveDaily($name)
	{
		$daily_id = R::findOne( 'daily', ' name = ? AND chat_id = ?', [ $name,$this->chat_id ] );
		R::trash( $daily_id );
		if($daily_id) return true;
	}

}