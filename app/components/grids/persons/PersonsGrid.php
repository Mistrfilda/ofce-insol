<?php

declare(strict_types = 1);


namespace App\Components\Grids\Persons;


use App\Components\Grids\BaseGrid;
use App\Lib\Helpers;
use App\Model\PersonModel;
use Dibi\DateTime;
use Dibi\Fluent;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
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
		$grid->setDataSource($this->personModel->getFluentBuilder()->leftJoin('invoices')->on('invoices_id = persons_actual_invoice_id'));

		$grid->addColumnText('persons_id', 'Id osoby')->setFilterText();
		$grid->addColumnText('persons_ag_id', 'System ID')->setFilterText();
		$grid->addColumnText('persons_birth_id', 'Rodne cislo')->setFilterText();
		$grid->addColumnText('persons_company_id', 'IC')->setFilterText();
		$grid->addColumnText('persons_year', 'Rok')->setFilterSelect($this->addGridSelect(Helpers::getGridYears(2005, 2020)));
		$grid->addColumnText('persons_firstname', 'Jmeno')->setFilterText();
		$grid->addColumnText('persons_lastname', 'Primeni/Nazev firmy')->setFilterText();
		$grid->addColumnText('persons_actual_invoice_id', 'Aktualni smlouva')->setRenderer(function($row) {
			if ($row['persons_actual_invoice_id'] !== NULL) {
				return 'ANO';
			}

			return 'NE';
		})->setFilterSelect([ 0 => 'NE', 1 => 'ANO'])->setPrompt('Vybrat')->setCondition(function(Fluent $fluent, int $value) : void {
			if ($value === 1) {
				$fluent->where('persons_actual_invoice_id is not null');
			} else {
				$fluent->where('persons_actual_invoice_id is null');
			}
		});

		$grid->addColumnText('invoices_type', 'Typ smlouvy');
		$grid->addColumnDateTime('invoices_from', 'Smlouva platna od')->setFormat('d. m. Y H:i:s')->setFilterDate();

		$grid->addColumnDateTime('invoices_to', 'Smlouva platna do')->setFormat('d. m. Y H:i:s')->setFilterDate();

		$grid->addFilterDateRange('invoices_to_range', 'Platnost smlouvy')->setCondition(function (Fluent $fluent,  $value) : void {
			$from = NULL;
			$to = NULL;

			if (array_key_exists('from', $value) && $value['from'] !== "") {
				$from = $value['from'];
				$from = Strings::replace($from, '~\ ~', '');
				$from = Strings::replace($from, '~\.~', '-');
			}

			if (array_key_exists('to', $value) && $value['to'] !== "") {
				$to = $value['to'];
				$to = Strings::replace($to, '~\ ~', '');
				$to = Strings::replace($to, '~\.~', '-');
			}

			if ($from !== NULL && $to !== NULL) {
				$fluent->where('date(invoices_from) <= %d and date(invoices_to) >= %d', $from, $to);
			} elseif ($from !== NULL) {
				$fluent->where('date(invoices_from) >= %d', $from);
			} elseif ($to !== NULL) {
				$fluent->where('date(invoices_to) <= %d', $to);
			}
		});

		$grid->addFilterSelect('invoices_month_select', 'Platnost smlouvy v měsíci', $this->getMonthsSelectOptions())->setCondition(function (Fluent $fluent, $value) : void {
			$values = Strings::split($value, '~:~');
			$year = (int) $values[0];
			$month = (int) $values[1];
			$fluent->where('
			(
			(month(invoices_from) = %i AND year(invoices_from) = %i) OR 
			(month(invoices_to) = %i AND year(invoices_to) = %i) OR
			((month(invoices_from) < %i AND year(invoices_from) = %i) AND (month(invoices_from) > %i AND year(invoices_from) = %i)) OR
			(year(invoices_from) < %i AND (month(invoices_to) > %i AND year(invoices_to) = %i) OR
			((month(invoices_from) < %i AND year(invoices_from) = %i) AND year(invoices_to) > %i)) OR
			(year(invoices_from) < %i AND year(invoices_to) > %i)
			)', $month, $year, $month, $year, $month, $year, $month, $year, $year, $month, $year, $month, $year, $year, $year, $year);
		});

		$grid->addColumnStatus('persons_checked', 'Zkontrolovano')
			->addOption(1, 'Ano')
			->setIcon('check')
			->setClass('btn-success')
			->endOption()
			->addOption(0, 'Ne')
			->setIcon('times')
			->setClass('btn-danger')
			->endOption()
			->onChange[] = [$this, 'checkPerson'];


		$grid->addAction('showInvoices', '', 'showInvoices', ['id' => 'persons_id'])
			->setClass('btn btn-default ajax')
			->setIcon('arrow-right');

		$grid->setAutoSubmit(FALSE);
		$grid->setOuterFilterRendering(TRUE);
		$grid->setRememberState(FALSE);

		$grid->addExportCsvFiltered('Export do csv', 'export-osob-' . date('Y-m-d-H-i-s') . '.csv');
		return $grid;
	}

	public function handleShowInvoices(int $id) : void
	{
		$this->getTemplate()->personId = $id;
		$this->getTemplate()->personInvoices = $this->personModel->getPersonInvoices($id);
		$this->showModal = TRUE;
		$this->redrawControl();
	}

	public function checkPerson(int $id, int $status) : void
	{
		$this->personModel->updatePersonChecked($id, $status);
		$this->presenter->flashMessage('Zmenen status pro osobu ' . $id);
		$this->getComponent('personsGrid')->reload();
		$this->presenter->redrawControl('flashes');
	}

	/**
	 * @return array|string[]
	 */
	private function getMonthsSelectOptions() : array
	{
		$translatedMonths = [
			1 => 'Leden',
			2 => 'Únor',
			3 => 'Březen',
			4 => 'Duben',
			5 => 'Kvetěn',
			6 => 'Červen',
			7 => 'Červenec',
			8 => 'Srpen',
			9 => 'Září',
			10 => 'Říjen',
			11 => 'Listopad',
			12 => 'Prosinec'
		];

		$options = [];
		$startYear = 2010;
		$endYear = 2025;
		while ($startYear <= $endYear) {
			$month = 1;
			while ($month <= 12) {
				$options[$startYear . ':' . $month] = $translatedMonths[$month] . ' ' . $startYear;
				$month = $month + 1;
			}

			$startYear = $startYear + 1;
		}

		return $options;
	}
}