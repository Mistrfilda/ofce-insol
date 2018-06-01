<?php

declare(strict_types = 1);


namespace App\Components\Forms\ExportPersons;


use App\Components\Forms\BaseForm;
use App\Lib\AppException;
use App\Model\ExportModel;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;


class ExportPersonsFormControl extends BaseForm
{
	/** @var ExportModel */
	private $exportModel;

	public function __construct(ExportModel $exportModel)
	{
		parent::__construct();
		$this->exportModel = $exportModel;
	}

	public function render() : void
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentExportPersonsForm(string $name) : Form
	{
		$form = $this->createForm();
		$form->addUpload('file', 'Csv file')->setRequired();
		$form->onValidate[] = [$this, 'validateExportPersonsForm'];
		$form->onSuccess[] = [$this, 'exportPersonsFormSucceed'];
		$form->addSubmit('submit', 'Odeslat');
		return $form;
	}

	public function validateExportPersonsForm(Form $form) : void
	{
		$file = $form->getValues()['file'];
		$fileInfo = pathinfo($file->getName());
		if ($fileInfo['extension'] === NULL || Strings::lower($fileInfo['extension']) !== 'csv') {
			$form->addError('Nepodporovany typ souboru, nahrajte prosim csv soubor');
			return;
		}
	}

	public function exportPersonsFormSucceed(Form $form, ArrayHash $values) : void
	{
		$fileContents = file_get_contents($values['file']->getTemporaryFile());
		try {
			$result = $this->exportModel->exportPersons($fileContents);
		} catch (AppException $e) {
			if ($e->getCode() === AppException::EXPORT_PERSONS_NO_ROWS) {
				$this['exportPersonsForm']->addError('Nepodarilo se nahrat zadnou novou osobu, zkontrolujte zdroj!');
				$this->presenter->flashMessage('Nepodarilo se nahrat zadnou novou osobu, zkontrolujte zdroj!', 'danger');
				return;
			}
			throw $e;
		}

		$this->presenter->flashMessage('Zobrazuji export #ID ' . $result, 'success');
		$this->presenter->redirect('Export:default');
	}
}