<?php
class Transactions extends Request
{
	private $chat_id;
	private $name;
	private $price;
	private $type;
	private $date;

	public function __construct($chat_id)
	{
		$this->chat_id = $chat_id;
	}

	public function Transaction($name,$price,$type,$account_id)
	{
		if(empty($name)) {$name = '';}
		$current_time = time() + 10800;
		$transaction = R::dispense( 'transactions' );
		$transaction->chat_id = $this->chat_id;
		$transaction->name = $name;
		$transaction->price = $price;
		$transaction->type = $type;
		$transaction->date = $current_time;
		$transaction->account_id = $account_id;
		$id = R::store( $transaction );
	}
	public function GetTransactions($day)
	{

		switch($day)
		{
			case 'today':
			
			$transactions =  R::getAll( 'SELECT * FROM transactions WHERE chat_id = :chat_id  AND `date` > UNIX_TIMESTAMP(CURDATE()) AND `date` < UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL +1 DAY)) ',
				[':chat_id' => $this->chat_id ]
				);  
			break;
			case 'yesterday':
			

			$transactions =  R::getAll( 'SELECT * FROM transactions WHERE chat_id = :chat_id  AND `date` > UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -2 DAY)) and `date` < UNIX_TIMESTAMP(CURDATE()) ',
				[':chat_id' => $this->chat_id ]
				);  
			break;
			case 'week':
			$transactions =  R::getAll( 'SELECT * FROM transactions WHERE chat_id = :chat_id  AND `date` >  UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -8 DAY)) and `date` < UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL +1 DAY)) ',
				[':chat_id' => $this->chat_id ]
				); 
			
			
			break;
		}


		return $transactions;
	}
}