<?php


namespace App\Model;


use Dibi\Connection;


abstract class BaseModel
{
	/** @var Connection $database */
	protected $database;

	public function injectDatabase(Connection $database)
	{
		$this->database = $database;
	}
}