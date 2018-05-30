<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Nette\Security\Passwords;


class UserModel extends BaseModel
{
	public function createUser($name, $password)
	{
		$this->database->query('INSERT into users', [
			'users_login' => 'admin',
			'users_password' => Passwords::hash($password)
		]);
	}

	public function getUserById(int $id) : array
	{
		$data = $this->database->query('SELECT * from users where users_id = %i', $id)->fetch();
		if ($data === NULL) {
			throw new AppException(AppException::UNKNOWN_USER);
		}

		return (array) $data;
	}

	public function getUserByLogin(string $login) : array
	{
		$data = $this->database->query('SELECT * from users where users_login = %s', $login)->fetch();
		if ($data === NULL) {
			throw new AppException(AppException::UNKNOWN_USER);
		}

		return (array) $data;
	}

	public function getPairs() : array
	{
		return $this->database->query('SELECT * from users')->fetchPairs('users_id', 'users_name');
	}
}