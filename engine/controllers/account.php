<?php
class Account
{
	private $account_id;
	private $chat_id;
	private $account_currency;
	private $account_balance;
	private $account_name;
	public function __construct($chat_id,$account_id)
	{
		if(isset($chat_id))
		{
			$users =  R::getAll( 'SELECT * FROM accounts WHERE chat_id = :chat_id AND id =:id',[':chat_id' => $chat_id,':id' => $account_id ]); 
			if($users)
			{
				$this->account_id = $users[0]['id'];
				$this->account_balance = $users[0]['account_balance'];
				$this->account_currency = $users[0]['account_currency'];
				$this->account_name = $users[0]['account_name'];

				
			}
			else
			{

				
				$accounts = R::dispense( 'accounts' );
				$accounts->chat_id = $chat_id;
				$accounts->account_name = 'default';
				$accounts->account_balance = 0.00;
				$accounts->account_currency = '₽';
				$id = R::store( $accounts );

				$this->account_id = $id;
				$this->account_balance = 0.00;
				$this->account_currency = '₽';
				$this->account_name = 'default';



			}
			$this->chat_id = $chat_id;
		}
	}
	public function CreateAccount($name)
	{
		$account  = R::exec('SELECT id FROM accounts WHERE account_name=? AND chat_id = ?',[ $name ,$this->chat_id ]);
		if(!$account)
		{
			$accounts = R::dispense( 'accounts' );
			$accounts->chat_id = $this->chat_id;
			$accounts->account_name = $name;
			$accounts->account_balance = 0.00;
			$accounts->account_currency = '₽';
			$id = R::store( $accounts );
			return $id;
		}
		else
		{
			return false;
		}
		
	}
	public function GetAccounts()
	{
		$accounts =  R::getAll( 'SELECT * FROM accounts WHERE chat_id = :chat_id',[':chat_id' => $this->chat_id]); 
		$dailyArray = array();
		$dailyArray2 = array();
		for($i=0;$i<count($accounts);$i++)
		{
			$dailyArray[$i] = '/accounts '.$accounts[$i]['account_name'];
		}
		for($i=0;$i<count($accounts);$i++)
		{
			$dailyArray2[$i] = array($dailyArray[$i]);
		}
		return $dailyArray2;
	}
	public function GetAccountId()
	{
		return $this ->account_id;
	}
	public function getBalance()
	{
		return $this ->account_balance;
	}
	public function setBalance($balance)
	{
		$account = R::load('accounts', $this->account_id);
		$account->account_balance = $balance;
		R::store($account);
	}
	public function upBalance($balance)
	{
		$this->account_balance = (float)$this->account_balance + $balance;
		$account = R::load('accounts', $this->account_id);
		$account->account_balance = $this->account_balance;
		R::store($account);
	}
	public function downBalance($balance)
	{
		$this->account_balance = (float)$this->account_balance - $balance;
		$account = R::load('accounts', $this->account_id);
		$account->account_balance = $this->account_balance;
		R::store($account);
	}
	public function getCurrency()
	{
		return $this->account_currency;
	}
	public function setCurrency($curr)
	{
		switch ($curr)
		{
			case '₽':
				$curr = '₽';
			break;
			case '$':
				$curr = '$';
			break;
			case '€':
				$curr = '€';
			break;
			case '£':
				$curr = '£';
			break;
			default: $curr = '₽';
		}
		$account = R::load('accounts', $this->account_id);
		$account->account_currency = $curr;
		R::store($account);
	}

}