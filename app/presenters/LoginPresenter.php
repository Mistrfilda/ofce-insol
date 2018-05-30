<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Forms\Login\LoginFormControlFactory;


class LoginPresenter extends BasePresenter
{
	private $loginFormControlFactory;

	public function __construct(LoginFormControlFactory $loginFormControlFactory)
	{
		parent::__construct();
		$this->loginFormControlFactory = $loginFormControlFactory;
	}

	public function createComponentLoginForm()
	{
		return $this->loginFormControlFactory->create();
	}
}