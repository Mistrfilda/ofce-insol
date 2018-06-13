<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\DatetimeProvider;
use App\Lib\Logger;
use Dibi\Connection;
use Dibi\Fluent;
use Nette\Security\User;
use ParseCsv\Csv;


abstract class BaseModel
{
	/** @var Connection */
	protected $database;

	/** @var  Fluent */
	protected $databaseFluent;

	/** @var DatetimeProvider */
	protected $datetimeProvider;

	/** @var Logger */
	protected $logger;

	/** @var User */
	protected $user;

	public function injectDatabase(Connection $database) : void
	{
		$this->database = $database;
		$this->databaseFluent = new Fluent($database);
	}

	public function injectDatetimeProvider(DatetimeProvider $datetimeProvider) : void
	{
		$this->datetimeProvider = $datetimeProvider;
	}

	public function injectLogger(Logger $logger) : void
	{
		$this->logger = $logger;
	}

	public function injectUser(User $user) : void
	{
		$this->user = $user;
	}

	public function getCsvParser() : Csv
	{
		$csvParser = new Csv();
		$csvParser->delimiter = ';';
		$csvParser->encoding('UTF-8');
		return $csvParser;
	}
}