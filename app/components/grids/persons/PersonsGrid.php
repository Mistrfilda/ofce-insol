<?php

declare(strict_types = 1);


namespace App\Components\Grids\Persons;


use App\Components\Grids\BaseGrid;
use App\Lib\Helpers;
use App\Model\PersonModel;
use Ublaboo\DataGrid\DataGrid;


class PersonsGrid extends BaseGrid
{
	/** @var  PersonModel */
	private $personModel;

	/** @var bool */
	private $showModal = FALSE;

	public function __construct(PersonModel $personModel)
	{
		parent::__construct();
		$this->personModel = $personModel;
	}

	public function render() : void
	{
		$this->getTemplate()->showModal = $this->showModal;
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentPersonsGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('persons_id');
		$grid->setDataSource($this->personModel->getFluentBuilder());
		$grid->addColumnText('persons_id', 'Id osoby')->setFilterText();
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

		$grid->addAction('showInvoices', '', 'showInvoices', ['id' => 'persons_id'])
			->setClass('btn btn-default ajax')
			->setIcon('arrow-right');

		$grid->setAutoSubmit(FALSE);
		$grid->setOuterFilterRendering(TRUE);
		return $grid;
	}

	public function handleShowInvoices(int $id) : void
	{
		$this->getTemplate()->personId = $id;
		$this->getTemplate()->personInvoices = $this->personModel->getPersonInvoices($id);
		$this->showModal = TRUE;
		$this->redrawControl();
	}
}