<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\DateTime;
use Dibi\Exception;


class ImportModel extends BaseModel
{
	/** @var PersonModel */
	private $personModel;

	public function __construct(PersonModel $personModel)
	{
		$this->personModel = $personModel;
	}

	public function importPersons(string $fileContents) : int
	{
		$csvParser = $this->getCsvParser();
		$csvParser->parse($fileContents);
		$data = $csvParser->data;

		if (count($data) > 0) {
			$inserts = [];
			foreach ($data as $key => $person) {
				foreach ($person as $personKey => $column) {
					if ($column === '') {
						$person[$personKey] = NULL;
					}
				}

				$inserts[] = [
					'persons_birth_id'   => $person['Rodné číslo'],
					'persons_year'       => $person['Ročník'],
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

			return count($inserts);
		}

		return 0;
	}

	public function importPersonInvoices(string $fileContents) : int
	{
		$csvParser = $this->getCsvParser();
		$csvParser->parse($fileContents);
		$data = $csvParser->data;

		if (count($data) < 0) {
			throw new AppException(AppException::IMPORT_NO_ROWS);
		}

		if (count($data) > 0) {
			$this->database->begin();
			foreach ($data as $row) {
				$person = $this->personModel->getPerson((int) $row['Id osoby']);
				$invoiceFrom = strtotime($row['Platnost od']);
				if ($invoiceFrom === FALSE) {
					throw new AppException(AppException::IMPORT_INVOICES_UNSUPPORTED_DATE);
				}

				$this->database->query('INSERT into invoices', [
					'invoices_persons_id' => $person['persons_id'],
					'invoices_from' => new DateTime($invoiceFrom),
					'invoices_type' => $row['Typ smlouvy'],
					'invoices_imported_date' => $this->datetimeProvider->getNow()
				]);

				$invoiceId = $this->database->getInsertId();
				$this->personModel->updatePersonInvoice($person['persons_id'], $invoiceId);
			}
			$this->database->commit();
			return count($data);
		}

		return 0;
	}
}