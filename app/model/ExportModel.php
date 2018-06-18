<?php

declare(strict_types = 1);


namespace App\Model;

use App\Lib\AppException;
use Dibi\Fluent;
use Dibi\Row;


class ExportModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('exports');
	}

	public function getExportsPersonDetailFluentBuilder(int $exportId) : Fluent
	{
		return $this->database->select('*')->from('persons')->leftJoin('invoices')->on('persons_actual_invoice_id = invoices_id')->where('(%or)', $this->buildDetailCondition($exportId));
	}


	/**
	 * @return array|array[]
	 */
	private function buildDetailCondition(int $exportId) : array
	{
		$rows = $this->getExportConditions($exportId);

		if (count($rows) === 0) {
			return [];
		}

		$where = [];
		foreach ($rows as $row) {
			if ($row['exports_persons_persons_company_id'] !== NULL && $row['exports_persons_persons_birth_id'] !== NULL) {
				$where[] = ['persons_company_id = %s and persons_birth_id = %s', $row['exports_persons_persons_company_id'], $row['exports_persons_persons_birth_id']];
				continue;
			}

			if ($row['exports_persons_persons_company_id'] !== NULL) {
				$where[] = ['persons_company_id = %s', $row['exports_persons_persons_company_id']];
			}

			if ($row['exports_persons_persons_birth_id'] !== NULL) {
				$where[] = ['persons_birth_id = %s', $row['exports_persons_persons_birth_id']];
			}
		}

		return $where;
	}

	/**
	 * @return array|array[]|Row[]
	 */
	private function getExportConditions(int $exportId) : array
	{
		return $this->database->query('SELECT * from exports_persons where exports_persons_exports_id = %i', $exportId)->fetchAll();
	}

	public function exportPersons(string $fileContents) : int
	{
		$this->logger->log('PERSON EXPORT', 'Start export');
		$csvParser = $this->getCsvParser();
		$csvParser->parse($fileContents);
		$data = $csvParser->data;
		$this->logger->log('PERSON EXPORT', 'File successfully parsed');

		if (count($data) < 0) {
			$this->logger->log('PERSON EXPORT', '0 rows parsed');
			throw new AppException(AppException::EXPORT_PERSONS_NO_ROWS);
		}

		$this->database->begin();
		$this->database->query('INSERT into exports', [
			'exports_users_id' => $this->user->getId(),
			'exports_time'     => $this->datetimeProvider->getNow(),
			'exports_lines' => count($data)
		]);

		$exportsId = $this->database->getInsertId();

		$inserts = [];
		foreach ($data as $key => $row) {
			if (!array_key_exists('Rodné číslo', $row)) {
				$this->database->rollback();
				$this->logger->log('PERSON EXPORT', 'Missing mandatory value - Rodné číslo');
				throw new AppException(AppException::EXPORT_PERSONS_MISSING_MANDATORY_VALUE, 'Rodné číslo');
			}

			if (!array_key_exists('IČ', $row)) {
				$row['IČ'] = NULL;
			}

			foreach ($row as $columnName => $column) {
				if ($column === "") {
					$row[$columnName] = NULL;
				}
			}

			$inserts[] = [
				'exports_persons_exports_id' => $exportsId,
				'exports_persons_persons_company_id' => $row['IČ'],
				'exports_persons_persons_birth_id' => $row['Rodné číslo']
			];
		}

		if (count($inserts) > 0) {
			$this->database->query('INSERT into exports_persons %ex', $inserts);
		}

		$this->database->commit();

		$this->logger->log('PERSON EXPORT', 'Export successful, count of exported persons - ' . count($inserts));
		return $exportsId;
	}


	/**
	 * @return array|array[]
	 */
	public function getExportPersonsData(int $exportId) : array
	{
		$data = $this->database->query('SELECT * from exports inner join users on exports_users_id = users_id where exports_id = %i', $exportId)->fetch();
		if ($data === NULL) {
			throw new AppException(AppException::EXPORT_UNKNOWN_EXPORT);
		}

		$data['conditions'] = $this->getExportConditions($exportId);

		return (array) $data;
	}
}