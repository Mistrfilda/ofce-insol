<?php

declare(strict_types = 1);


namespace App\Components\Forms\Login;


use App\Components\Forms\BaseForm;
use App\Model\UserModel;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;


class LoginFormControl extends BaseForm
{
	private $userModel;

	public function __construct(UserModel $userModel)
	{
		parent::__construct();
		$this->userModel = $userModel;
	}

	public function render()
	{
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentLoginForm() : Form
	{
		$form = $this->createForm();
		$form->addText('login', 'Login')->setRequired();
		$form->addPassword('password', 'Heslo')->setRequired();
		$form->onSuccess[] = [$this, 'loginFormSucceed'];
		$form->addSubmit('submit', 'Prihlasit se');
		return $form;
	}

	public function loginFormSucceed(Form $form, ArrayHash $values)
	{
		try {
			$this->user->login($values['login'], $values['password']);
			$this->user->setExpiration('30 minutes');
		} catch (AuthenticationException $e) {
			$this->presenter->flashMessage('Nespravne uzivatelske jmeno nebo heslo');
			return;
		}

		$this->presenter->redirect('Homepage:default');
	}

}