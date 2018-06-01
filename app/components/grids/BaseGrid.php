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
	 * @param array|array[] $option
	 * @return array|array[string]
	 */
	public function addGridSelect(array $option) : array
	{
		return [NULL => 'Vybrat'] + $option;
	}
}