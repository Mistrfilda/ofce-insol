<?php

declare(strict_types = 1);


namespace App\Presenters;


use Nette\Application\BadRequestException;


class SystemPresenter extends SecurePresenter
{
	public function startup() : void
	{
		parent::startup();
		if ($this->appUser['users_sysadmin'] === 0) {
			throw new BadRequestException();
		}
	}
}