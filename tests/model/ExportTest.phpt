<?php


namespace Test;


use App\Model\ExportModel;
use App\Model\ImportModel;
use Tester\Assert;


$container = require '../bootstrap.php';

/**
 * @testCase
 */
class ExportTest extends BaseTest
{
	/** @var ImportModel */
	private $importModel;

	/** @var ExportModel */
	private $exportModel;

	public function setUp()
	{
		parent::setUp();
		$this->importModel = $this->container->getByType(ImportModel::class);
		$this->exportModel = $this->container->getByType(ExportModel::class);
		$this->createUser();
		$this->loginUser();
		$this->database->commit();
	}

	public function testExport()
	{
		$personFile = file_get_contents('../files/person_import.csv');
		$importedPersons = $this->importModel->importPersons($personFile);
		Assert::equal(4, $importedPersons['imported_count']);

		$invoiceFile = file_get_contents('../files/invoice_import_2.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);
		Assert::equal(2, $importedInvoices['imported_count']);

		$exportFile = file_get_contents('../files/export.csv');
		$exportsId = $this->exportModel->exportPersons($exportFile);

		$export = $this->exportModel->getExportPersonsData($exportsId);
		Assert::equal($this->user->getId(), $export['exports_users_id']);
		Assert::equal(2, $export['exports_lines']);
		Assert::count(2, $export['conditions']);
		Assert::equal("666666/1111", $export['conditions'][0]['exports_persons_persons_birth_id']);
		Assert::equal("555555/2222", $export['conditions'][1]['exports_persons_persons_birth_id']);
		Assert::equal(NULL, $export['conditions'][0]['exports_persons_persons_company_id']);

		Assert::count(1, $this->exportModel->getFluentBuilder()->fetchAll());

		$exportedPersons = $this->exportModel->getExportsPersonDetailFluentBuilder($exportsId)->orderBy(['persons_id' => 'desc'])->fetchAll();
		Assert::count(2, $exportedPersons);
		Assert::equal(547848, $exportedPersons[0]['persons_ag_id']);
		Assert::equal(547412, $exportedPersons[1]['persons_ag_id']);
	}
}

(new ExportTest($container))->run();