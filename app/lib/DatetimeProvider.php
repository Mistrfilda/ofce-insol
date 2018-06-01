<?php

declare(strict_types = 1);


namespace App\Lib;

/**
 *
 * Simple datetime provider service
 */
class DatetimeProvider
{
	public function getNow() : \DateTimeImmutable
	{
		return new \DateTimeImmutable();
	}
}