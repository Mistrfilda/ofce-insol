<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\Fluent;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Passwords;


class UserModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('users');
	}

	public function createUser(string $name, string $password, int $sysadmin = 0) : int
	{
		if ($this->getUserById($this->user->getId())['users_sysadmin'] === 0) {
			throw new ForbiddenRequestException();
		}

		$this->database->query('INSERT into users', [
			'users_login' => $name,
			'users_password' => Passwords::hash($password),
			'users_sysadmin' => $sysadmin
		]);

		$userId = $this->database->getInsertId();

		$this->logger->log('USER CREATE', sprintf('Created user - %s', $name), ['users_name' => $name, 'users_id' => $userId]);

		return $userId;
	}

	public function updateUser(int $userId, string $name, ?string $password, int $sysadmin = 0) : void
	{
		if ($this->getUserById($this->user->getId())['users_sysadmin'] === 0) {
			throw new ForbiddenRequestException();
		}

		$this->getUserById($userId);

		$update = [];
		$update['users_login'] = $name;
		$update['users_sysadmin'] = $sysadmin;
		if ($password !== NULL) {
			$update['users_password'] = Passwords::hash($password);
		}

		$this->database->query('UPDATE users set', $update, 'where users_id = %i', $userId);

		$this->logger->log('USER UPDATE', sprintf('Updated user - %s', $name), ['users_name' => $name, 'users_id' => $userId, 'users_sysadmin' => $sysadmin]);
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