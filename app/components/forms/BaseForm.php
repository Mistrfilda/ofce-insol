<?php

declare(strict_types = 1);


namespace App\Components\Forms;


use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tomaj\Form\Renderer\BootstrapRenderer;


abstract class BaseForm extends Control
{
	/** @var User $user */
	protected $user;

	public function injectUser(User $user)
	{
		$this->user = $user;
	}

	protected function createForm()
	{
		$form = new Form();
		$form->setRenderer(new BootstrapRenderer());
		return $form;
	}
}