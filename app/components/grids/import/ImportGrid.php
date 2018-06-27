<?php

declare(strict_types = 1);


namespace App\Components\Grids\Import;


use App\Components\Grids\BaseGrid;
use App\Model\ImportModel;
use App\Model\UserModel;
use Ublaboo\DataGrid\DataGrid;


class ImportGrid extends BaseGrid
{
	/** @var bool */
	private $showModal = FALSE;

	/** @var ImportModel  */
	private $importModel;

	/** @var UserModel */
	private $userModel;

	public function __construct(ImportModel $importModel, UserModel $userModel)
	{
		parent::__construct();
		$this->importModel = $importModel;
		$this->userModel = $userModel;
	}

	public function render() : void
	{
		$this->getTemplate()->showModal = $this->showModal;
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentImportGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('imports_id');
		$grid->setDataSource($this->importModel->getFluentBuilder());
		$grid->addColumnText('imports_id', 'ID')->setSortable()->setFilterText();
		$grid->addColumnDateTime('imports_time', 'Cas')->setFormat('d. m. Y H:i:s')->setSortable()->setFilterDate();
		$grid->addColumnText('imports_type', 'Typ')->setRenderer(function ($row) {
			if ($row['imports_type'] === 'INVOICE') {
				return 'Smlouvy';
			}

			return 'Osoby';
		})->setFilterSelect($this->addGridSelect(['INVOICE' => 'smlouvy', 'PERSON' => 'osoby']));
		$userPairs = $this->userModel->getPairs();
		$grid->addColumnText('imports_users_id', 'Uzivatel')->setRenderer(function($row) use ($userPairs) {
			return $userPairs[$row['imports_users_id']];
		})->setFilterText($userPairs);

		$grid->addAction('showLog', '', 'showLog', ['id' => 'imports_id'])
			->setClass('btn btn-default ajax')
			->setIcon('arrow-right');

		$grid->setDefaultSort(['imports_id' => 'DESC']);
		return $grid;
	}

	public function handleShowLog(int $id) : void
	{
		$import = $this->importModel->getImport($id);
		$this->getTemplate()->log = json_decode($import['imports_log'], TRUE);
		$this->showModal = TRUE;
		$this->redrawControl();
	}
}