<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Other\CssJsLoaderControl;
use App\Components\Other\CssJsLoaderControlFactory;
use App\Model\UserModel;
use Nette\Application\UI\Presenter;
use Nette\Utils\Strings;


abstract class BasePresenter extends Presenter
{
	/** @var UserModel $userModel */
	protected $userModel;

	/** @var array|string[]|int[] */
	protected $appUser;

	/** @var CssJsLoaderControlFactory */
	private $cssJsLoaderControlFactory;

	public function injectUserModel(UserModel $userModel) : void
	{
		$this->userModel = $userModel;
	}

	public function injectCssJsLoaderControlFactory(CssJsLoaderControlFactory $cssJsLoaderControlFactory) : void
	{
		$this->cssJsLoaderControlFactory = $cssJsLoaderControlFactory;
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

	/**
	 * @return array|mixed[]
	 */
	private function buildMenu() : array
	{
		$menu = [
			'homepage' => [
				'label' => 'Home',
				'icon' => 'fa-home',
				'link' => 'Homepage:default'
			],
			'import' => [
				'label' => 'Import',
				'icon' => 'fa-upload',
				'link' => 'Import:default'
			],
			'persons' => [
				'label' => 'Osoby',
				'icon' => 'fa-users',
				'link' => 'Persons:default'
			],
			'export' => [
				'label' => 'Export',
				'icon' => 'fa-file',
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

	public function createComponentCssJsLoaderControl(string $name) : CssJsLoaderControl
	{
		return $this->cssJsLoaderControlFactory->create();
	}

	public function isPresenterCurrent(string $menuKey) : bool
	{
		if (Strings::lower($this->getName()) === $menuKey) {
			return TRUE;
		}
		return FALSE;
	}
}