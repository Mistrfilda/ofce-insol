<?php


namespace App\Components\Forms\Login;


interface LoginFormControlFactory
{
	/** @return LoginFormControl */
	public function create();
}