<?php


namespace App\Components\Grids\Log;


use App\Components\Grids\BaseGrid;
use App\Model\LogModel;
use App\Model\UserModel;
use Ublaboo\DataGrid\DataGrid;


class LogGrid extends BaseGrid
{
	/** @var LogModel */
	private $logModel;

	/** @var UserModel */
	private $userModel;

	public function __construct(LogModel $logModel, UserModel $userModel)
	{
		parent::__construct();
		$this->logModel = $logModel;
		$this->userModel = $userModel;
	}

	public function render()
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentLogGrid() : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('log_id');
		$grid->setDataSource($this->logModel->getFluentBuilder());
		$grid->addColumnText('log_id', 'ID');

		$userPairs = $this->userModel->getPairs();
		$grid->addColumnText('log_users_id', 'Uzivatel')->setRenderer(function ($row) use ($userPairs) {
			return $userPairs[$row['log_users_id']];
		})->setFilterSelect($userPairs);

		$grid->addColumnText('log_type', 'Typ')->setSortable()->setFilterText();
		$grid->addColumnText('log_message', 'Zprava')->setSortable()->setFilterText();
		return $grid;
	}
}