<?php

declare(strict_types = 1);


namespace App\Lib;


class Helpers
{
	/**
	 * @return array|int[]|int[]
	 */
	public static function getGridYears(int $start, int $end) : array
	{
		$years = [];
		while ($start <= $end) {
			$years[$start] = $start;
			$start++;
		}

		return $years;
	}
}