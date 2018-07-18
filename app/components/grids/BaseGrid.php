<?php

declare(strict_types = 1);


namespace App\Components\Grids;


use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;


class BaseGrid extends Control
{
	public function createGrid() : DataGrid
	{
		$grid = new DataGrid();
		return $grid;
	}

	/**
	 * @param array|mixed[] $options
	 * @return array|mixed[]
	 */
	public function addGridSelect(array $options) : array
	{
		return [NULL => 'Vybrat'] + $options;
	}
}