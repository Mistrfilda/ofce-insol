<?php


namespace Test;


use Dibi\Connection;
use Nette\Application\BadRequestException;
use Nette\Security\Passwords;
use Nette\Security\User;
use Tester;
use Nette\DI\Container;

/**
 * @skip
 */
abstract class BaseTest extends Tester\TestCase
{
	/** @var Container  */
	protected $container;

	/** @var Connection */
	protected $database;

	/** @var User|null */
	protected $user = NULL;

	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->database = $container->getByType(Connection::class);
	}

	public function setUp()
	{
		parent::setUp();
		Tester\Environment::lock('database', __DIR__ . '/../temp');
		$this->database->begin();
	}

	protected function createUser()
	{
		$this->database->query('INSERT into users', [
			'users_login' => 'testAdmin',
			'users_password' => Passwords::hash('123456'),
			'users_sysadmin' => 1
		]);
	}

	protected function loginUser()
	{
		/** @var User $user */
		$user = $this->container->getByType(User::class);
		$user->login('testAdmin', '123456');
		$this->user = $user;
	}

	public function tearDown()
	{
		$this->database->rollback();
		parent::tearDown();

		$this->database->query('set foreign_key_checks = 0');
		$this->database->query('DELETE from users');
		$this->database->query('DELETE from log');
		$this->database->query('DELETE from persons');
		$this->database->query('DELETE from invoices');
		$this->database->query('DELETE FROM imports');
		$this->database->query('DELETE FROM exports');
		$this->database->query('set foreign_key_checks = 1');
	}
}