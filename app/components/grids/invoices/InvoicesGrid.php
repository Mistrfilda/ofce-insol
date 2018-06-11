<?php

declare(strict_types = 1);


namespace App\Components\Grids\Invoices;


use App\Components\Grids\BaseGrid;
use App\Model\InvoiceModel;
use Ublaboo\DataGrid\DataGrid;


class InvoicesGrid extends BaseGrid
{
	/** @var InvoiceModel */
	private $invoiceModel;

	public function __construct(InvoiceModel $invoiceModel)
	{
		parent::__construct();
		$this->invoiceModel = $invoiceModel;
	}

	public function render() : void
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentInvoicesGrid(string $name) : DataGrid
	{
		$grid = $this->createGrid();
		$grid->setPrimaryKey('invoices_id');
		$grid->setDataSource($this->invoiceModel->getFluentBuilder());
		$grid->addColumnText('invoices_id', 'ID')->setSortable()->setFilterText();
		$grid->addColumnText('invoices_persons_birth_id', 'Rodne cislo')->setSortable()->setFilterText();
		$grid->addColumnDateTime('invoices_from', 'Platnost od')->setFormat('d. m. Y H:i:s')->setSortable()->setFilterDate();
		$grid->addColumnDateTime('invoices_imported_date', 'Nahrano dne')->setFormat('d. m. Y H:i:s')->setSortable()->setFilterDate();
		return $grid;
	}
}