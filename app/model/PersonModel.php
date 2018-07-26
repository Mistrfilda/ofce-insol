<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\DateTime;
use Dibi\Exception;
use Dibi\Fluent;
use Dibi\Row;


class PersonModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('persons');
	}

	/**
	 * @param int $id
	 * @return array|mixed[]
	 * @throws AppException
	 */
	public function getPerson(int $id) : array
	{
		$data = $this->database->query('SELECT * from persons where persons_id = %i', $id)->fetch();
		if ($data === NULL) {
			throw new AppException(AppException::PERSON_UNKNOWN_PERSON, (string) $id);
		}

		return (array) $data;
	}

	public function updatePersonInvoice(string $personBirthId, int $personAgId, int $invoiceId, DateTime $invoiceTo) : void
	{
		try {
			$actualInvoice = $this->getActualPersonInvoiceByBirthId($personBirthId);
		} catch (AppException $e) {
			if ($e->getCode() === AppException::PERSON_UNKNOWN_PERSON) {
				return;
			}

			throw $e;
		}

		if ($actualInvoice['invoices_to'] !== NULL && $actualInvoice['invoices_to'] > $invoiceTo) {
			return;
		}

		$this->database->query('UPDATE persons set persons_actual_invoice_id = %i, persons_ag_id = %i where persons_birth_id = %s', $invoiceId, $personAgId, $personBirthId);

		$this->logger->log('PERSON INVOICE UPDATE', sprintf('Person: %s, AgID: %s, InvoiceID: %s' ,$personBirthId, $personAgId, $invoiceId), ['persons_birth_id' => $personBirthId, 'persons_ag_id' => $personAgId, 'invoices_id' => $invoiceId]);
	}


	/**
	 * @param null|string $personBirthId
	 * @return array|mixed[]
	 * @throws AppException
	 * @throws Exception
	 */
	public function getActualPersonInvoiceByBirthId(?string $personBirthId) : array
	{
		$data = $this->database->query('SELECT * from persons left join invoices on persons_actual_invoice_id = invoices_id where persons_birth_id = %s', $personBirthId)->fetch();

		if ($data === NULL) {
			throw new AppException(AppException::PERSON_UNKNOWN_PERSON);
		}

		return (array) $data;
	}

	/**
	 * @return array|Row[]
	 */
	public function getPersons() : array
	{
		return $this->database->query('SELECT * from persons')->fetchAssoc('persons_id');
	}

	/**
	 * @return array|array[]|Row[]
	 */
	public function getPersonInvoices(int $personId) : array
	{
		return $this->database->query('SELECT * from invoices where invoices_persons_birth_id = %s order by invoices_id desc', $this->getPersonBirthId($personId))->fetchAll();
	}

	public function getPersonBirthId(int $personId) : ?string
	{
		return $this->database->query('SELECT persons_birth_id from persons where persons_id = %i', $personId)->fetchSingle();
	}

	public function updatePersonChecked(int $personId, int $checked) : void
	{
		$this->getPerson($personId);
		$this->database->query('UPDATE persons set persons_checked = %i where persons_id = %i', $checked, $personId);
		$this->logger->log('PERSON CHECK STATUS UPDATE', sprintf('Person: %s, changed status to %s' ,$personId, $checked), ['persons_id' => $personId, 'persons_checked' => $checked]);
	}

	public function processNewPersonsInvoices() : int
	{
		$newPersonsBirthIds = array_keys($this->database->query('SELECT * from persons where persons_invoices_checked = %i limit 10000', 0)->fetchPairs('persons_birth_id', 'persons_birth_id'));
		$personsInvoices = $this->database->query('SELECT * from invoices where invoices_persons_birth_id in %in', $newPersonsBirthIds)->fetchAll();

		$updatedInvoices = 0;
		foreach ($personsInvoices as $invoice) {
			$this->updatePersonInvoice($invoice['invoices_persons_birth_id'], $invoice['invoices_persons_system_id'], $invoice['invoices_id'], $invoice['invoices_to']);
			$updatedInvoices = $updatedInvoices + 1;
		}

		return $updatedInvoices;
	}
}