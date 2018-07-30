<?php

declare (strict_types = 1);


namespace App\Lib;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MonologConsoleHandler
{
	public static function getMonologConsoleHandler() : StreamHandler
	{
		$output = "[%datetime%] %channel%.%level_name%: %message%\n";
		$formatter = new LineFormatter($output);

		$streamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
		$streamHandler->setFormatter($formatter);

		return $streamHandler;
	}
}