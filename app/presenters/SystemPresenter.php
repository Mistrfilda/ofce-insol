<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Grids\Log\LogGrid;
use App\Components\Grids\Log\LogGridFactory;
use Nette\Application\BadRequestException;


class SystemPresenter extends SecurePresenter
{
	/** @var LogGridFactory */
	private $logGridFactory;

	public function __construct(LogGridFactory $logGridFactory)
	{
		parent::__construct();
		$this->logGridFactory = $logGridFactory;
	}

	public function createComponentLogGrid(string $name) : LogGrid
	{
		return $this->logGridFactory->create();
	}

	public function startup() : void
	{
		parent::startup();
		if ($this->appUser['users_sysadmin'] === 0) {
			throw new BadRequestException();
		}
	}
}