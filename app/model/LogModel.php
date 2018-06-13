<?php

declare(strict_types = 1);


namespace App\Model;


use Dibi\Fluent;


class LogModel extends BaseModel
{
	public function getFluentBuilder() : Fluent
	{
		return $this->database->select('*')->from('log')->innerJoin('users')->on('users_id = log_users_id');
	}
}