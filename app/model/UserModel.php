<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\Fluent;
use Nette\Security\Passwords;


class UserModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('users');
	}

	public function createUser(string $name, string $password, int $sysadmin = 0) : void
	{
		$this->database->query('INSERT into users', [
			'users_login' => $name,
			'users_password' => Passwords::hash($password),
			'users_sysadmin' => $sysadmin
		]);

		$this->logger->log('USER CREATE', 'Created user - . ' . $name);
	}

	public function updateUser(int $userId, string $name, ?string $password, int $sysadmin = 0) : void
	{
		$update = [];
		$update['users_login'] = $name;
		$update['users_sysadmin'] = $sysadmin;
		if ($password !== NULL) {
			$update['users_password'] = Passwords::hash($password);
		}

		$this->database->query('UPDATE users set', $update, 'where users_id = %i', $userId);

		$this->logger->log('USER UPDATE', 'Updated user - . ' . $name);
	}


	/**
	 * @param int $id
	 * @return array|string[]|int[]
	 * @throws AppException
	 */
	public function getUserById(int $id) : array
	{
		$data = $this->database->query('SELECT * from users where users_id = %i', $id)->fetch();
		if ($data === NULL) {
			throw new AppException(AppException::UNKNOWN_USER);
		}

		return (array) $data;
	}


	/**
	 * @param string $login
	 * @return array|array[]
	 * @throws AppException
	 */
	public function getUserByLogin(string $login) : array
	{
		$data = $this->database->query('SELECT * from users where users_login = %s', $login)->fetch();
		if ($data === NULL) {
			throw new AppException(AppException::UNKNOWN_USER);
		}

		return (array) $data;
	}


	/**
	 * @return array|array[]
	 */
	public function getPairs() : array
	{
		return $this->database->query('SELECT * from users')->fetchPairs('users_id', 'users_login');
	}
}