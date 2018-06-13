<?php

declare(strict_types = 1);


namespace App\Auth;

use App\Lib\AppException;
use Dibi\Connection;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Utils\Strings;


class Authenticator implements IAuthenticator
{
	/**
	 * @var Connection
	 */
	private $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}


	/**
	 * @param array|array[] $credentials
	 * @throws AppException
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials) : Identity
	{
		$user = $this->database->query('SELECT * from users where users_login = %s', $credentials[0])->fetch();
		if ($user === NULL) {
			throw new AuthenticationException();
		}

		if (!Passwords::verify($credentials[1], $user['users_password'])) {
			throw new AuthenticationException();
		}

		return new Identity($user['users_id'], $user['users_id']);
	}
}