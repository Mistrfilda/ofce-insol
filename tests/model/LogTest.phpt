<?php


namespace Test;


use App\Model\ImportModel;
use App\Model\LogModel;
use Tester\Assert;


$container = require '../bootstrap.php';

/**
 * @testCase
 */
class LogTest extends BaseTest
{
	/** @var ImportModel */
	private $importModel;

	/** @var LogModel */
	private $logModel;

	public function setUp()
	{
		parent::setUp();
		$this->importModel = $this->container->getByType(ImportModel::class);
		$this->logModel = $this->container->getByType(LogModel::class);
		$this->createUser();
		$this->loginUser();
		$this->database->commit();
	}

	public function testLog()
	{
		$file = file_get_contents('../files/single_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(1, $importedPersons['imported_count']);

		$log = $this->logModel->getFluentBuilder()->fetchAll();
		Assert::count(3, $log);
		Assert::equal($this->user->getId(), $log[0]['log_users_id']);

		$invoiceFile = file_get_contents('../files/invoice_import_2.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);
		Assert::equal(2, $importedInvoices['imported_count']);

		$log = $this->logModel->getFluentBuilder()->fetchAll();
		Assert::count(7, $log);
	}
}

(new LogTest($container))->run();