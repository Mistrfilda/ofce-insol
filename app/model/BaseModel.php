<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\DatetimeProvider;
use Dibi\Connection;
use Dibi\Fluent;


abstract class BaseModel
{
	/** @var Connection */
	protected $database;

	/** @var  Fluent */
	protected $databaseFluent;

	/** @var DatetimeProvider */
	protected $datetimeProvider;

	public function injectDatabase(Connection $database) : void
	{
		$this->database = $database;
		$this->databaseFluent = new Fluent($database);
	}

	public function injectDatetimeProvider(DatetimeProvider $datetimeProvider) : void
	{
		$this->datetimeProvider = $datetimeProvider;
	}
}