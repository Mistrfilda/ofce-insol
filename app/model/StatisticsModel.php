<?php

declare (strict_types = 1);


namespace App\Model;


class StatisticsModel extends BaseModel
{
	public function getPersonsCount() : int
	{
		return $this->database->query('SELECT count(persons_id) from persons')->fetchSingle();
	}

	public function getInvoicesCount() : int
	{
		return $this->database->query('SELECT count(invoices_id) from invoices')->fetchSingle();
	}
}