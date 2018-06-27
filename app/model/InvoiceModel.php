<?php

declare(strict_types = 1);


namespace App\Model;


use App\Lib\AppException;
use Dibi\Fluent;


class InvoiceModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('invoices');
	}

	/**
	 * @return array|int[]
	 */
	public function getInvoicesBySystemId() : array
	{
		return $this->database->query('SELECT * from invoices')->fetchPairs('invoices_system_id', 'invoices_id');
	}

	/**
	 * @return array|mixed[]
	 */
	public function getInvoice(int $invoiceId) : array
	{
		$invoice = $this->database->query('SELECT * from invoices where invoices_id = %i', $invoiceId)->fetch();
		if ($invoice === NULL) {
			throw new AppException(AppException::INVOICE_UNKNOWN_INVOICE);
		}

		return (array) $invoice;
	}
}