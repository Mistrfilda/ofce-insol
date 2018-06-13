<?php


namespace Test;


use App\Model\UserModel;
use Dibi\Connection;
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
		$this->createUser();
	}

	public function setUp()
	{
		parent::setUp();
		$this->database->begin();
		Tester\Environment::lock('database', __DIR__ . '/../temp');
	}

	protected function createUser()
	{
		/** @var UserModel $userModel */
		$userModel = $this->container->getByType(UserModel::class);
		$userModel->createUser('testAdmin', '123456');
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
		parent::tearDown();
		$this->database->rollback();
	}
}