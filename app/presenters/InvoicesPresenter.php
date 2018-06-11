<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Grids\Invoices\InvoicesGrid;
use App\Components\Grids\Invoices\InvoicesGridFactory;


class InvoicesPresenter extends SecurePresenter
{
	/** @var InvoicesGridFactory  */
	private $invoicesGridFactory;

	public function __construct(InvoicesGridFactory $invoicesGridFactory)
	{
		parent::__construct();
		$this->invoicesGridFactory = $invoicesGridFactory;
	}

	public function createComponentInvoicesGrid(string $name) : InvoicesGrid
	{
		return $this->invoicesGridFactory->create();
	}
}