<?php

declare(strict_types = 1);


namespace App\Components\Forms\ImportPersonsInvoices;



interface ImportPersonInvoicesFormControlFactory
{
	public function create() : ImportPersonInvoicesFormControl;
}