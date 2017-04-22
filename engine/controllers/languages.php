<?php
class Languages
{
	private $lang;
	private $phrase;
	private $translate;

	public function __construct($lang)
	{
		$this->lang = $lang;
	} 

	public function lang($text)
	{
		$book  = R::findOne( 'languages', ' phrase = ? AND lang = ?', [ $text,$this->lang ] );
		if($this->lang == 'ru') return $text;
		else return $book['translate'];
	}
}