<?php

declare(strict_types = 1);


namespace App\Lib;


use Throwable;


class AppException extends \Exception
{
	const UNKNOWN_USER = 10;

	public function __construct(int $code = 0, string $message = "", ?Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}