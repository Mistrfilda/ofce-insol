<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Forms\ImportPersons\ImportPersonsFormControl;
use App\Components\Forms\ImportPersons\ImportPersonsFormControlFactory;


class ImportPresenter extends SecurePresenter
{
	/**
	 * @var ImportPersonsFormControlFactory
	 */
	private $importPersonsFormControlFactory;

	public function __construct(ImportPersonsFormControlFactory $importPersonsFormControlFactory)
	{
		parent::__construct();
		$this->importPersonsFormControlFactory = $importPersonsFormControlFactory;
	}

	public function createComponentImportPersonsFormControl() : ImportPersonsFormControl
	{
		$control = $this->importPersonsFormControlFactory->create();
		return $control;
	}
}