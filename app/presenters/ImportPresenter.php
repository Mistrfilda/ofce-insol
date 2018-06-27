<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Forms\ImportPersons\ImportPersonsFormControl;
use App\Components\Forms\ImportPersons\ImportPersonsFormControlFactory;
use App\Components\Forms\ImportPersonsInvoices\ImportPersonInvoicesFormControl;
use App\Components\Forms\ImportPersonsInvoices\ImportPersonInvoicesFormControlFactory;
use App\Components\Grids\Import\ImportGrid;
use App\Components\Grids\Import\ImportGridFactory;


class ImportPresenter extends SecurePresenter
{
	/**
	 * @var ImportPersonsFormControlFactory
	 */
	private $importPersonsFormControlFactory;

	/**
	 * @var ImportPersonInvoicesFormControlFactory
	 */
	private $importPersonInvoicesFormControlFactory;

	/**
	 * @var ImportGridFactory
	 */
	private $importGridFactory;

	public function __construct(ImportPersonsFormControlFactory $importPersonsFormControlFactory, ImportPersonInvoicesFormControlFactory $importPersonInvoicesFormControlFactory, ImportGridFactory $importGridFactory)
	{
		parent::__construct();
		$this->importPersonsFormControlFactory = $importPersonsFormControlFactory;
		$this->importPersonInvoicesFormControlFactory = $importPersonInvoicesFormControlFactory;
		$this->importGridFactory = $importGridFactory;
	}

	public function createComponentImportPersonsFormControl() : ImportPersonsFormControl
	{
		$control = $this->importPersonsFormControlFactory->create();
		return $control;
	}

	public function createComponentImportPersonInvoicesFormControl(string $name) : ImportPersonInvoicesFormControl
	{
		return $this->importPersonInvoicesFormControlFactory->create();
	}

	public function createComponentImportGrid() : ImportGrid
	{
		return $this->importGridFactory->create();
	}
}