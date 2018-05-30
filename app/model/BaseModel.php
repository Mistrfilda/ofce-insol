<?php


namespace App\Model;


use Dibi\Connection;


abstract class BaseModel
{
	/** @var $database Connection */
	protected $database;

	public function injectDatabase(Connection $database)
	{
		$this->database = $database;
	}
}