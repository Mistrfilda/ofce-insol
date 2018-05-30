<?php

declare(strict_types = 1);


namespace App\Auth;

use App\Model\UserModel;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;

class Authenticator implements IAuthenticator
{
	private $userModel;

	public function __construct(UserModel $userModel)
	{
		$this->userModel = $userModel;
	}

	public function authenticate(array $credentials)
	{
		$this->userModel;
		dump($credentials);
		die();
		//return new Identity($user->getId(), $user->getId());
	}
}