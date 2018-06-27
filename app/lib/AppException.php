<?php

declare(strict_types = 1);


namespace App\Lib;


use Throwable;


class AppException extends \Exception
{
	const UNKNOWN_USER = 10;

	const
		IMPORT_DATABASE_ERROR = 20,
		IMPORT_NO_ROWS = 21,
		IMPORT_INVOICES_UNSUPPORTED_DATE = 22,
		IMPORT_MISSING_MANDATORY_VALUE = 23,
		IMPORT_UNKNOWN_IMPORT = 24;

	const
		EXPORT_UNKNOWN_EXPORT = 30,
		EXPORT_PERSONS_NO_ROWS = 31,
		EXPORT_PERSONS_MISSING_MANDATORY_VALUE = 32;

	const
		PERSON_UNKNOWN_PERSON = 40;


	public function __construct(int $code = 0, string $message = "", ?Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}