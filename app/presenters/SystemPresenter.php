<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Forms\User\EditUserFormControl;
use App\Components\Forms\User\EditUserFormControlFactory;
use App\Components\Grids\Log\LogGrid;
use App\Components\Grids\Log\LogGridFactory;
use App\Components\Grids\Users\UsersGrid;
use App\Components\Grids\Users\UsersGridFactory;
use Nette\Application\BadRequestException;


class SystemPresenter extends SecurePresenter
{
	/** @var LogGridFactory */
	private $logGridFactory;

	/** @var UsersGridFactory */
	private $usersGridFactory;

	/** @var EditUserFormControlFactory */
	private $editUserFormControlFactory;

	public function __construct(LogGridFactory $logGridFactory, UsersGridFactory $usersGridFactory, EditUserFormControlFactory $editUserFormControlFactory)
	{
		parent::__construct();
		$this->logGridFactory = $logGridFactory;
		$this->usersGridFactory = $usersGridFactory;
		$this->editUserFormControlFactory = $editUserFormControlFactory;
	}

	public function createComponentLogGrid(string $name) : LogGrid
	{
		return $this->logGridFactory->create();
	}

	public function createComponentUsersGrid(string $name) : UsersGrid
	{
		return $this->usersGridFactory->create();
	}

	public function createComponentEditUserFormControl(string $name) : EditUserFormControl
	{
		$control = $this->editUserFormControlFactory->create();
		$control->setId((int)$this->getParameter('id'));
		return $control;
	}


	public function startup() : void
	{
		parent::startup();
		if ($this->appUser['users_sysadmin'] === 0) {
			throw new BadRequestException();
		}
	}

	public function renderEditUser(int $id) : void
	{

	}
}