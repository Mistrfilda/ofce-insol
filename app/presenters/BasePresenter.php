<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Model\UserModel;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;


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
		$this->getTemplate()->menu = $this->buildMenu();
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

	private function buildMenu()
	{
		$menu = [
			'homepage' => [
				'label' => 'Home',
				'icon' => 'fa-home',
				'link' => 'Homepage:default'
			],
			'import' => [
				'label' => 'Import',
				'icon' => 'fa-user',
				'link' => 'Import:default'
			],
			'persons' => [
				'label' => 'Osoby',
				'icon' => 'fa-user',
				'link' => 'Persons:default'
			],
			'export' => [
				'label' => 'Export',
				'icon' => 'fa-arrow-right',
				'link' => 'Export:default'
			],
			'invoices' => [
				'label' => 'Smlouvy',
				'icon' => 'fa-list',
				'link' => 'Invoices:default'
			],
			'system' => [
				'label' => 'System',
				'icon' => 'fa-desktop',
				'link' => 'System:default',
				'right' => 1
			]
		];

		return $menu;
	}

	public function isPresenterCurrent(string $menuKey)
	{
		if (Strings::lower($this->getName()) === $menuKey) {
			return TRUE;
		}
		return FALSE;
	}
}