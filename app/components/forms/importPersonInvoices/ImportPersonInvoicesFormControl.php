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

	public function __construct(ImportModel $importModel)
	{
		parent::__construct();
		$this->importModel = $importModel;
	}

	public function render() : void
	{
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
		$fileInfo = pathinfo($file->getName());
		if ($fileInfo['extension'] === NULL || Strings::lower($fileInfo['extension']) !== 'csv') {
			$form->addError('Nepodporovany typ souboru, nahrajte prosim csv soubor');
			return;
		}
	}

	public function importPersonInvoicesFormSucceed(Form $form, ArrayHash $values) : void
	{
		$fileContents = file_get_contents($values['file']->getTemporaryFile());

		try {
			$result = $this->importModel->importPersonInvoices($fileContents);
		} catch (AppException $e) {
			if ($e->getCode() === AppException::PERSON_UNKNOWN_PERSON) {
				$this['importPersonsForm']->addError('Nezname ID - ' . $e->getMessage());
				$this->presenter->flashMessage('Nezname ID - ' . $e->getMessage(), 'danger');
				return;
			}
			throw $e;
		}

		if ($result === 0) {
			$this['importPersonsForm']->addError('Nepodarilo se nahrat zadnou novou smlouvu, zkontrolujte zdroj!');
			$this->presenter->flashMessage('Nepodarilo se nahrat zadnou novou smlouvu, zkontrolujte zdroj!', 'danger');
			return;
		}

		$this->presenter->flashMessage($result . ' smluv uspesne nahrano!', 'success');
		$this->presenter->redirect('Invoices:default');

	}
}