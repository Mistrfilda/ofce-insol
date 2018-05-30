<?php

declare(strict_types = 1);


namespace App\Presenters;


class SecurePresenter extends BasePresenter
{
	protected function startup()
	{
		parent::startup();
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Login:default');
		}
	}

}