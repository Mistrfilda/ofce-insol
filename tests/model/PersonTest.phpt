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
}

(new PersonTest($container))->run();