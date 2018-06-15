<?php

declare(strict_types = 1);


namespace App\Components\Forms\User;


interface EditUserFormControlFactory
{
	public function create() : EditUserFormControl;
}