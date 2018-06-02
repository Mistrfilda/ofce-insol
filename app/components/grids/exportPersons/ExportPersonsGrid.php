<?php

declare(strict_types = 1);


namespace App\Components\Grids\ExportPersons;


use App\Components\Grids\BaseGrid;
use App\Model\ExportModel;
use App\Model\UserModel;
use Ublaboo\DataGrid\DataGrid;


class ExportPersonsGrid extends BaseGrid
{
	/** @var ExportModel  */
	private $exportModel;

	/** @var UserModel */
	private $userModel;

	public function __construct(ExportModel $exportModel, UserModel $userModel)
	{
		parent::__construct();
		$this->exportModel = $exportModel;
		$this->userModel = $userModel;
	}

	public function render() : void
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentExportPersonsGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('exports_id');
		$grid->setDataSource($this->exportModel->getFluentBuilder());
		$grid->addColumnText('exports_id', 'ID')->setFilterText();
		$users = $this->userModel->getPairs();
		$grid->addColumnText('exports_users_id', 'Uzivatel')->setRenderer(function($row) use ($users) {
			return $users[$row['exports_users_id']];
		})->setFilterSelect($this->addGridSelect($this->userModel->getPairs()));

		$grid->addColumnDateTime('exports_time', 'Cas')->setFormat('d. m. Y H:i:s')->setFilterDate();
		$grid->addColumnText('exports_lines', 'Pocet osob k exportu');
		$grid->addAction('exportDetail', '', ':Export:exportDetail', ['id' => 'exports_id'])
			->setIcon('arrow-right');

		$grid->setAutoSubmit(FALSE);
		$grid->setOuterFilterRendering();
		return $grid;
	}
}