<?php


namespace Test;

use App\Model\ImportModel;
use App\Model\InvoiceModel;
use App\Model\PersonModel;
use Dibi\DateTime;
use Tester\Assert;


$container = require '../bootstrap.php';

/** @testCase */
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
	}


	public function testImportInvoices()
	{
		$this->database->commit();
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

		$person = $this->personModel->getFluentBuilder()->fetch();
		$invoice = $this->invoiceModel->getInvoice($person['persons_actual_invoice_id']);
		Assert::equal("10000058", $invoice['invoices_system_id']);
		Assert::equal(new DateTime('1.1.2019'), $invoice['invoices_to']);
	}

	public function tearDown()
	{
		parent::tearDown();
		//need to delete from persons table manually, since transactions are commited in importing, just for localhost :) i am lazy
		$this->database->query('set foreign_key_checks = 0');
		$this->database->query('DELETE from users');
		$this->database->query('DELETE from log');
		$this->database->query('DELETE from persons');
		$this->database->query('DELETE from invoices');
		$this->database->query('set foreign_key_checks = 1');
	}
}

(new InvoiceTest($container))->run();