<?php


namespace Test;

use App\Lib\AppException;
use App\Model\UserModel;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Passwords;
use Tester\Assert;


$container = require '../bootstrap.php';

/**
 * @testCase
 */
class UserTest extends BaseTest
{
	/** @var UserModel */
	private $userModel;

	public function setUp()
	{
		parent::setUp();
		$this->userModel = $this->container->getByType(UserModel::class);
	}

	public function testCreateUser()
	{
		$this->createUser();
		$this->loginUser();

		$newUserID = $this->userModel->createUser('secondUser', '123456789');
		$userData = $this->userModel->getUserById($newUserID);
		Assert::equal('secondUser', $userData['users_login']);
		Assert::true(Passwords::verify('123456789', $userData['users_password']));
		Assert::equal(0, $userData['users_sysadmin']);

		Assert::exception(function() {
			$this->userModel->getUserById(12346789123);
		}, AppException::class, NULL, AppException::UNKNOWN_USER);

		$user = $this->userModel->getUserByLogin('testAdmin');
		Assert::equal('testAdmin', $user['users_login']);
		Assert::true(Passwords::verify('123456', $user['users_password']));
		Assert::equal(1, $user['users_sysadmin']);

		Assert::exception(function() {
			$this->userModel->getUserByLogin('aaaaaUser');
		}, AppException::class, NULL, AppException::UNKNOWN_USER);

		$this->userModel->updateUser($newUserID, 'secondUserNotAnymore', NULL, 1);
		$userData = $this->userModel->getUserById($newUserID);
		Assert::equal('secondUserNotAnymore', $userData['users_login']);
		Assert::true(Passwords::verify('123456789', $userData['users_password']));
		Assert::equal(1, $userData['users_sysadmin']);

		$this->userModel->updateUser($newUserID, 'secondUserNotAnymore', '987654321', 1);
		$userData = $this->userModel->getUserById($newUserID);
		Assert::equal('secondUserNotAnymore', $userData['users_login']);
		Assert::true(Passwords::verify('987654321', $userData['users_password']));
		Assert::equal(1, $userData['users_sysadmin']);

		$this->userModel->updateUser($this->user->getId(), 'testAdmin', NULL, 0);
		Assert::exception(function() {
			$this->userModel->createUser('11', '22');
		}, ForbiddenRequestException::class);

		Assert::exception(function() {
			$this->userModel->updateUser(5, 'secondUserNotAnymore', '987654321', 1);
		}, ForbiddenRequestException::class);
	}

	public function testGetUsersMethods()
	{
		$this->createUser();
		$this->loginUser();
		$this->userModel->createUser('secondUser', '123456789');
		$this->userModel->createUser('thirdUser', '123456789');
		Assert::count(3, $this->userModel->getFluentBuilder()->fetchAll());
		Assert::count(3, $this->userModel->getPairs());
	}
}
(new UserTest($container))->run();