<?php


namespace Test;


use App\Lib\AppException;
use App\Model\ImportModel;
use App\Model\PersonModel;
use Tester\Assert;


$container = require '../bootstrap.php';

/**
 * @testCase
 */
class PersonTest extends BaseTest
{
	/** @var PersonModel */
	private $personModel;

	/** @var ImportModel */
	private $importModel;

	public function setUp()
	{
		parent::setUp();
		$this->personModel = $this->container->getByType(PersonModel::class);
		$this->importModel = $this->container->getByType(ImportModel::class);
		$this->createUser();
		$this->loginUser();
	}

	public function testImportPersons()
	{
		$this->database->commit();
		$file = file_get_contents('../files/blank_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal([], $importedPersons);
		$file = file_get_contents('../files/person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(4, $importedPersons['imported_count']);
		Assert::count(2, $importedPersons['skipped_columns']);
		Assert::equal(6, $importedPersons['skipped_columns'][0]['index']);
		Assert::equal(7, $importedPersons['skipped_columns'][1]['index']);

		$persons = $this->personModel->getPersons();
		Assert::count(4, $persons);

	}

	public function testGetPersonMethods()
	{
		$this->database->commit();
		$file = file_get_contents('../files/single_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(1, $importedPersons['imported_count']);

		$personsFluentCount = $this->personModel->getFluentBuilder()->fetchAll();
		Assert::count(1, $personsFluentCount);

		$persons = $this->personModel->getPersons();
		Assert::count(1, $persons);

		$testPerson = array_pop($persons);

		Assert::exception(function () {
			$this->personModel->getPerson(9999999);
		}, AppException::class, '9999999', AppException::PERSON_UNKNOWN_PERSON);

		$person = $this->personModel->getPerson($testPerson['persons_id']);
		Assert::equal($person['persons_birth_id'], '666666/1111');
		Assert::equal($person['persons_year'], 2018);
		Assert::equal($person['persons_firstname'], 'Test');
		Assert::equal($person['persons_lastname'], 'person 1');

		$invoiceFile = file_get_contents('../files/invoice_import_2.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);

		Assert::equal(2, $importedInvoices['imported_count']);

		$person = $this->personModel->getPerson($testPerson['persons_id']);
		Assert::notEqual(NULL, $person['persons_actual_invoice_id']);

		$personInvoices = $this->personModel->getPersonInvoices($testPerson['persons_id']);
		Assert::count(1, $personInvoices);
	}

	public function testPersonChecked()
	{
		$this->database->commit();
		$file = file_get_contents('../files/single_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(1, $importedPersons['imported_count']);

		$persons = $this->personModel->getPersons();
		Assert::count(1, $persons);

		$testPerson = array_pop($persons);
		$person = $this->personModel->getPerson($testPerson['persons_id']);
		Assert::equal(0, $person['persons_checked']);
		$this->personModel->updatePersonChecked($person['persons_id'], 1);

		$person = $this->personModel->getPerson($testPerson['persons_id']);
		Assert::equal(1, $person['persons_checked']);
	}

	public function testPersonsInvoicesCommand()
	{
		$invoiceFile = file_get_contents('../files/invoice_import_2.csv');
		$importedInvoices = $this->importModel->importPersonInvoices($invoiceFile);

		Assert::equal(2, $importedInvoices['imported_count']);

		$file = file_get_contents('../files/single_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(1, $importedPersons['imported_count']);

		$this->personModel->processNewPersonsInvoices();

		$persons = $this->personModel->getPersons();
		Assert::count(1, $persons);

		$testPerson = array_pop($persons);

		$person = $this->personModel->getPerson($testPerson['persons_id']);
		Assert::notEqual(NULL, $person['persons_actual_invoice_id']);
	}
}

(new PersonTest($container))->run();