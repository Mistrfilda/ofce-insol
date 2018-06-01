<?php

declare(strict_types = 1);


namespace App\Presenters;


use App\Components\Grids\Persons\PersonsGrid;
use App\Components\Grids\Persons\PersonsGridFactory;


class PersonsPresenter extends SecurePresenter
{
	/** @var PersonsGridFactory  */
	private $personsGridFactory;

	public function __construct(PersonsGridFactory $personsGridFactory)
	{
		parent::__construct();
		$this->personsGridFactory = $personsGridFactory;
	}

	public function createComponentPersonsGrid(string $name) : PersonsGrid
	{
		return $this->personsGridFactory->create();
	}
}