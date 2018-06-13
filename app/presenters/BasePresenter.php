<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Model\UserModel;
use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
	/** @var UserModel $userModel */
	protected $userModel;

	/** @var array|string[]|int[] */
	protected $appUser;

	public function injectUserModel(UserModel $userModel) : void
	{
		$this->userModel = $userModel;
	}

	protected function startup() : void
	{
		parent::startup();
		if ($this->getUser()->isLoggedIn()) {
			$user = $this->userModel->getUserById($this->getUser()->getId());
			$this->getTemplate()->appUser = $user;
			$this->appUser = $user;
		}
	}

	public function handleLogout() : void
	{
		$this->getUser()->logout();
		$this->flashMessage('Byli jste odhlášeni', 'warning');
		$this->getPresenter()->redirect('Login:default');
	}
}