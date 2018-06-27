<?php

declare(strict_types = 1);


namespace App\Components\Grids\Import;


interface ImportGridFactory
{
	public function create() : ImportGrid;
}