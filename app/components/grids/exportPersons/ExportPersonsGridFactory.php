<?php

declare(strict_types = 1);


namespace App\Components\Grids\ExportPersons;


interface ExportPersonsGridFactory
{
	public function create() : ExportPersonsGrid;
}