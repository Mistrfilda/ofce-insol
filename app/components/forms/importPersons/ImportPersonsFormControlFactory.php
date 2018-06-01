<?php

declare(strict_types = 1);


namespace App\Components\Forms\ImportPersons;


interface ImportPersonsFormControlFactory
{
	public function create() : ImportPersonsFormControl;
}