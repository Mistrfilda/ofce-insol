<?php

declare(strict_types = 1);


namespace App\Console;


use App\Lib\Logger;
use Nette\Security\User;
use Symfony\Component\Console\Command\Command;


class BaseCommand extends Command
{
	/** @var Logger */
	protected $logger;

	/** @var User */
	protected $user;

	public function injectUser(User $user) : void
	{
		$this->user = $user;
	}

	public function injectLogger(Logger $logger) : void
	{
		$this->logger = $logger;
	}

	/**
	 * @param array|string[] $cliCredentials
	 * @throws \Dibi\Exception
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function setCliUser(array $cliCredentials) : void
	{
		$this->user->login($cliCredentials['user'], $cliCredentials['password']);
		$this->user->setExpiration('30 minutes');
		$this->logger->log('Login', 'Cli login');
	}
}