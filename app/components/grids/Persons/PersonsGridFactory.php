<?php

declare(strict_types = 1);


namespace App\Components\Grids\Persons;


interface PersonsGridFactory
{
	public function create() : PersonsGrid;
}