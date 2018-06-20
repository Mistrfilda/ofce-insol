<?php

declare(strict_types = 1);


namespace App\Components\Grids\ExportPersonsDetail;


use App\Components\Grids\BaseGrid;
use App\Lib\Helpers;
use App\Model\ExportModel;
use App\Model\PersonModel;
use Dibi\Row;
use Ublaboo\DataGrid\DataGrid;


class ExportPersonsDetailGrid extends BaseGrid
{
	/** @var ExportModel */
	private $exportModel;

	/** @var PersonModel */
	private $personModel;

	/** @var int */
	private $exportsId;

	/** @var array|array[] */
	private $export;

	/** @var bool */
	private $showModal = FALSE;

	public function __construct(ExportModel $exportModel, PersonModel $personModel)
	{
		parent::__construct();
		$this->exportModel = $exportModel;
		$this->personModel = $personModel;
	}

	public function setId(int $id) : void
	{
		$this->exportsId = $id;
		$this->export = $this->exportModel->getExportPersonsData($id);
	}

	public function render() : void
	{
		$this->getTemplate()->showModal = $this->showModal;
		$this->getTemplate()->export = $this->export;
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentExportPersonsDetailGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('persons_id');
		$grid->setDataSource($this->exportModel->getExportsPersonDetailFluentBuilder($this->exportsId));
		$grid->addColumnText('persons_id', 'ID')->setFilterText();
		$grid->addColumnText('persons_ag_id', 'System ID')->setFilterText();
		$grid->addColumnText('persons_birth_id', 'Rodne cislo')->setFilterText();
		$grid->addColumnText('persons_company_id', 'IC')->setFilterText();
		$grid->addColumnText('persons_year', 'Rok')->setFilterSelect($this->addGridSelect(Helpers::getGridYears(2005, 2020)));
		$grid->addColumnText('persons_firstname', 'Jmeno');
		$grid->addColumnText('persons_lastname', 'Primeni/Nazev firmy');
		$grid->addColumnText('persons_actual_invoice_id', 'Aktualni smlouva')->setRenderer(function($row) {
			if ($row['persons_actual_invoice_id'] !== NULL) {
				return 'ANO';
			}

			return 'NE';
		});
		$grid->addColumnText('invoices_type', 'Typ smlouvy');
		$grid->addColumnDateTime('invoices_from', 'Smlouva platna od')->setFormat('d. m. Y H:i:s')->setFilterDate();
		$grid->addColumnDateTime('invoices_to', 'Smlouva platna do')->setFormat('d. m. Y H:i:s')->setFilterDate();
		$grid->setAutoSubmit(FALSE);
		$grid->setOuterFilterRendering(TRUE);
		$grid->addExportCsvFiltered('Export do csv', 'export-' . date('d-m-Y-H-i-s') . '.csv');

		$grid->addAction('showInvoices', '', 'showInvoices', ['id' => 'persons_id'])
			->setClass('btn btn-default ajax')
			->setIcon('arrow-right');

		return $grid;
	}

	public function handleShowInvoices(int $id) : void
	{
		$this->getTemplate()->personId = $id;
		$this->getTemplate()->personInvoices = $this->personModel->getPersonInvoices($id);
		$this->showModal = TRUE;
		$this->redrawControl('grid');
	}
}