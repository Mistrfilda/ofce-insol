<?php

declare(strict_types = 1);


namespace App\Model;


use Dibi\Fluent;


class InvoiceModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('invoices');
	}

	/** @return array|int[] */
	public function getInvoicesBySystemId() : array
	{
		return $this->database->query('SELECT * from invoices')->fetchPairs('invoices_system_id', 'invoices_id');
	}
}