<?php

declare(strict_types = 1);


namespace App\Auth;

use App\Lib\AppException;
use App\Model\UserModel;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Utils\Strings;


class Authenticator implements IAuthenticator
{
	/**
	 * @var UserModel
	 */
	private $userModel;

	public function __construct(UserModel $userModel)
	{
		$this->userModel = $userModel;
	}


	/**
	 * @param array $credentials
	 * @return Identity
	 * @throws AppException
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		try {
			$user = $this->userModel->getUserByLogin(Strings::lower($credentials[0]));
		} catch (AppException $e) {
			if ($e->getCode() === AppException::UNKNOWN_USER) {
				throw new AuthenticationException();
			}

			throw $e;
		}

		if (!Passwords::verify($credentials[1], $user['users_password'])) {
			throw new AuthenticationException();
		}

		return new Identity($user['users_id'], $user['users_id']);
	}
}