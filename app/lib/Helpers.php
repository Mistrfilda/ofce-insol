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

	public static function convertToUtfFromWindows1250(string $string) : string
	{
		$string = @iconv('windows-1250',  'utf-8', $string);

		if ($string === FALSE) {
			throw new AppException(AppException::HELPERS_GENERAL_ERROR);
		}

		return $string;
	}
}