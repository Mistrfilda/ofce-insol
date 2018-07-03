<?php

declare(strict_types = 1);


namespace App\Components\Forms\ImportPersonsInvoices;


use App\Components\Forms\BaseForm;
use App\Lib\AppException;
use App\Model\ImportModel;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;


class ImportPersonInvoicesFormControl extends BaseForm
{
	/** @var ImportModel */
	private $importModel;

	/** @var array|mixed[] */
	private $log = [];

	/** @var bool */
	private $showModal = FALSE;

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

	public function createComponentImportPersonInvoicesForm(string $name) : Form
	{
		$form = $this->createForm();
		$form->addUpload('file', 'CSV soubor')->setRequired();
		$form->onSuccess[] = [$this, 'importPersonInvoicesFormSucceed'];
		$form->onValidate[] = [$this, 'validateImportPersonInvoicesForm'];
		$form->addSubmit('submit', 'Upload');
		return $form;
	}

	public function validateImportPersonInvoicesForm(Form $form) : void
	{
		$file = $form->getValues()['file'];
		/** @var array $fileInfo */
		$fileInfo = pathinfo($file->getName());
		if ($fileInfo['extension'] === NULL || Strings::lower($fileInfo['extension']) !== 'csv') {
			$form->addError('Nepodporovany typ souboru, nahrajte prosim csv soubor');
			return;
		}
	}

	public function importPersonInvoicesFormSucceed(Form $form, ArrayHash $values) : void
	{
		$fileContents = @file_get_contents($values['file']->getTemporaryFile());
		if ($fileContents === FALSE) {
			$form->addError('Nepodarilo se nahrat soubor, zkuste to prosim znovu!');
			$this->presenter->flashMessage('Nepodarilo se nahrat soubor, zkuste to prosim znovu!', 'danger');
			return;
		}

		try {
			$result = $this->importModel->importPersonInvoices($fileContents);
		} catch (AppException $e) {
			if ($e->getCode() === AppException::PERSON_UNKNOWN_PERSON) {
				$form->addError('Nezname ID - ' . $e->getMessage());
				$this->presenter->flashMessage('Nezname ID - ' . $e->getMessage(), 'danger');
				return;
			} elseif ($e->getCode() === AppException::IMPORT_MISSING_MANDATORY_VALUE) {
				$form->addError('Chybi povinny parameter - ' . $e->getMessage());
				$this->presenter->flashMessage('Chybi povinny parameter - ' . $e->getMessage(), 'danger');
				return;
			}
			throw $e;
		}

		$this->log = $result;
		$this->showModal = TRUE;
	}
}