<?php

declare(strict_types = 1);


namespace App\Components\Other;

use App\Model\StatisticsModel;


class HomepageStatisticsControl extends BaseControl
{
	/** @var StatisticsModel  */
	private $statisticsModel;

	public function __construct(StatisticsModel $statisticsModel)
	{
		parent::__construct();
		$this->statisticsModel = $statisticsModel;
	}

	public function render() : void
	{
		$this->getTemplate()->personsCount = $this->statisticsModel->getPersonsCount();
		$this->getTemplate()->invoicesCount = $this->statisticsModel->getInvoicesCount();
		$this->getTemplate()->setFile(str_replace('.php', '.latte', __FILE__));
		$this->getTemplate()->render();
	}
}