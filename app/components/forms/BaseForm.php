<?php

declare(strict_types = 1);


namespace App\Components\Forms;


use App\Lib\Logger;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Tomaj\Form\Renderer\BootstrapRenderer;


abstract class BaseForm extends Control
{
	/** @var User $user */
	protected $user;

	/** @var Logger */
	protected $logger;

	public function injectUser(User $user) : void
	{
		$this->user = $user;
	}

	public function injectLogger(Logger $logger) : void
	{
		$this->logger = $logger;
	}

	protected function createForm() : Form
	{
		$form = new Form();
		$form->setRenderer(new BootstrapRenderer());
		$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		return $form;
	}
}