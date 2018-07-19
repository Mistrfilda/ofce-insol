<?php

declare(strict_types = 1);


namespace App\Components\Other;


interface CssJsLoaderControlFactory
{
	public function create() : CssJsLoaderControl;
}