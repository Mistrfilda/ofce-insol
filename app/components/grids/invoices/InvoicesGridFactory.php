<?php

declare(strict_types = 1);


namespace App\Components\Grids\Invoices;


interface InvoicesGridFactory
{
	public function create() : InvoicesGrid;
}