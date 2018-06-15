<?php

declare(strict_types = 1);


namespace App\Components\Grids\Users;


interface UsersGridFactory
{
	public function create() : UsersGrid;
}