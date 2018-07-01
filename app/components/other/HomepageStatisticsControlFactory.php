<?php

declare(strict_types = 1);


namespace App\Components\Other;


interface HomepageStatisticsControlFactory
{
	public function create() : HomepageStatisticsControl;
}