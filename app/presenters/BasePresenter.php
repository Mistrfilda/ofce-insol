<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Model\UserModel;
use Nette\Application\UI\Presenter;


class BasePresenter extends Presenter
{
	/** @var UserModel $userModel */
	protected $userModel;

	public function injectUserModel(UserModel $userModel) : void
	{
		$this->userModel = $userModel;
	}

	protected function startup() : void
	{
		parent::startup();
		if ($this->getUser()->isLoggedIn()) {
			$this->getTemplate()->appUser = $this->userModel->getUserById($this->getUser()->getId());
		}
	}

	public function handleLogout() : void
	{
		$this->getUser()->logout();
		$this->getPresenter()->redirect('Login:default');
	}
}