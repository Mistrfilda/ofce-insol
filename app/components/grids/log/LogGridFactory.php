<?php


namespace App\Components\Grids\Log;


interface LogGridFactory
{
	/** @return LogGrid */
	public function create();
}