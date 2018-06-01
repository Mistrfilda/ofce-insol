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

	public function __construct(PersonModel $personModel)
	{
		parent::__construct();
		$this->personModel = $personModel;
	}

	public function render() : void
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentPersonsGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('persons_id');
		$grid->setDataSource($this->personModel->getFluentBuilder());
		$grid->addColumnText('persons_id', 'ID')->setFilterText();
		$grid->addColumnText('persons_ag_id', 'System ID')->setFilterText();
		$grid->addColumnText('persons_birth_id', 'Rodne cislo')->setFilterText();
		$grid->addColumnText('persons_company_id', 'IC')->setFilterText();
		$grid->addColumnText('persons_year', 'Rok')->setFilterSelect($this->addGridSelect(Helpers::getGridYears(2005, 2020)));
		$grid->addColumnText('persons_firstname', 'Jmeno');
		$grid->addColumnText('persons_lastname', 'Primeni/Nazev firmy');
		$grid->addColumnText('persons_actual_invoice_id', 'Aktualni smlouva');

		$grid->setAutoSubmit(FALSE);
		$grid->setOuterFilterRendering(TRUE);
		return $grid;
	}
}