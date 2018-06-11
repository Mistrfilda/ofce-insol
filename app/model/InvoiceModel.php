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
}