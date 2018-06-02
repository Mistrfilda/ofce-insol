<?php

declare(strict_types = 1);


namespace App\Components\Grids\ExportPersonsDetail;


interface ExportPersonsDetailGridFactory
{
	public function create() : ExportPersonsDetailGrid;
}