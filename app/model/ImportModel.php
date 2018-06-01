<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\Exception;
use ParseCsv\Csv;


class ImportModel extends BaseModel
{
	public function importPersons(string $fileContents) : int
	{
		$csvParser = new Csv();
		$csvParser->delimiter = ';';
		$csvParser->encoding('UTF-8');
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
}