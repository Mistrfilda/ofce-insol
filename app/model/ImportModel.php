<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\DateTime;
use Dibi\Exception;
use Nette\Utils\Strings;


class ImportModel extends BaseModel
{
	/** @var PersonModel */
	private $personModel;

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

	public function __construct(PersonModel $personModel)
	{
		$this->personModel = $personModel;
	}

	public function importPersons(string $fileContents) : int
	{
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

				$this->validatePersonRow($person);

				$inserts[] = [
					'persons_birth_id'   => $person['Rodné číslo'],
					'persons_year'       => $person['Ročník/Datum'],
					'persons_company_id' => $person['IČ'],
					'persons_firstname'  => $person['Jméno'],
					'persons_lastname'   => $person['Příjmení/Název']
				];
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

			$this->logger->log('PERSON IMPORT', 'Import successfull, count of imported persons - ' . count($inserts));
			return count($inserts);
		}

		$this->logger->log('PERSON IMPORT', 'Import finished, 0 persons imported');
		return 0;
	}

	public function importPersonInvoices(string $fileContents) : int
	{
		$this->logger->log('INVOICE IMPORT', 'Start import');
		$csvParser = $this->getCsvParser();
		$csvParser->parse($fileContents);
		$data = $csvParser->data;
		$this->logger->log('INVOICE IMPORT', 'File successfully parsed');

		if (count($data) < 0) {
			throw new AppException(AppException::IMPORT_NO_ROWS);
		}

		if (count($data) > 0) {
			$this->database->begin();
			foreach ($data as $rowIndex => $row) {
				foreach ($row as $key => $value) {
					if ($value === "") {
						$this->database->rollback();
						$this->logger->log('INVOICE IMPORT', 'Missing mandatory value - ' . $key);
						throw new AppException(AppException::IMPORT_MISSING_MANDATORY_VALUE, $key . ' na radku: ' . ($rowIndex + 2));
					}

					$row[$key] = Strings::trim($value);
				}

				$this->validateInvoiceRow($row);

				$invoiceFrom = strtotime($row['Platnost od']);
				if ($invoiceFrom === FALSE) {
					$this->database->rollback();
					$this->logger->log('INVOICE IMPORT', 'Unsupported date (invoice from) - ' . $row['Platnost od']);
					throw new AppException(AppException::IMPORT_INVOICES_UNSUPPORTED_DATE);
				}

				$invoiceTo = strtotime($row['Platnost do']);
				if ($invoiceTo === FALSE) {
					$this->database->rollback();
					$this->logger->log('INVOICE IMPORT', 'Unsupported date - (invoice to) ' . $row['Platnost do']);
					throw new AppException(AppException::IMPORT_INVOICES_UNSUPPORTED_DATE);
				}

				$this->database->query('INSERT into invoices', [
					'invoices_persons_birth_id' => $row['Rodné číslo'],
					'invoices_from' => new DateTime($invoiceFrom),
					'invoices_to' => new DateTime($invoiceTo),
					'invoices_type' => $row['Typ smlouvy'],
					'invoices_imported_date' => $this->datetimeProvider->getNow(),
					'invoices_persons_system_id' => $row['Id osoby'],
					'invoices_system_id' => $row['Číslo prac. smlouvy']
				]);

				$invoiceId = $this->database->getInsertId();
				$this->personModel->updatePersonInvoice($row['Rodné číslo'], (int)$row['Id osoby'], $invoiceId);
			}
			$this->database->commit();

			$this->logger->log('INVOICE IMPORT', 'Import successfull, count of imported invoices - ' . count($data));
			return count($data);
		}

		$this->logger->log('INVOICE IMPORT', 'Import finished, 0 invoices imported');
		return 0;
	}


	/**
	 * @param array|string[] $row
	 * @return bool
	 * @throws AppException
	 */
	private function validatePersonRow(array $row) : bool
	{
		foreach ($this->personColumns as $column) {
			if (!array_key_exists($column, $row)) {
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