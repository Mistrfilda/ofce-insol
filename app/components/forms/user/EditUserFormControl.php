<?php

declare(strict_types = 1);


namespace App\Components\Forms\User;


use App\Components\Forms\BaseForm;
use App\Model\UserModel;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;


class EditUserFormControl extends BaseForm
{
	/** @var UserModel */
	private $userModel;

	/** @var ?int */
	private $userId;

	public function __construct(UserModel $userModel)
	{
		parent::__construct();
		$this->userModel = $userModel;
	}

	public function setId(?int $id) : void
	{
		$this->userId = $id;
	}

	public function render() : void
	{
		if ($this->userId !== NULL && $this->userId !== 0) {
			$user = $this->userModel->getUserById($this->userId);
			$this['editUserForm']->setDefaults($user);
		}

		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}

	public function createComponentEditUserForm(string $name) : Form
	{
		$form = $this->createForm();
		$form->addText('users_login', 'Login')->setRequired();
		$form->addPassword('users_password', 'Heslo')->setNullable();
		$form->addSelect('users_sysadmin', 'Sysadmin', [0 => 'Ne', 1 => 'Ano']);
		$form->onSuccess[] = [$this, 'editUserFormSucceed'];
		$form->addSubmit('save', 'Ulozit');
		return $form;
	}

	public function editUserFormSucceed(Form $form, ArrayHash $values) : void
	{
		if ($this->userId !== NULL && $this->userId !== 0) {
			$this->userModel->updateUser($this->userId, $values['users_login'], $values['users_password'], $values['users_sysadmin']);
		} else {
			$this->userModel->createUser($values['users_login'], $values['users_password'], $values['users_sysadmin']);
		}

		$this->presenter->flashMessage('Uzivatel ulozen', 'success');
		$this->presenter->redirect('default');
	}

}