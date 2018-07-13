<?php

declare (strict_types = 1);


namespace App\Lib;


use Dibi\Connection;
use Nette\Security\User;
use Tracy\ILogger;


class Logger
{
	/** @var Connection */
	private $database;

	/** @var  \Nextras\TracyMonologAdapter\Logger */
	private $monologLogger;

	/** @var User */
	private $user;

	/** @var DatetimeProvider */
	private $datetimeProvider;

	public function __construct(Connection $database, User $user, \Nextras\TracyMonologAdapter\Logger $monologLogger, DatetimeProvider $datetimeProvider)
	{
		$this->database = $database;
		$this->user = $user;
		$this->monologLogger = $monologLogger;
		$this->datetimeProvider = $datetimeProvider;
	}

	public function log(string $type, string $message, string $priority = ILogger::INFO, bool $logToMonolog = TRUE, bool $logToDatabase = TRUE) : void
	{
		if ($logToMonolog) {
			//$monologMessage = 'User: ' . $this->user->getId() . ' - Type: ' . $type . ' - Message: ' . $message;
			$monologMessage = sprintf('User: %s - Type: %s - Message: %s', $this->user->getId(), $type, $message);
			$this->monologLogger->log($monologMessage, $priority);
		}

		if ($logToDatabase) {
			$this->database->query('INSERT into log', [
				'log_type' => $type,
				'log_message' => $message,
				'log_users_id' => $this->user->getId(),
				'log_time' => $this->datetimeProvider->getNow()
			]);
		}
	}
}