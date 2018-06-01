<?php

declare(strict_types = 1);


namespace App\Model;


use Dibi\Connection;
use Dibi\Fluent;


abstract class BaseModel
{
	/** @var Connection */
	protected $database;

	/** @var  Fluent */
	protected $databaseFluent;

	public function injectDatabase(Connection $database) : void
	{
		$this->database = $database;
		$this->databaseFluent = new Fluent($database);
	}
}