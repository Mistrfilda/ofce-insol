<?php

declare (strict_types = 1);


namespace App\Components\Grids\Users;


use App\Components\Grids\BaseGrid;
use App\Model\UserModel;
use Ublaboo\DataGrid\DataGrid;


class UsersGrid extends BaseGrid
{
	/** @var UserModel  */
	private $userModel;

	public function __construct(UserModel $userModel)
	{
		parent::__construct();
		$this->userModel = $userModel;
	}

	public function render() : void
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentUsersGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('users_id');
		$grid->setDataSource($this->userModel->getFluentBuilder());
		$grid->addColumnText('users_id', 'ID');
		$grid->addColumnText('users_login', 'Login')->setSortable()->setFilterText();
		$grid->addColumnText('users_sysadmin', 'Sysadmin')->setRenderer(function ($row) {
			if ($row['users_sysadmin'] === 1) {
				return 'ANO';
			}

			return 'NE';
		});
		$grid->addAction('editUser', '', ':System:editUser', ['id' => 'users_id'])
			->setClass('btn btn-default')
			->setIcon('arrow-right');

		return $grid;
	}
}