<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Other\HomepageStatisticsControl;
use App\Components\Other\HomepageStatisticsControlFactory;


class HomepagePresenter extends SecurePresenter
{
	/** @var HomepageStatisticsControlFactory */
	private $homepageStatisticsControlFactory;

	public function __construct(HomepageStatisticsControlFactory $homepageStatisticsControlFactory)
	{
		parent::__construct();
		$this->homepageStatisticsControlFactory = $homepageStatisticsControlFactory;
	}

	public function createComponentHomepageStatisticsControl(string $name) : HomepageStatisticsControl
	{
		return $this->homepageStatisticsControlFactory->create();
	}

	public function renderDefault() : void
	{
	}
}
