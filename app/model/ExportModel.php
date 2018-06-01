<?php

declare(strict_types = 1);


namespace App\Model;

use App\Lib\AppException;
use Dibi\Fluent;
use Nette\Security\User;
use ParseCsv\Csv;


class ExportModel extends BaseModel
{
	/** @var User */
	private $user;


	public function __construct(User $user)
	{
		$this->user = $user;
	}


	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('exports');
	}


	public function exportPersons(string $fileContents) : int
	{
		$csvParser = new Csv();
		$csvParser->delimiter = ';';
		$csvParser->encoding('UTF-8');
		$csvParser->parse($fileContents);
		$data = $csvParser->data;

		if (count($data) < 0) {
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
		return $exportsId;
	}
}