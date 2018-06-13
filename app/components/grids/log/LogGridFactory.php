<?php

declare(strict_types = 1);


namespace App\Components\Grids\Log;


interface LogGridFactory
{
	public function create() : LogGrid;
}