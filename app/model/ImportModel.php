<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\DateTime;
use Dibi\Exception;
use Dibi\Fluent;
use Nette\Utils\Strings;


class ImportModel extends BaseModel
{
	/** @var PersonModel */
	private $personModel;

	/** @var InvoiceModel */
	private $invoiceModel;

	/**
	 * For now hardcoded, possibility in future to set columns from config
	 * @var array|string[]
	 */
	private	$personColumns = ['Rodné číslo', 'Ročník/Datum', 'Jméno', 'Příjmení/Název'];

	/**
	 * For now hardcoded, possibility in future to set columns from config
	 * @var array|string[]
	 */
	private $invoiceColumns = ['Číslo prac. smlouvy', 'Id osoby', 'Rodné číslo', 'Platnost od', 'Typ smlouvy', 'Platnost do'];

	public function __construct(PersonModel $personModel, InvoiceModel $invoiceModel)
	{
		$this->personModel = $personModel;
		$this->invoiceModel = $invoiceModel;
	}

	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('imports');
	}

	/**
	 * @param string $fileContents
	 * @return array|mixed[]
	 * @throws AppException
	 */
	public function importPersons(string $fileContents) : array
	{
		$log = [];
		$importedPersons = 0;

		$this->logger->log('PERSON IMPORT', 'Start import');
		$csvParser = $this->getCsvParser();
		$csvParser->parse($fileContents);
		$data = $csvParser->data;
		$this->logger->log('PERSON IMPORT', 'File successfully parsed');

		if (count($data) > 0) {
			$inserts = [];
			foreach ($data as $key => $person) {
				foreach ($person as $personKey => $column) {
					if ($column === '') {
						$person[$personKey] = NULL;
					} else {
						$person[$personKey] = Strings::trim($column);
					}
				}

				if (!array_key_exists('IČ', $person)) {
					$person['IČ'] = NULL;
				}

				try {
					$this->validatePersonRow($person);
				} catch (AppException $e) {
					if ($e->getCode() === AppException::IMPORT_MISSING_MANDATORY_VALUE) {
						$log['skipped_columns'][] = ['index' => $key + 2, 'message' => 'Chybi vyplnene povinne pole - ' . $e->getMessage()];
						continue;
					}

					throw $e;
				}

				$inserts[] = [
					'persons_birth_id'   => $person['Rodné číslo'],
					'persons_year'       => $person['Ročník/Datum'],
					'persons_company_id' => $person['IČ'],
					'persons_firstname'  => $person['Jméno'],
					'persons_lastname'   => $person['Příjmení/Název']
				];
				$importedPersons++;
			}

			if (count($inserts) > 0) {
				try {
					$this->database->begin();
					$this->database->query('INSERT into persons %ex', $inserts);
					$this->database->commit();
				} catch (Exception $e) {
					throw new AppException($e->getCode(), $e->getMessage());
				}
			}
			$log['imported_count'] = $importedPersons;

			$this->insertImportResult('PERSON', $log);
			$this->logger->log('PERSON IMPORT', 'Import successfull, count of imported persons - ' . count($inserts));
			return $log;
		}

		$this->logger->log('PERSON IMPORT', 'Import finished, 0 persons imported');
		return $log;
	}

	/**
	 * @return array|mixed[]
	 */
	public function importPersonInvoices(string $fileContents) : array
	{
		$log = [];
		$importedInvoices = 0;

		$this->logger->log('INVOICE IMPORT', 'Start import');
		$csvParser = $this->getCsvParser();
		$csvParser->parse($fileContents);
		$data = $csvParser->data;
		$this->logger->log('INVOICE IMPORT', 'File successfully parsed');

		if (count($data) < 0) {
			throw new AppException(AppException::IMPORT_NO_ROWS);
		}

		$currentInvoices = $this->invoiceModel->getInvoicesBySystemId();

		if (count($data) > 0) {
			$this->database->begin();
			foreach ($data as $rowIndex => $row) {

				$validated = TRUE;
				foreach ($row as $key => $value) {
					if ($value === "") {
						$this->database->rollback();
						$this->logger->log('INVOICE IMPORT', 'Missing mandatory value - ' . $key);
						$log['skipped_columns'][] = ['index' => $rowIndex + 2, 'message' => 'Chybi vyplnene povinne pole - ' . $key];
						$validated = FALSE;
						continue;
					}

					$row[$key] = Strings::trim($value);
				}

				if (!$validated) {
					continue;
				}

				try {
					$this->validateInvoiceRow($row);
				} catch (AppException $e) {
					if ($e->getCode() === AppException::IMPORT_MISSING_MANDATORY_VALUE) {
						$log['skipped_columns'][] = ['index' => $rowIndex + 2, 'message' => 'Chybi vyplnene povinne pole - ' . $e->getMessage()];
						continue;
					}

					throw $e;
				}

				if ((int)array_key_exists($row['Číslo prac. smlouvy'], $currentInvoices)) {
					$log['skipped_columns'][] = ['index' => $rowIndex + 2, 'message' => $row['Číslo prac. smlouvy'] . ' - tato smlouva jiz byla naimportovana'];
					$this->logger->log('INVOICE IMPORT', 'Invoice already imported - ' . $row['Číslo prac. smlouvy'] . ' on line ' . $rowIndex);
					continue;
				}

				$invoiceFrom = strtotime($row['Platnost od']);
				if ($invoiceFrom === FALSE) {
					$this->database->rollback();
					$this->logger->log('INVOICE IMPORT', 'Unsupported date (invoice from) - ' . $row['Platnost od']);
					throw new AppException(AppException::IMPORT_INVOICES_UNSUPPORTED_DATE);
				}
				$invoiceFrom = new DateTime($invoiceFrom);

				$invoiceTo = strtotime($row['Platnost do']);
				if ($invoiceTo === FALSE) {
					$this->database->rollback();
					$this->logger->log('INVOICE IMPORT', 'Unsupported date - (invoice to) ' . $row['Platnost do']);
					throw new AppException(AppException::IMPORT_INVOICES_UNSUPPORTED_DATE);
				}
				$invoiceTo = new DateTime($invoiceTo);

				$this->database->query('INSERT into invoices', [
					'invoices_persons_birth_id' => $row['Rodné číslo'],
					'invoices_from' => $invoiceFrom,
					'invoices_to' => $invoiceTo,
					'invoices_imported_date' => $this->datetimeProvider->getNow(),
					'invoices_persons_system_id' => $row['Id osoby'],
					'invoices_type' => $row['Typ smlouvy'],
					'invoices_system_id' => $row['Číslo prac. smlouvy']
				]);

				$importedInvoices++;

				$invoiceId = $this->database->getInsertId();
				$this->personModel->updatePersonInvoice($row['Rodné číslo'], (int)$row['Id osoby'], $invoiceId, $invoiceTo);
			}
			$this->database->commit();

			$log['imported_count'] = $importedInvoices;

			$this->insertImportResult('INVOICE', $log);
			$this->logger->log('INVOICE IMPORT', 'Import successfull, count of imported invoices - ' . $importedInvoices);
			return $log;
		}

		$this->logger->log('INVOICE IMPORT', 'Import finished, 0 invoices imported');
		return $log;
	}


	/**
	 * @param string $type
	 * @param array|mixed[] $log
	 * @throws Exception
	 */
	private function insertImportResult(string $type, array $log) : void
	{
		$this->database->query('INSERT into imports', [
			'imports_time' => $this->datetimeProvider->getNow(),
			'imports_users_id' => $this->user->getId(),
			'imports_type' => $type,
			'imports_log' => json_encode($log)
		]);
	}

	/**
	 * @return array|mixed[]
	 */
	public function getImport(int $importId) : array
	{
		$import = $this->database->query('SELECT * from imports where imports_id = %i', $importId)->fetch();
		if ($import === NULL) {
			throw new AppException(AppException::IMPORT_UNKNOWN_IMPORT);
		}

		return (array) $import;
	}

	/**
	 * @param array|mixed[] $row
	 * @return bool
	 * @throws AppException
	 */
	private function validatePersonRow(array $row) : bool
	{
		foreach ($this->personColumns as $column) {
			if (!array_key_exists($column, $row) || (array_key_exists($column, $row) && $row[$column] === NULL)) {
				$this->database->rollback();
				$this->logger->log('PERSON IMPORT', 'Missing mandatory value - ' . $column);
				throw new AppException(AppException::IMPORT_MISSING_MANDATORY_VALUE, $column);
			}
		}

		return TRUE;
	}


	/**
	 * @param array|string[] $row
	 * @return bool
	 * @throws AppException
	 */
	private function validateInvoiceRow(array $row) : bool
	{
		foreach ($this->invoiceColumns as $column) {
			if (!array_key_exists($column, $row)) {
				$this->database->rollback();
				$this->logger->log('INVOICE IMPORT', 'Missing mandatory value - ' . $column);
				throw new AppException(AppException::IMPORT_MISSING_MANDATORY_VALUE, $column);
			}
		}

		return TRUE;
	}
}