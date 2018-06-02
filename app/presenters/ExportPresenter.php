<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Forms\ExportPersons\ExportPersonsFormControl;
use App\Components\Forms\ExportPersons\ExportPersonsFormControlFactory;
use App\Components\Grids\ExportPersons\ExportPersonsGrid;
use App\Components\Grids\ExportPersons\ExportPersonsGridFactory;
use App\Components\Grids\ExportPersonsDetail\ExportPersonsDetailGrid;
use App\Components\Grids\ExportPersonsDetail\ExportPersonsDetailGridFactory;


class ExportPresenter extends SecurePresenter
{
	/** @var ExportPersonsFormControlFactory */
	private $exportPersonsFormControlFactory;

	/** @var ExportPersonsGridFactory */
	private $exportPersonsGridFactory;

	/** @var ExportPersonsDetailGridFactory */
	private $exportPersonsDetailGridFactory;

	public function __construct(ExportPersonsFormControlFactory $exportPersonsFormControlFactory, ExportPersonsGridFactory $exportPersonsGridFactory, ExportPersonsDetailGridFactory $exportPersonsDetailGridFactory)
	{
		parent::__construct();
		$this->exportPersonsFormControlFactory = $exportPersonsFormControlFactory;
		$this->exportPersonsGridFactory = $exportPersonsGridFactory;
		$this->exportPersonsDetailGridFactory = $exportPersonsDetailGridFactory;
	}

	public function createComponentExportPersonsFormControl(string $name) : ExportPersonsFormControl
	{
		return $this->exportPersonsFormControlFactory->create();
	}

	public function createComponentExportPersonsGrid(string $name) : ExportPersonsGrid
	{
		return $this->exportPersonsGridFactory->create();
	}

	public function createComponentExportPersonsDetailGrid(string $name) : ExportPersonsDetailGrid
	{
		$control = $this->exportPersonsDetailGridFactory->create();
		$control->setId((int) $this->getParameter('id'));
		return $control;
	}

	public function renderExportDetail(int $id) : void
	{

	}
}