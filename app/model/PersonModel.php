<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\Fluent;
use Dibi\Row;


class PersonModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('persons')->leftJoin('invoices')->on('persons_actual_invoice_id = invoices_id');
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

	public function updatePersonInvoice(string $personBirthId, int $invoiceId) : void
	{
		$this->database->query('UPDATE persons set persons_actual_invoice_id = %i where persons_birth_id = %s', $invoiceId, $personBirthId);
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
}