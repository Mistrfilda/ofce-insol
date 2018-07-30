<?php

declare (strict_types = 1);


namespace App\Lib;


use Dibi\Connection;
use Monolog\Logger as MonologLogger;
use Nette\Security\User;
use Tracy\ILogger;


class Logger
{
	/** @var Connection */
	private $database;

	/** @var  \Monolog\Logger */
	private $monologLogger;

	/** @var User */
	private $user;

	/** @var DatetimeProvider */
	private $datetimeProvider;

	public function __construct(MonologLogger $monologLogger, Connection $database, User $user, DatetimeProvider $datetimeProvider)
	{
		$this->database = $database;
		$this->user = $user;
		$this->monologLogger = $monologLogger;
		$this->datetimeProvider = $datetimeProvider;
		$monologLogger->pushHandler(MonologConsoleHandler::getMonologConsoleHandler());
	}

	/**
	 * @param string $type
	 * @param string $message
	 * @param array|mixed[] $context
	 * @param string $priority
	 * @param bool $logToMonolog
	 * @param bool $logToDatabase
	 * @throws \Dibi\Exception
	 */
	public function log(string $type, string $message, array $context = [], string $priority = ILogger::INFO, bool $logToMonolog = TRUE, bool $logToDatabase = TRUE) : void
	{
		if ($logToMonolog) {
			$monologParameters = [
				'log_time' => $this->datetimeProvider->getNow()->format('Y-m-d H:i:s'),
				'log_users_id' => $this->user->getId(),
				'log_message' => $message
			];

			$context = array_merge($monologParameters, $context);
			$this->monologLogger->log($priority, $type, $context);
		}

		if ($logToDatabase) {
			$databaseLogContext = NULL;
			if (count($context) > 0) {
				$databaseLogContext = json_encode($context);
			}

			$this->database->query('INSERT into log', [
				'log_type' => $type,
				'log_message' => $message,
				'log_users_id' => $this->user->getId(),
				'log_time' => $this->datetimeProvider->getNow(),
				'log_context' => $databaseLogContext
			]);
		}
	}
}