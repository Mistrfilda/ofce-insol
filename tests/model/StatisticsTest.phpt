<?php


namespace Test;


use App\Model\ImportModel;
use App\Model\StatisticsModel;
use Tester\Assert;


$container = require '../bootstrap.php';

/**
 * @testCase
 */
class StatisticsTest extends BaseTest
{
	/** @var ImportModel */
	private $importModel;

	/** @var StatisticsModel */
	private $statisticsModel;

	public function setUp()
	{
		parent::setUp();
		$this->importModel = $this->container->getByType(ImportModel::class);
		$this->statisticsModel = $this->container->getByType(StatisticsModel::class);
		$this->createUser();
		$this->loginUser();
		$this->database->commit();
	}

	public function testStatistics()
	{
		$personFile = file_get_contents('../files/person_import.csv');
		$importedPersons = $this->importModel->importPersons($personFile);
		$invoiceFile = file_get_contents('../files/invoice_import_2.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);

		Assert::equal(4, $this->statisticsModel->getPersonsCount());
		Assert::equal(2, $this->statisticsModel->getInvoicesCount());
	}
}
(new StatisticsTest($container))->run();