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
		$this->loginUser();
	}

	public function testImportPersons()
	{
		$this->database->commit();
		Assert::exception(function () {
			$this->importModel->importPersons(file_get_contents('../files/person_missing_data.csv'));
		}, AppException::class, 'Ročník', AppException::IMPORT_MISSING_MANDATORY_VALUE);

		$file = file_get_contents('../files/person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(5, $importedPersons);

		$persons = $this->personModel->getPersons();
		Assert::count(5, $persons);

		//need to delete from persons table manually, since transactions are commited in importing
		$this->database->query('DELETE from persons');
	}

	public function testGetPersonMethods()
	{
		$this->database->commit();
		$file = file_get_contents('../files/single_person_import.csv');
		$importedPersons = $this->importModel->importPersons($file);
		Assert::equal(1, $importedPersons);

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

		Assert::equal(2, $importedInvoices);

		$person = $this->personModel->getPerson($testPerson['persons_id']);
		Assert::notEqual(NULL, $person['persons_actual_invoice_id']);

		$personInvoices = $this->personModel->getPersonInvoices($testPerson['persons_id']);
		Assert::count(1, $personInvoices);

		//need to delete from persons table manually, since transactions are commited in importing, just for localhost :) i am lazy
		$this->database->query('DELETE from persons');
		$this->database->query('DELETE from invoices');
	}
}

(new PersonTest($container))->run();