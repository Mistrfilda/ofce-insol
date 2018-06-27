<?php

declare (strict_types = 1);


namespace App\Components\Forms\ImportPersons;


use App\Components\Forms\BaseForm;
use App\Lib\AppException;
use App\Model\ImportModel;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;


class ImportPersonsFormControl extends BaseForm
{
	/** @var array|mixed[] */
	private $log = [];

	/** @var bool */
	private $showModal = FALSE;

	/** @var ImportModel */
	private $importModel;

	public function __construct(ImportModel $importModel)
	{
		parent::__construct();
		$this->importModel = $importModel;
	}

	public function render() : void
	{
		$this->getTemplate()->showModal = $this->showModal;
		$this->getTemplate()->log = $this->log;
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentImportPersonsForm(string $name) : Form
	{
		$form = $this->createForm();
		$form->addUpload('file', 'CSV soubor')->setRequired();
		$form->onSuccess[] = [$this, 'importPersonsFormSucceed'];
		$form->onValidate[] = [$this, 'validateImportPersonsForm'];
		$form->addSubmit('submit', 'Upload');
		return $form;
	}

	public function validateImportPersonsForm(Form $form) : void
	{
		$file = $form->getValues()['file'];

		/** @var array $fileInfo */
		$fileInfo = pathinfo($file->getName());
		if ($fileInfo['extension'] === NULL || Strings::lower($fileInfo['extension']) !== 'csv') {
			$form->addError('Nepodporovany typ souboru, nahrajte prosim csv soubor');
			return;
		}
	}

	public function importPersonsFormSucceed(Form $form, ArrayHash $values) : void
	{
		$fileContents = @file_get_contents($values['file']->getTemporaryFile());
		if ($fileContents === FALSE) {
			$this['exportPersonsForm']->addError('Nepodarilo se nahrat soubor, zkuste to prosim znovu!');
			$this->presenter->flashMessage('Nepodarilo se nahrat soubor, zkuste to prosim znovu!', 'danger');
			return;
		}

		try {
			$result = $this->importModel->importPersons($fileContents);
		} catch (AppException $e) {
			if ($e->getCode() === AppException::IMPORT_MISSING_MANDATORY_VALUE) {
				$this['importPersonsForm']->addError('Chybi povinny parameters - ' . $e->getMessage());
				$this->presenter->flashMessage('Chybi povinny parameters - ' . $e->getMessage(), 'danger');
				return;
			}

			throw $e;
		}

		$this->log = $result;
		$this->showModal = TRUE;
	}
}