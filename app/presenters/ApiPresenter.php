<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Api\TestApi;


class ApiPresenter extends SecurePresenter
{
	public function renderDefault() : void
	{
		$test = new TestApi();
		dump($test->getPersonByBirthId('710426/3881'));
	}
}