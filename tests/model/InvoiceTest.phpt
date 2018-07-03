<?php


namespace Test;

use App\Model\ImportModel;
use App\Model\InvoiceModel;
use App\Model\PersonModel;
use Dibi\DateTime;
use Tester\Assert;


$container = require '../bootstrap.php';

/**
 * @testCase
 */
class InvoiceTest extends BaseTest
{
	/** @var ImportModel */
	private $importModel;

	/** @var PersonModel */
	private $personModel;

	/** @var InvoiceModel */
	private $invoiceModel;

	public function setUp()
	{
		parent::setUp();
		$this->importModel = $this->container->getByType(ImportModel::class);
		$this->personModel = $this->container->getByType(PersonModel::class);
		$this->invoiceModel = $this->container->getByType(InvoiceModel::class);
		$this->createUser();
		$this->loginUser();
		$this->database->commit();
	}


	public function testImportInvoices()
	{
		$file = file_get_contents('../files/single_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(1, $importedPersons['imported_count']);

		$invoiceFile = file_get_contents('../files/invoice_import_2.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);
		Assert::equal(2, $importedInvoices['imported_count']);

		$person = $this->personModel->getFluentBuilder()->fetch();
		$invoice = $this->invoiceModel->getInvoice($person['persons_actual_invoice_id']);
		Assert::equal("10000050", $invoice['invoices_system_id']);

		$invoiceFile = file_get_contents('../files/invoice_import_lower_duration_change.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);
		Assert::equal(2, $importedInvoices['imported_count']);

		$person = $this->personModel->getFluentBuilder()->fetch();
		$invoice = $this->invoiceModel->getInvoice($person['persons_actual_invoice_id']);
		Assert::equal("10000050", $invoice['invoices_system_id']);

		$invoiceFile = file_get_contents('../files/invoice_import_duration_change.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);
		Assert::equal(4, $importedInvoices['imported_count']);

		$import = $this->importModel->getImport($importedInvoices['import_id']);
		Assert::equal($this->user->getId(), $import['imports_users_id']);
		//TODO mock datetime library to check time

		$person = $this->personModel->getFluentBuilder()->fetch();
		$invoice = $this->invoiceModel->getInvoice($person['persons_actual_invoice_id']);
		Assert::equal("10000058", $invoice['invoices_system_id']);
		Assert::equal(new DateTime('1.1.2019'), $invoice['invoices_to']);

		Assert::count(8, $this->invoiceModel->getFluentBuilder()->fetchAll());
		Assert::count(4, $this->importModel->getFluentBuilder()->fetchAll());
	}

	public function testImportWrongInvoiceFile()
	{
		$invoiceFile = file_get_contents('../files/wrong_invoices.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);
		Assert::equal(1, $importedInvoices['imported_count']);
		Assert::count(3, $importedInvoices['skipped_columns']);
		Assert::equal(2, $importedInvoices['skipped_columns'][0]['index']);
		Assert::equal('Nepodarilo se zpracovat datum: Planost od', $importedInvoices['skipped_columns'][0]['message']);
		Assert::equal(4, $importedInvoices['skipped_columns'][1]['index']);
		Assert::equal('10000059 - tato smlouva jiz byla naimportovana', $importedInvoices['skipped_columns'][1]['message']);
		Assert::equal(5, $importedInvoices['skipped_columns'][2]['index']);
		Assert::equal('Chybi vyplnene povinne pole - Typ smlouvy', $importedInvoices['skipped_columns'][2]['message']);
	}
}

(new InvoiceTest($container))->run();