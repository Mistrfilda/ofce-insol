<?php

declare(strict_types = 1);


namespace App\Components\Forms\Login;


interface LoginFormControlFactory
{
	/** @return LoginFormControl */
	public function create();
}