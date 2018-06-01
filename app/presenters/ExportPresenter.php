<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Forms\ExportPersons\ExportPersonsFormControl;
use App\Components\Forms\ExportPersons\ExportPersonsFormControlFactory;
use App\Components\Grids\ExportPersons\ExportPersonsGrid;
use App\Components\Grids\ExportPersons\ExportPersonsGridFactory;


class ExportPresenter extends SecurePresenter
{
	/** @var ExportPersonsFormControlFactory */
	private $exportPersonsFormControlFactory;

	/** @var ExportPersonsGridFactory */
	private $exportPersonsGridFactory;

	public function __construct(ExportPersonsFormControlFactory $exportPersonsFormControlFactory, ExportPersonsGridFactory $exportPersonsGridFactory)
	{
		parent::__construct();
		$this->exportPersonsFormControlFactory = $exportPersonsFormControlFactory;
		$this->exportPersonsGridFactory = $exportPersonsGridFactory;
	}

	public function createComponentExportPersonsFormControl(string $name) : ExportPersonsFormControl
	{
		return $this->exportPersonsFormControlFactory->create();
	}

	public function createComponentExportPersonsGrid(string $name) : ExportPersonsGrid
	{
		return $this->exportPersonsGridFactory->create();
	}
}